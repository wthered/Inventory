<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Enums\Inventory\MovementStatus;
	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	trait CanSeedAdjustments {
		abstract public function flushList(): void;

		/**
		 * @throws Throwable
		 */
		protected function seedAdjustments(Collection $products, Collection $users): void {
			$this->command->info('Generating adjustment transactions safely...');

			// 1. Παίρνουμε 1024 τυχαία locations ΜΙΑ φορά
			$locationsList = DB::table('warehouse_locations')->inRandomOrder()->limit(1024)->get([
				'id',
				'warehouse_id'
			]);

			for ($i = 0; $i < $locationsList->count(); $i++) {
				$product = $products->random();
				$user    = $users->random();

				// 2. ΕΠΙΛΟΓΗ ΣΥΓΚΕΚΡΙΜΕΝΟΥ LOCATION
				$location = $locationsList->random();
				$type       = fake()->boolean() ? AdjustmentType::INCREASE : AdjustmentType::DECREASE;
				$enumReason = Collection::make(AdjustmentReason::cases())->random();

				$quantityBefore = mt_rand(24, 160);
				$adjQty         = mt_rand(1, 64);
				$quantityAfter  = ($type == AdjustmentType::INCREASE) ? $quantityBefore + $adjQty : $quantityBefore - $adjQty;
				$createdAt      = now()->subDays(mt_rand(0, 90));

				// 3. ΠΡΟΣΘΗΚΗ $location ΚΑΙ $i ΣΤΟ use
				DB::transaction(function () use ($product, $location, $user, $type, $enumReason, $adjQty, $quantityBefore, $quantityAfter, $createdAt) {
					/** @var Product $product */

					// 4. ΔΙΟΡΘΩΣΗ adjustment_number (Str::padLeft στο $i)
					$adjustment = StockAdjustment::query()->create([
						'adjustment_number' => 'ADJ-' . $createdAt->format('Y-m-d') . '-' . Str::upper(Str::random(mt_rand(6, 12))),
						'warehouse_id'      => $location->warehouse_id,
						'adjustment_date'   => $createdAt,
						'status'            => fake()->randomElement(MovementStatus::forAdjustment())->value,
						'created_by'        => $user,
						'approved_by'       => $user,
						'approved_at'       => $createdAt->setHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
						'notes'             => $this->generateAdjustmentNote($type, $adjQty, $enumReason),
						'created_at'        => $createdAt,
						'updated_at'        => $createdAt->copy()->addDays(mt_rand(1, 90)),
					]);

					// 5. ΧΡΗΣΗ ΤΟΥ $location (όχι της λίστας)
					$adjustment->items()->create([
						'product_id'          => $product->id,
						'location_id'         => $location->id,
						'type'                => $type->value,
						'reason'              => $enumReason->value,
						'quantity'            => $adjQty,
						'quantity_before'     => $quantityBefore,
						'quantity_after'      => $quantityAfter,
						'unit_cost'           => $product->cost_price ?? 10,
						'notes'               => $this->generateAdjustmentItemNote($type, $adjQty, $enumReason),
						'created_at'          => $createdAt,
						'updated_at'          => $createdAt->copy()->addDays(mt_rand(1, 90)),
					]);

					$product->transactions()->create([
						'batch_number'    => InventoryTransaction::generateTransactionNumber('ADJ'),
						'warehouse_id'    => $location->warehouse_id,
						'location_id'     => $location->id,
						'type'            => fake()->randomElement(TransactionType::cases())->value,
						'reason'          => fake()->randomElement(TransactionReason::cases())->value,
						'quantity'        => ($type == AdjustmentType::DECREASE) ? -$adjQty : $adjQty,
						'quantity_before' => $quantityBefore,
						'quantity_after'  => $quantityAfter,
						'reference_type'  => StockAdjustment::class,
						'reference_id'    => $adjustment->id,
						'created_by'      => $user,
//						'created_at'      => $createdAt,
//						'updated_at'      => $createdAt->addDays(mt_rand(1, 90)),
					]);
				});
			}
		}

		private function generateAdjustmentNote(AdjustmentType $type, int $quantity, AdjustmentReason $reason): string {
			$notes = [
				// --- Core Operations ---
				AdjustmentReason::PURCHASE->value => [
					AdjustmentType::INCREASE->value => "Stock received from supplier purchase order",
				],
				AdjustmentReason::SALE->value => [
					AdjustmentType::DECREASE->value => "Stock deducted due to direct customer sale",
				],
				AdjustmentReason::RETURNED->value => [
					AdjustmentType::INCREASE->value => "Customer return - items restocked into inventory",
				],
				AdjustmentReason::TRANSFER_IN->value => [
					AdjustmentType::INCREASE->value => "Stock transferred in from another location/warehouse",
				],
				AdjustmentReason::TRANSFER_OUT->value => [
					AdjustmentType::DECREASE->value => "Stock transferred out to another location/warehouse",
				],

				// --- Stock Corrections ---
				AdjustmentReason::STOCKTAKE->value => [
					AdjustmentType::INCREASE->value => "Found extra units during official stocktake",
					AdjustmentType::DECREASE->value => "Missing units discovered during official stocktake",
				],
				AdjustmentReason::COUNTING_ERROR->value => [
					AdjustmentType::INCREASE->value => "Inventory corrected upwards after counting error",
					AdjustmentType::DECREASE->value => "Inventory corrected downwards after counting error",
				],
				AdjustmentReason::DATA_ENTRY->value => [
					AdjustmentType::INCREASE->value => "Correction due to previous data entry mistake",
					AdjustmentType::DECREASE->value => "Correction due to previous data entry mistake",
				],
				AdjustmentReason::FOUND->value => [
					AdjustmentType::INCREASE->value => "Found in unexpected location / misplaced stock",
				],
				AdjustmentReason::ADJUSTMENT->value => [
					AdjustmentType::INCREASE->value => "Manual balance adjustment (Increase)",
					AdjustmentType::DECREASE->value => "Manual balance adjustment (Decrease)",
				],

				// --- Quality Issues ---
				AdjustmentReason::DAMAGED->value => [
					AdjustmentType::DECREASE->value => "Units damaged and written off",
				],
				AdjustmentReason::EXPIRED->value => [
					AdjustmentType::DECREASE->value => "Expired units removed and disposed",
				],
				AdjustmentReason::SPILLAGE->value => [
					AdjustmentType::DECREASE->value => "Stock lost due to accidental spillage/leakage",
				],
				AdjustmentReason::QUALITY_CONTROL->value => [
					AdjustmentType::DECREASE->value => "Held or removed for routine Quality Control inspection",
				],
				AdjustmentReason::QC_REJECT->value => [
					AdjustmentType::DECREASE->value => "Failed Quality Control inspection - discarded",
				],
				AdjustmentReason::QC_SAMPLE->value => [
					AdjustmentType::DECREASE->value => "Consumed as a sample for Quality Control testing",
				],

				// --- Loss & Theft ---
				AdjustmentReason::THEFT->value => [
					AdjustmentType::DECREASE->value => "Reported theft / inventory shrinkage",
				],
				AdjustmentReason::LOST->value => [
					AdjustmentType::DECREASE->value => "Unaccounted loss / missing stock",
				],
				AdjustmentReason::WRITE_OFF->value => [
					AdjustmentType::DECREASE->value => "Approved inventory write-off",
				],

				// --- Business Use ---
				AdjustmentReason::PRODUCTION->value => [
					AdjustmentType::INCREASE->value => "Finished goods added from production line",
					AdjustmentType::DECREASE->value => "Raw materials consumed in production line",
				],
				AdjustmentReason::SAMPLE->value => [
					AdjustmentType::DECREASE->value => "Item distributed as a free sample",
				],
				AdjustmentReason::DEMO->value => [
					AdjustmentType::DECREASE->value => "Allocated for product demonstration purposes",
				],
				AdjustmentReason::PROMO->value => [
					AdjustmentType::DECREASE->value => "Used in promotional marketing campaign",
				],
				AdjustmentReason::DONATION->value => [
					AdjustmentType::DECREASE->value => "Stock removed for charitable donation",
				],

				// --- Other ---
				AdjustmentReason::OTHER->value => [
					AdjustmentType::INCREASE->value => "Miscellaneous inventory increase",
					AdjustmentType::DECREASE->value => "Miscellaneous inventory decrease",
				],
			];

			return $notes[$reason->value][$type->value] ?? "Regular adjustment of " . $quantity . " units";
		}

		/**
		 * Δημιουργεί μια τυχαία, ρεαλιστική σημείωση για κάθε γραμμή προσαρμογής (StockAdjustmentItem).
		 *
		 * @param AdjustmentType $type
		 * @param int $quantity
		 * @param AdjustmentReason $reason
		 * @return string
		 */
		private function generateAdjustmentItemNote(AdjustmentType $type, int $quantity, AdjustmentReason $reason): string {
			// Δημιουργία συλλογής με πλούσια ποικιλία μηνυμάτων ανά Reason και Type
			$notes = Collection::make([
				// 1. Απογραφή (Stocktake)
				AdjustmentReason::STOCKTAKE->value => [
					AdjustmentType::INCREASE->value => [
						"Βρέθηκαν επιπλέον τεμάχια κατά την ετήσια απογραφή.",
						"Πλεόνασμα απογραφής - διορθωτική εγγραφή γραμμής.",
						"Καταμέτρηση αποθήκης: Επιπλέον μονάδες στο ράφι.",
					],
					AdjustmentType::DECREASE->value => [
						"Έλλειμμα κατά την απογραφή αποθέματος.",
						"Απόκλιση καταμέτρησης - έλλειψη τεμαχίων.",
						"Διαφορά απογραφής (απώλεια τεμαχίων).",
					]
				],

				// 2. Ζημιές / Καταστροφές (Damaged)
				AdjustmentReason::DAMAGED->value => [
					AdjustmentType::DECREASE->value => [
						"Κατεστραμμένο προϊόν κατά τη διαχείριση στην αποθήκη.",
						"Ελαττωματική παρτίδα, ακατάλληλη προς πώληση.",
						"Ζημιά στη συσκευασία, κρίθηκε μη εμπορεύσιμο.",
						"Σπασμένα/αλλοιωμένα τεμάχια - Οριστική διαγραφή.",
					]
				],

				// 3. Λήξη Προϊόντων (Expired)
				AdjustmentReason::EXPIRED->value => [
					AdjustmentType::DECREASE->value => [
						"Λήξη ορίου ζωής προϊόντος - Απόσυρση και καταστροφή.",
						"Προϊόντα πέραν της ημερομηνίας λήξεως.",
						"Ληγμένα εμπορεύματα - Απομακρύνθηκαν από το ράφι.",
					]
				],

				// 4. Κλοπή (Theft)
				AdjustmentReason::THEFT->value => [
					AdjustmentType::DECREASE->value => [
						"Καταγραφή απώλειας λόγω κλοπής / έλλειμμα ασφαλείας.",
						"Απώλεια stock - Αναφορά κλοπής.",
						"Ύποπτη έλλειψη εμπορεύματος - Διαγραφή από το σύστημα.",
					]
				],

				// 5. Απώλεια (Lost)
				AdjustmentReason::LOST->value => [
					AdjustmentType::DECREASE->value => [
						"Απώλεια τεμαχίων κατά τη μεταφορά εντός των ζωνών.",
						"Αδιευκρίνιστη απώλεια stock κατά τη διαλογή.",
					]
				],

				// 6. Εύρεση (Found)
				AdjustmentReason::FOUND->value => [
					AdjustmentType::INCREASE->value => [
						"Εντοπίστηκαν τεμάχια σε λάθος ράφι.",
						"Εύρεση stock που δεν είχε καταχωρηθεί κατά την παραλαβή.",
						"Επανεισαγωγή εμπορεύματος - Βρέθηκαν ξεχασμένες κούτες.",
					]
				],

				// 7. Λάθος Καταμέτρηση (Counting Error)
				AdjustmentReason::COUNTING_ERROR->value => [
					AdjustmentType::INCREASE->value => [
						"Διόρθωση λάθους προηγούμενης καταμέτρησης (Επιπλέον).",
						"Λάθος καταχώρηση σε προηγούμενο δελτίο - Επανεκτίμηση.",
					],
					AdjustmentType::DECREASE->value => [
						"Διόρθωση σφάλματος εισαγωγής / Είχε μετρηθεί παραπάνω.",
						"Διορθωτική μείωση λόγω λανθασμένης αρχικής καταμέτρησης.",
					]
				],

				// 8. Οριστική Διαγραφή (Write Off)
				AdjustmentReason::WRITE_OFF->value => [
					AdjustmentType::DECREASE->value => [
						"Οριστική διαγραφή αποθέματος κατόπιν εντολής διοίκησης.",
						"Λογιστική τακτοποίηση - Write-off απαξιωμένου stock.",
					]
				],

				// 9. Διαρροή / Φύρα (Spillage)
				AdjustmentReason::SPILLAGE->value => [
					AdjustmentType::DECREASE->value => [
						"Διαρροή / Απώλεια υλικού κατά τη διαχείριση.",
						"Φύρα κατά τη διαδικασία συσκευασίας ή ανασυσκευασίας.",
					]
				],

				// 10. Λάθος Καταχώρηση Δεδομένων (Data Entry)
				AdjustmentReason::DATA_ENTRY->value => [
					AdjustmentType::INCREASE->value => [
						"Διόρθωση τυπογραφικού λάθους κατά την αρχική εισαγωγή.",
					],
					AdjustmentType::DECREASE->value => [
						"Ακύρωση λανθασμένης διπλής καταχώρησης ποσότητας.",
					]
				],

				// 11. Ποιοτικός Έλεγχος (Quality Control / Samples / Rejects)
				AdjustmentReason::QUALITY_CONTROL->value => [
					AdjustmentType::DECREASE->value => ["Δέσμευση stock για έλεγχο ποιότητας."],
					AdjustmentType::INCREASE->value => ["Αποδέσμευση εμπορεύματος μετά από επιτυχή έλεγχο."]
				],
				AdjustmentReason::QC_SAMPLE->value => [
					AdjustmentType::DECREASE->value => ["Αφαίρεση δειγμάτων για εργαστηριακό έλεγχο (QC)."]
				],
				AdjustmentReason::QC_REJECT->value => [
					AdjustmentType::DECREASE->value => ["Απόρριψη παρτίδας - Αποτυχία ποιοτικού ελέγχου."]
				],

				// 12. Δωρεές / Προωθητικά / Δείγματα (Donation / Promo / Demo / Sample)
				AdjustmentReason::DONATION->value => [
					AdjustmentType::DECREASE->value => [
						"Διάθεση αποθέματος για φιλανθρωπικούς σκοπούς / Χορηγία.",
						"Έγκριση δωρεάς εμπορευμάτων.",
					]
				],
				AdjustmentReason::PROMO->value => [
					AdjustmentType::DECREASE->value => [
						"Αφαίρεση stock για τις ανάγκες προωθητικής καμπάνιας.",
						"Marketing Promo - Διαφημιστική διάθεση.",
					]
				],
				AdjustmentReason::DEMO->value => [
					AdjustmentType::DECREASE->value => ["Δέσμευση συσκευής / προϊόντος ως εκθεσιακό κομμάτι (Demo)."]
				],
				AdjustmentReason::SAMPLE->value => [
					AdjustmentType::DECREASE->value => ["Διάθεση δωρεάν δειγμάτων σε πελάτες χονδρικής."]
				],

				// 13. Γενική Προσαρμογή (Adjustment / Other)
				AdjustmentReason::ADJUSTMENT->value => [
					AdjustmentType::INCREASE->value => ["Γενική διόρθωση πλεονάσματος είδους."],
					AdjustmentType::DECREASE->value => ["Γενική διόρθωση ελλείμματος είδους."]
				],
				AdjustmentReason::OTHER->value => [
					AdjustmentType::INCREASE->value => ["Λοιπή αιτιολογία αύξησης αποθέματος είδους."],
					AdjustmentType::DECREASE->value => ["Λοιπή αιτιολογία μείωσης αποθέματος είδους."]
				]
			]);

			// Ανάκτηση των διαθέσιμων επιλογών με dot notation (π.χ. "damaged.decrease")
			$specificNotes = data_get($notes, "{$reason->value}.{$type->value}");

			// Αν βρέθηκε το array με τις φράσεις, επιλέγουμε μία τυχαία μέσω Collection
			if (!empty($specificNotes)) {
				return Collection::make($specificNotes)->random();
			}

			// Δυναμικό Fallback σε περίπτωση που προκύψει μη προδιαγεγραμμένος συνδυασμός στο array
			$typeLabel = $type === AdjustmentType::INCREASE ? 'Πλεόνασμα' : 'Έλλειμμα';
			$reasonLabel = method_exists($reason, 'label') ? $reason->label() : $reason->value;

			return "Προσαρμογή γραμμής είδους ({$typeLabel}). Ποσότητα: {$quantity} τεμ. Αιτιολογία: {$reasonLabel}";
		}
	}
