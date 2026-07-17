<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Enums\Inventory\MovementStatus;
	use App\Enums\Inventory\TransferStatus;
	use App\Models\Category;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	class StockAdjustmentSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$warehouses = Warehouse::all();
			$users = User::pluck('id');

			$parentCategories = Category::query()->whereNull('parent_id')->with('children')->orderBy('sort_order')->pluck('id');

			if (Product::all()->isEmpty()) {
				$this->command->error('No products found. Skipping Adjustments.');
				return;
			}

			$warehouses->each(function (Warehouse $warehouse) use ($users, $parentCategories) {

				$parentCategory = $parentCategories->shuffle()->random();
				$categories = [
					'parent' => $parentCategory,
					'children' => Category::query()->where('parent_id', $parentCategory)->pluck('id'),
				];

				$products = Product::whereHas('category', function ($query) use ($categories) {
					$query->whereIn('id', $categories['children']);
				})->get();

				if ($products->isEmpty()) {
					$products = Product::query()->get()->shuffle()->take(self::BATCH_SIZE);
				}

				// Περιορίζουμε λίγο τα adjustments ανά αποθήκη για ταχύτητα (π.χ. 6 - 12)
				Collection::range(1, mt_rand(6, 12))->each(function ($i) use ($users, $warehouse, $products) {

					$createdAt = Carbon::now(config('app.timezone'))->subDays(mt_rand(1, today()->dayOfYear));
					$status = fake()->randomElement(MovementStatus::cases());

					$adjustment = StockAdjustment::create([
						'adjustment_number' => 'ADJ-' . Str::padLeft($i, 2, '0') . '-' . Str::upper(Str::random(8)),
						'warehouse_id'      => $warehouse->id,
						'adjustment_date'   => $createdAt,
						'status'            => $status->value,
						'created_by'        => $users->random(),
						'approved_by'       => $status === MovementStatus::APPROVED ? $users->random() : null,
						'approved_at'       => $status === MovementStatus::APPROVED ? $createdAt->addHours(2) : null,
						'notes'             => 'Monthly stock reconciliation',
					]);

					// Παίρνουμε τοποθεσίες ΜΟΝΟ για τη συγκεκριμένη αποθήκη
					$warehouseLocations = WarehouseLocation::query()->where('warehouse_id', $warehouse->id)->pluck('id');
					if ($warehouseLocations->isEmpty()) {
						return;
					}

					// 8 - 16 items ανά adjustment είναι υπεραρκετά
					$itemsCount = mt_rand(8, 16);

					Collection::range(1, $itemsCount)->each(function () use ($adjustment, $warehouseLocations, $products) {
						$product = $products->random();
						$type = fake()->randomElement([AdjustmentType::INCREASE, AdjustmentType::DECREASE]);
						$reasons = $type === AdjustmentType::INCREASE ? AdjustmentReason::increaseReasons() : AdjustmentReason::decreaseReasons();

						$adjustment->items()->create([
							'product_id'      => $product->id,
							'location_id'     => $warehouseLocations->random(), // ✅ Σωστή τοποθεσία
							'reason'          => fake()->randomElement($reasons)->value,
							'type'            => $type->value,
							'quantity'        => mt_rand(1, 24),
							'quantity_before' => $product->quantity ?? 0,
							'unit_cost'       => $product->cost_price ?? mt_rand(16, 128),
							'notes'           => 'Verified by floor supervisor',
						]);
					});

					$this->command->info("Created Adjustment " . $adjustment->adjustment_number." with ".Str::padLeft($itemsCount, 2)." items into " . $warehouse->name . ".");
				});
			});
		}
	}
