<?php

	namespace App\Observers\Inventory;

	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	class InventoryTransactionObserver {
		/**
		 * Handle the InventoryTransaction "creating" event.
		 * Logic for generating unique transaction numbers.
		 */
		public function creating(InventoryTransaction $transaction): void {
			// Αν δεν έχουμε ορίσει χειροκίνητα batch_number (π.χ. από κάποιο service)
			// φτιάξε ένα αυτόματο.
			if (empty($transaction->batch_number)) {
				$prefix = match($transaction->type) {
					TransactionType::ADJUSTMENT => 'ADJ',
					TransactionType::IN => 'IN',
					TransactionType::OUT => 'OUT',
					default => 'TRX'
				};

				$transaction_time = Carbon::now(config('app.timezone'))->format('Ymd');
				$transaction->batch_number = $prefix. '-' . $transaction_time . '-' . Str::upper(Str::random(6));
			}
		}

		/**
		 * Handle the InventoryTransaction "saving" event.
		 * Logic for financial math consistency.
		 * Fires on both CREATE and UPDATE right before writing to DB
		 */
		public function saving(InventoryTransaction $transaction): void {
			// Automatically keep total cost in sync
			if ($transaction->unit_cost && $transaction->quantity) {
				$transaction->total_cost = abs($transaction->unit_cost * $transaction->quantity);
			}
		}

		/**
		 * @throws Throwable
		 */
		public function created(InventoryTransaction $transaction): void {
			// 💡 Ασφάλεια: Αν λείπει το warehouse_id ή το product_id, δεν μπορούμε να ενημερώσουμε το stock.
			// Το location_id επιτρέπεται να είναι null βάσει του migration σου στο inventory_transactions table.
			if (empty($transaction->warehouse_id) || empty($transaction->product_id)) {
				return;
			}

			DB::transaction(function () use ($transaction) {
				$searchBatch = $transaction->batch_number;

				// Αν το batch δημιουργήθηκε αυτόματα (TRX-, IN-, ADJ-),
				// σημαίνει ότι δεν ανήκει σε πραγματικό short-lived batch εγγραφής του Inventories,
				// οπότε ψάχνουμε την generic null εγγραφή στον πίνακα inventories.
				if (Str::startsWith($searchBatch, [
					'TRX-',
					'IN-',
					'ADJ-'
				])) {
					$searchBatch = null;
				}

				// Αναζήτηση ή δημιουργία του Live Stock (Inventories)
				$inventory = Inventory::where([
					'product_id'   => $transaction->product_id,
					'warehouse_id' => $transaction->warehouse_id,
					'location_id'  => $transaction->location_id,
					// Μπορεί να είναι null
					'batch_number' => $searchBatch,
				])->lockForUpdate()->first();

				if (!$inventory) {
					// ΠΡΟΣΟΧΗ: Αν το inventories table απαιτεί μη-null location_id,
					// βεβαιώσου ότι έχεις ορίσει ένα default bin (π.χ. 1) αν το transaction ήρθε χωρίς location.
					$inventory = Inventory::create([
						'product_id'   => $transaction->product_id,
						'warehouse_id' => $transaction->warehouse_id,
						'location_id'  => $transaction->location_id ?? $transaction->warehouse->locations()->pluck('id')->random(),
						// Default fallback αν χρειάζεται
						'batch_number' => $searchBatch,
						'quantity'     => 0
					]);
				}

				// Snapshots για το audit log (Μετατροπή σε int λόγω του integer type στο migration)
				$transaction->quantity_before = intval($inventory->quantity);
				$transaction->quantity_after  = intval($inventory->quantity + $transaction->quantity);

				// Προσθήκη του συστημικού reference tracking number
				if ($searchBatch === null && $transaction->batch_number) {
					$transaction->notes = "System Batch ref: " . $transaction->batch_number . ". " . $transaction->notes;
				}

				$now = Carbon::now(config('app.timezone'));

				DB::table('inventory_transactions')->where('id', $transaction->id)->update([
						'quantity_before' => $transaction->quantity_before,
						'quantity_after'  => $transaction->quantity_after,
						'notes'           => $transaction->notes ?? $this->generateDefaultNotes($transaction),
						'created_at'      => $transaction->created_at ?? $now,
						'updated_at'      => $transaction->updated_at ?? $now,
					]);

				// saveQuietly() does not fire Observer, but ->save() does, resulting infinite loop
				$transaction->saveQuietly();

				// Ενημέρωση του live stock
				$inventory->increment('quantity', intval($transaction->quantity));
			});
		}

		/**
		 * Παραγωγή ρεαλιστικών αυτόματων σημειώσεων με βάση τον τύπο και την ακριβή αιτιολογία της κίνησης.
		 */
		protected function generateDefaultNotes(InventoryTransaction $transaction): string {
			$creatorName = $transaction->creator ? $transaction->creator->name : 'Σύστημα';

			return match($transaction->type) {
				// 1. Περίπτωση Εισαγωγής (IN)
				TransactionType::IN => match($transaction->reason) {
					TransactionReason::PURCHASE     => "Αυτόματη παραλαβή και εισαγωγή αποθεμάτων από παραγγελία αγοράς.",
					TransactionReason::RETURNED     => "Επιστροφή προϊόντος από πελάτη και επαναφορά στο διαθέσιμο απόθεμα.",
					TransactionReason::TRANSFER_IN  => "Εισαγωγή αποθεμάτων μέσω εσωτερικής μεταφοράς / διακίνησης.",
					TransactionReason::FOUND        => "Θετική διόρθωση αποθέματος - Βρέθηκαν πλεονάζοντα τεμάχια.",
					TransactionReason::STOCKTAKE,
					TransactionReason::COUNTING_ERROR,
					TransactionReason::DATA_ENTRY   => "Θετική προσαρμογή υπολοίπου κατόπιν καταμέτρησης / απογραφής.",
					default                         => "Χειροκίνητη εισαγωγή αποθεμάτων στο σύστημα από τον χρήστη ".$creatorName,
				},

				// 2. Περίπτωση Εξαγωγής (OUT)
				TransactionType::OUT => match($transaction->reason) {
					TransactionReason::SALE         => "Απομείωση αποθέματος λόγω δέσμευσης / ολοκλήρωσης πώλησης.",
					TransactionReason::TRANSFER_OUT => "Εξαγωγή αποθεμάτων λόγω εσωτερικής μεταφοράς σε άλλη τοποθεσία.",
					TransactionReason::RETURNED     => "Επιστροφή ελαττωματικών ή πλεοναζόντων αποθεμάτων προς τον προμηθευτή.",
					TransactionReason::DAMAGED      => "Απομείωση αποθέματος λόγω καταστροφής / φθοράς προϊόντων.",
					TransactionReason::EXPIRED      => "Απόσυρση προϊόντων λόγω λήξης της ημερομηνίας καταλληλότητας.",
					TransactionReason::THEFT        => "Απώλεια αποθέματος λόγω καταγεγραμμένης κλοπής / υπεξαίρεσης.",
					TransactionReason::LOST,
					TransactionReason::WRITE_OFF    => "Οριστική διαγραφή και ολική απόσβεση αποθεμάτων από το σύστημα.",
					TransactionReason::SPILLAGE     => "Απώλεια αποθέματος λόγω διαρροής / χυσίματος υλικού.",
					TransactionReason::QC_REJECT    => "Απόρριψη παρτίδας κατά τον ποιοτικό έλεγχο (Quality Control).",
					TransactionReason::QC_SAMPLE,
					TransactionReason::QUALITY_CONTROL => "Δέσμευση δειγμάτων για τη διαδικασία ποιοτικού ελέγχου.",
					TransactionReason::PRODUCTION   => "Ανάλωση αποθεμάτων για τις ανάγκες της γραμμής παραγωγής.",
					TransactionReason::SAMPLE,
					TransactionReason::DEMO,
					TransactionReason::PROMO        => "Εξαγωγή ειδών για δειγματισμό, προώθηση ή εταιρική παρουσίαση.",
					TransactionReason::DONATION     => "Εξαγωγή αποθεμάτων για σκοπούς δωρεάς / χορηγίας.",
					TransactionReason::STOCKTAKE,
					TransactionReason::COUNTING_ERROR,
					TransactionReason::DATA_ENTRY   => "Αρνητική προσαρμογή υπολοίπου κατόπιν καταμέτρησης / απογραφής.",
					default                         => "Χειροκίνητη εξαγωγή αποθεμάτων από το σύστημα από τον χρήστη {$creatorName}.",
				},

				// 3. Περίπτωση Απογραφής (ADJUSTMENT)
				TransactionType::ADJUSTMENT => match($transaction->reason) {
					TransactionReason::STOCKTAKE    => "Γενική απογραφή αποθήκης και καθορισμός νέου φυσικού υπολοίπου.",
					TransactionReason::COUNTING_ERROR => "Διόρθωση σφάλματος προηγούμενης καταμέτρησης.",
					TransactionReason::DATA_ENTRY   => "Διόρθωση λανθασμένης καταχώρησης στο σύστημα.",
					default                         => "Εξισορρόπηση υπολοίπου μέσω της διαδικασίας απογραφής.",
				},

				default => "Καταγραφή κίνησης αποθέματος από το σύστημα (Αυτοματοποιημένο Log).",
			};
		}
	}
