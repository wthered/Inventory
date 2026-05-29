<?php

	namespace Database\Seeders\inventories;

	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Product;
	use App\Models\User;
	use Database\Seeders\inventories\Concerns\CanSeedAdjustments;
	use Database\Seeders\inventories\Concerns\CanSeedPurchases;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Throwable;

	class InventoryTransactionsSeeder extends ParentSeeder {
		// Ενσωμάτωση των Traits από το σωστό namespace
		use CanSeedPurchases, CanSeedAdjustments;

		/**
		 * @throws Throwable
		 */
		public function run(): void {
			// 1. Προετοιμασία δεδομένων (Μόνο IDs για μνήμη)
			$products = Product::where('track_inventory', true)->get();
			$users    = User::pluck('id');

			if ($products->isEmpty() || $users->isEmpty()) {
				$this->command->error('Required data (products/users) not found for Transactions.');
				return;
			}

			$this->list = Collection::empty();

			// 2. Εκτέλεση των επιμέρους Seeders

			// Από το CanSeedPurchases Trait
			$this->seedPurchases($products, $users);

			// Από το CanSeedAdjustments Trait
			$this->seedAdjustments($products, $users);

			$this->command->info('Inventory transactions (Purchases & Adjustments) completed!');
		}

		/**
		 * Η υλοποίηση της flushList που απαιτούν τα Traits
		 */
		public function flushList(): void {
			if ($this->list->isEmpty()) {
				return;
			}

			InventoryTransaction::insert($this->list->toArray());

			// Καθαρισμός μνήμης
			$this->list = Collection::empty();
		}
	}