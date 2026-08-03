<?php

	namespace Database\Seeders;

	use App\Models\Product;
	use App\Models\Supplier;
	use Carbon\Carbon;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Http;

	class SupplierSeeder extends ParentSeeder {

		protected int $rounds;

		public function __construct() {
			parent::__construct();
			$this->rounds = 8;
		}

		/**
		 * Run the database seeds.
		 *
		 * @throws ConnectionException|\Throwable
		 */
		public function run(): void {
			$this->list = Collection::empty();
			$creation_time = Carbon::now(config('app.timezone'));

			$this->command->info('📡 Fetching supplier data from Internet & mapping via SupplierFactory...');

			for ($i = 0; $i < $this->rounds; $i++) {
				$response = Http::withHeaders([
					'X-API-Key' => '9a523780', // Κρατάμε το mockaroo token σου
				])->get('https://my.api.mockaroo.com/product_suppliers.json');

				$this->requests++;

				if ($response->failed()) {
					$this->command->error('❌ Mockaroo API dropped connection. Generating fallback suppliers via Factory.');

					// Fallback αν το API αποτύχει
					$fallbackSuppliers = Supplier::factory()->count(32)->raw([
						'is_active'  => fake()->boolean(),
						'created_at' => $creation_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => $creation_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
					]);
					$this->list = $this->list->merge($fallbackSuppliers);
					continue;
				}

				$formatted = Collection::make($response->json())->map(function ($supplier) use ($creation_time) {
					// Filter out null/missing values from API response so Factory fallbacks kick in
					$apiData = array_filter([
						'code'           => $supplier['code'] ?? null,
						'name'           => $supplier['name'] ?? null,
						'company_name'   => $supplier['company_name'] ?? null,
						'email'          => $supplier['email'] ?? null,
						'phone'          => $supplier['phone'] ?? fake()->regexify('(2[0-9]{9}|69[0-9]{8})'),
						'website'        => $supplier['website'] ?? null,
						'tax_number'     => isset($supplier['tax_number']) ? (string) $supplier['tax_number'] : null,
						'address'        => $supplier['address'] ?? null,
						'city'           => $supplier['city'] ?? null,
						'state'          => $supplier['state'] ?? null,
						'country'        => $supplier['country'] ?? null,
						'postal_code'    => $supplier['postal_code'] ?? null,
						'contact_person' => $supplier['contact_person'] ?? null,
						'contact_phone'  => $supplier['contact_phone'] ?? null,
						'credit_limit'   => $supplier['credit_limit'] ?? null,
						'payment_terms'  => $supplier['payment_terms'] ?? null,
						'notes'          => $supplier['notes'] ?? null,
						'is_active'      => $supplier['is_active'] ?? fake()->boolean(),
					], fn($value) => !is_null($value));

					// Combine custom timestamps with filtered API data
					return Supplier::factory()->raw(array_merge($apiData, [
						'created_at' => $creation_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => $creation_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
					]));
				});

				$this->list = $this->list->concat($formatted);
			}

			// Ασφαλές Chunked Bulk Insert
			if ($this->list->isNotEmpty()) {
				$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
					DB::table('suppliers')->insert($chunk->values()->toArray());
				});
			}

			$this->command->info('🔗 Linking suppliers to products via optimized pivot chunking...');

			// Παίρνουμε ΜΟΝΟ τα IDs των προμηθευτών για να προστατεύσουμε τη RAM
			// Forces a direct query against the table
			$suppliers = DB::table('suppliers')->pluck('id');

			if ($suppliers->isEmpty()) {
				$this->command->error('No suppliers available to link with products.');
				return;
			}

			// Χρησιμοποιούμε chunk() στα προϊόντα για να μη σταματήσει ο Seeder (OOM Safe)
			Product::query()->chunk(self::BATCH_SIZE, function ($products) use ($suppliers) {
				DB::transaction(function () use ($products, $suppliers) {
					$pivotBuffer = [];

					foreach ($products as $product) {
						// Επιλογή 2 - 8 τυχαίων IDs προμηθευτών
						$randomSuppliers = $suppliers->random(mt_rand(2, min(8, $suppliers->count())))->values();

						foreach ($randomSuppliers as $index => $id) {
							$pivotBuffer[] = [
								'product_id'     => $product->id,
								'supplier_id'    => $id,
								'price'          => fake()->randomFloat(2, $product->cost_price * 0.9, $product->cost_price * 1.4),
								'lead_time_days' => fake()->numberBetween(3, 21),
								'is_preferred'   => $index === 0,
								'moq'            => fake()->numberBetween(1, 64),
								'created_at'     => now()->subSeconds(mt_rand(64, 24 * 3600 * 30)),
								'updated_at'     => now()->addSeconds(mt_rand(64, 24 * 3600 * 30)),
							];
						}

						// Overwrites the same line on each iteration
						$this->command->getOutput()->write("Finished product #".$product->id."...\r");
					}

					// Μαζικό Insert στον Pivot πίνακα για το συγκεκριμένο chunk
					if (!empty($pivotBuffer)) {
						DB::table('product_supplier')->insert($pivotBuffer);
					}
				});
			});

			$this->command->info('✅ Suppliers and product links successfully seeded using automated Factory pipelines.');
		}
	}