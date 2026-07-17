<?php

	namespace App\Services\Stock;

	use App\Models\StockAdjustment;
	use App\Models\WarehouseLocation;
	use Illuminate\Support\Facades\DB;
	use Exception;
	use Throwable;

	class InventoryStockService {

		/**
		 * Ενημερώνει το πραγματικό απόθεμα στην αποθήκη με βάση τα items της προσαρμογής.
		 *
		 * @param  StockAdjustment  $adjustment
		 *
		 * @return void
		 * @throws Exception|Throwable
		 */
		public function updatePhysicalInventory(StockAdjustment $adjustment): void {
			// Χρησιμοποιούμε Database Transaction για να διασφαλίσουμε ότι είτε θα ενημερωθούν όλα τα προϊόντα είτε κανένα (σε περίπτωση σφάλματος)
			DB::transaction(function () use ($adjustment) {

				// Φορτώνουμε τα items αν δεν είναι ήδη φορτωμένα
				$adjustment->loadMissing('items');

				foreach ($adjustment->items as $item) {

					$location = WarehouseLocation::query()->find($item->location_id);

					// 1. Εντοπισμός της εγγραφής αποθέματος (Inventory) για το συγκεκριμένο προϊόν και τοποθεσία
					// Σημείωση: Αν έχεις Eloquent Model π.χ. Inventory, μπορείς να χρησιμοποιήσεις εκείνο.
					// Εδώ χρησιμοποιούμε DB query στον pivot πίνακα της σχέσης `inventories()` των προϊόντων σου.
					$inventory = DB::table('inventories')
					               ->where('product_id', $item->product_id)
					               ->where('location_id', $location->id)
					               ->first();

					if (!$inventory) {
						// Αν για κάποιο λόγο δεν υπάρχει καν η εγγραφή στον πίνακα αποθεμάτων, τη δημιουργούμε με αρχική ποσότητα 0
						$inventoryId = DB::table('inventories')->insertGetId([
							'product_id'  => $item->product_id,
							'location_id' => $item->location_id,
							'warehouse_id' => $location->warehouse_id,
							'quantity'    => 0,
							'created_at'  => now(),
							'updated_at'  => now(),
						]);

						$currentQuantity = 0;
					} else {
						$currentQuantity = $inventory->quantity;
					}

					// 2. Υπολογισμός της νέας ποσότητας
					// Το $item->quantity περιέχει ήδη το πρόσημο (+ ή -) ανάλογα με την αύξηση/μείωση
					$newQuantity = $currentQuantity + $item->quantity;

					if ($newQuantity < 0) {
						throw new Exception("Το απόθεμα για το προϊόν ID {$item->product_id} στη θέση ID {$item->location_id} δεν μπορεί να γίνει αρνητικό ({$newQuantity}).");
					}

					// 3. Ενημέρωση του πραγματικού αποθέματος στην αποθήκη
					DB::table('inventories')
					  ->where('product_id', $item->product_id)
					  ->where('location_id', $item->location_id)
					  ->update([
						  'quantity'   => $newQuantity,
						  'updated_at' => now(),
					  ]);

					// 4. Ενημέρωση της στήλης quantity_before και quantity_after στο item της προσαρμογής για ιστορικότητα
					$item->update([
						'quantity_before' => $currentQuantity,
						'quantity_after'  => $newQuantity
					]);
				}
			});
		}
	}