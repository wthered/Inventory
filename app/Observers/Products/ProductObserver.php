<?php

	namespace App\Observers\Products;

	use App\Models\Product;
	use App\Models\Products\ProductHistory;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Exception;

	class ProductObserver {
		/**
		 * Ενέργεια: created (Δημιουργήθηκε)
		 */
		public function created(Product $product): void {
			// Καταγραφή της αρχικής δημιουργίας
			ProductHistory::query()->create([
				'product_id' => $product->id,
				'user_id'    => Auth::id(),
				'action'     => $product->is_cloned ? 'cloned' : 'created',
				'details'    => [
					'name'          => $product->name,
					'initial_stock' => $product->stock ?? 0,
				],
			]);
		}

		/**
		 * Ενέργεια: updated (Ενημερώθηκε)
		 */
		public function updated(Product $product): void {
			$userId          = Auth::id();
			$monitoredFields = [
				'name',
				'price',
				'cost'
			];

			// 1. Καταγραφή Αλλαγών Metadata & Financial (όπως πριν)
			foreach ($monitoredFields as $field) {
				if ($product->isDirty($field)) {
					ProductHistory::query()->create([
						'product_id' => $product->id,
						'user_id'    => $userId,
						'action'     => $field.'_updated',
						'details'    => [
							'old_value' => $product->getOriginal($field),
							'new_value' => $product->$field,
						],
					]);
				}
			}

			// 2. Ειδική Καταγραφή: Αλλαγή Αποθέματος (Stock Adjustment)
			// Αυτή η λογική ενεργοποιείται μόνο αν το πεδίο 'stock' έχει αλλάξει.
			if ($product->isDirty('stock') && !$product->isDirty('deleted_at')) {
				ProductHistory::query()->create([
					'product_id' => $product->id,
					'user_id'    => $userId,
					'action'     => 'stock_adjusted',
					'details'    => [
						'old_stock' => $product->getOriginal('stock'),
						'new_stock' => $product->current_stock,
						'change'    => $product->current_stock - $product->getOriginal('current_stock'),
						// Σημείωση: Αν η αλλαγή stock οφείλεται σε πώληση, πρέπει να καταγραφεί μέσω του Order Observer, όχι εδώ.
						'source'    => 'Manual adjustment',
					],
				]);
			}

			// 3. Ειδική Καταγραφή: Αρχειοθέτηση (Soft Delete - The 'archived' action)
			// Το 'deleted_at' πεδίο μόλις ορίστηκε (είναι 'dirty')
			if ($product->isDirty('deleted_at') && $product->deleted_at !== null && !$product->getOriginal('deleted_at')) {
				ProductHistory::query()->create([
					'product_id' => $product->id,
					'user_id'    => $userId,
					'action'     => 'archived',
					'details'    => [
						'name'    => $product->name,
						'message' => 'Product was soft deleted (archived).',
					],
				]);
			}
		}

		/**
		 * Ενέργεια: deleted (Οριστική Διαγραφή)
		 * ΣΗΜΕΙΩΣΗ: Αν χρησιμοποιείτε Soft Deletes, αυτό πυροδοτείται μόνο στο $product->forceDelete().
		 */
		public function deleted(Product $product): void {
			// Ελέγχουμε αν πρόκειται για οριστική διαγραφή (όχι soft delete)
			if ($product->isForceDeleting()) {
				ProductHistory::query()->create([
					'product_id' => $product->id,
					'user_id'    => Auth::id(),
					'action'     => 'deleted',
					'details'    => [
						'name'    => $product->getOriginal('name'),
						'message' => 'Product permanently removed from database.',
					],
				]);
			}
		}

		/**
		 * Ενέργεια: restored (Επαναφορά)
		 */
		public function restored(Product $product): void {
			// Καταγραφή της επαναφοράς από το αρχείο
			ProductHistory::query()->create([
				'product_id' => $product->id,
				'user_id'    => Auth::id(),
				'action'     => 'restored',
				'details'    => [
					'name'    => $product->name,
					'message' => 'Product was restored from archive.',
				],
			]);
		}

		/**
		 * Ενέργεια: replicated (Cloned - Δημιουργία Αντιγράφου)
		 * Αυτή η μέθοδος πυροδοτείται όταν καλείται η μέθοδος $product->replicate().
		 * Τρέχει ΠΡΙΝ το created συμβάν του νέου μοντέλου.
		 */
		public function replicated(Product $clonedProduct): void {
			// Το clonedProduct είναι το νέο μοντέλο που πρόκειται να αποθηκευτεί.
			// Το originalProduct είναι το μοντέλο από το οποίο έγινε η αντιγραφή.

			// ΣΗΜΑΝΤΙΚΟ: Το created συμβάν του νέου προϊόντος θα τρέξει αμέσως μετά.
			// Μπορούμε να καταγράψουμε την πηγή αντιγραφής εδώ ή στο created.

			// Εάν θέλουμε να καταγράψουμε το event στον ΠΙΝΑΚΑ ΙΣΤΟΡΙΚΟΥ του ΠΡΩΤΟΤΥΠΟΥ:
			// $originalProductId = $clonedProduct->getOriginal('id');
			// ProductHistory::create([ ... action => 'was_cloned_from', product_id => originalId, ... ])

			// Εάν θέλουμε να καταγράψουμε το event στον ΠΙΝΑΚΑ ΙΣΤΟΡΙΚΟΥ του ΑΝΤΙΓΡΑΦΟΥ:
			// Επειδή το created θα τρέξει αμέσως μετά,
			// μπορούμε να προσθέσουμε μια ιδιότητα στο νέο μοντέλο
			// για να ενημερώσουμε τη μέθοδο 'created' ότι πρόκειται για κλώνο.
			$clonedProduct->is_cloned = true;
		}

		/**
		 * Ενέργεια: creating (Πριν τη Δημιουργία)
		 */
		public function creating(Product $product): void {
			// Auto-generate SKU if not provided
			if (empty($product->sku)) {
				$product->sku = $this->generateUniqueSku();
			}

			// Auto-generate slug
			if (empty($product->slug)) {
				$product->slug = Str::slug($product->name);
			}
		}

		/**
		 * @throws Exception
		 */
		private function generateUniqueSku(): string {
			$maxAttempts = 100;
			$attempts    = 0;

			do {
				$sku = Str::upper(fake()->bothify('SKU-####-????-'.Str::random(4)));
				$attempts++;

				if ($attempts >= $maxAttempts) {
					throw new Exception('Failed to generate unique SKU after '.$maxAttempts.' attempts');
				}
			} while (Product::query()
				->where('sku', $sku)
				->exists());

			return $sku;
		}

		/**
		 * Ενέργεια: saving (Πριν Αποθήκευση)
		 */
		public function saving(Product $product): void {
			// Ensure prices are valid
			if ($product->price < 0) {
				$product->price = 0;
			}
			if ($product->cost < 0) {
				$product->cost = 0;
			}
			if ($product->stock < 0) {
				$product->stock = 0;
			}
		}
	}
