<?php

	namespace Database\Seeders\products;

	use App\Models\Product;
	use App\Models\Supplier;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class SupplierProductSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$products  = Product::query()->pluck('id');
			$suppliers = Supplier::query()->pluck('id');

			if ($products->isEmpty() || $suppliers->isEmpty()) {
				$this->command->warn('⚠️ No products or suppliers found. Seed them first!');
				return;
			}

			$pivotData = Collection::empty();

			$suppliers_number = $suppliers->count();
			$products_number  = $products->count();
			$suppliers->each(function ($supplier) use (&$products, &$suppliers_number, &$products_number) {
				$supplier_entry = Supplier::query()->find($supplier);
				// Each supplier will sell 5 – 10 random products
				foreach ($products->random(mt_rand(2, 10)) as $product) {
					$supplier_time = Carbon::now(config('app.timezone'))->subHours(mt_rand(1, 23))->subMinutes(mt_rand(1, 59))->subSeconds(mt_rand(1, 59));
					$supplier_entry->products()->attach($product, [
						'price'          => fake()->randomFloat(2, 5, 500),
						'lead_time_days' => fake()->numberBetween(1, 10),
						'moq'            => fake()->numberBetween(5, 20),
						'is_preferred'   => fake()->boolean(round(100 / ($suppliers_number * $products_number))),
						'created_at'     => $supplier_time->toDateTimeString(),
						'updated_at'     => $supplier_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59))->toDateTimeString()
					]);
				}
			});

			$pivotData->chunk(64)->each(function (Collection $suppliers_chunk) {
				DB::table('suppliers_products')->insert($suppliers_chunk->toArray());
			});

//			DB::table('suppliers_products')->insert($pivotData->toArray());

			$this->command->info('✅ Supplier–Product pivot table seeded successfully!');
		}
	}
