<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\MovementStatus;
	use App\Models\Product;
	use App\Models\StockAdjustment;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Str;

	class StockAdjustmentSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$warehouses = Warehouse::all();
			$userIds = User::query()->pluck('id');

			if (!Product::query()->exists()) {
				$this->command->error('No products found. Skipping Adjustments.');
				return;
			}

			// Pre-fetch category tree mapping to avoid queries inside loops
//			$parentCategories = Category::query()->whereNull('parent_id')->pluck('id');

			foreach ($warehouses as $warehouse) {
				// Fetch warehouse locations as flat array
				$locations = $warehouse->locations->pluck('id');

				if ($locations->isEmpty()) {
					continue;
				}

				// Pick relevant products for this warehouse (chunked to preserve memory)
//				$randomCategory = $parentCategories->isNotEmpty() ? $parentCategories->random() : null;
//				$childCategoryIds = $randomCategory
//					? Category::query()->where('parent_id', $randomCategory)->pluck('id')
//					: collect();

//				$products = Product::query()
//				                   ->when($childCategoryIds->isNotEmpty(), function($q) use ($childCategoryIds) {
//					                   $q->whereIn('category_id', $childCategoryIds);
//								   })->limit(100)
//				                   ->get(['id', 'cost_price', 'current_stock']);

//				if ($products->isEmpty()) {
//					$products = Product::query()->inRandomOrder()->limit(50)->get([
//						'id', 'cost_price', 'current_stock'
//					]);
//				}

				for ($i = 1; $i <= mt_rand(6, 12); $i++) {
					$createdAt = Carbon::now()->subDays(mt_rand(1, 180));
					$status = fake()->randomElement(MovementStatus::cases());

					// Insert Adjustment Master Record
//					$adjustmentId = DB::table('stock_adjustments')->insertGetId([
					$adjustment = StockAdjustment::query()->create([
						'adjustment_number' => 'ADJ-'.Str::padLeft($i, 2, '0').'-'.Str::upper(Str::random(8)),
						'warehouse_id'      => $warehouse->id,
						'adjustment_date'   => $createdAt,
						'status'            => $status->value,
						'created_by'        => $userIds->random(),
						'approved_by'       => $status === MovementStatus::APPROVED ? $userIds->random() : null,
						'approved_at'       => $status === MovementStatus::APPROVED ? $createdAt->copy()->addHours(2) : null,
						'notes'             => 'Monthly stock reconciliation',
						'created_at'        => $createdAt,
						'updated_at'        => $createdAt,
					]);

					// Bulk Insert Adjustment Items
//					$itemsCount = mt_rand(8, 16);
					$itemsBatch = [];

//					for ($j = 0; $j < $itemsCount; $j++) {
//						$product = $products->random();
//						$type = fake()->randomElement([AdjustmentType::INCREASE, AdjustmentType::DECREASE]);
//						$reasons = $type === AdjustmentType::INCREASE
//							? AdjustmentReason::increaseReasons()
//							: AdjustmentReason::decreaseReasons();
//
//						$this->list->push([
//							'stock_adjustment_id' => $adjustmentId, // Adjust foreign key column name if different
//							'product_id'          => $product->id,
//							'location_id'         => $locationIds->random(),
//							'reason'              => fake()->randomElement($reasons)->value,
//							'type'                => $type->value,
//							'quantity'            => mt_rand(1, 24),
//							'quantity_before'     => $product->current_stock ?? 0,
//							'unit_cost'           => $product->cost_price ?? mt_rand(16, 128),
//							'notes'               => 'Verified by floor supervisor',
//							'created_at'          => $createdAt,
//							'updated_at'          => $createdAt,
//						]);
//					}
//
//					DB::table('stock_adjustment_items')->insert($this->list->toArray());

					$this->command->getOutput()->write("Created Adjustment ".$adjustment->adjustment_number."\r");
				}

				$this->command->info("\nEnd of Adjustments for ".$warehouse->name);
			}
		}
	}
