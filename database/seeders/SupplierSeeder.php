<?php

	namespace Database\Seeders;

	use App\Models\Product;
	use App\Models\Supplier;
	use Carbon\Carbon;
	use Database\Factories\SupplierFactory;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Str;

	class SupplierSeeder extends ParentSeeder {

		protected int $rounds;

		public function __construct() {
			parent::__construct();
			$this->rounds = 8;
		}

		/**
		 * Run the database seeds.
		 *
		 * @throws ConnectionException
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
					$fallbackSuppliers = SupplierFactory::new()->count(32)->raw([
						'created_at' => $creation_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => $creation_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
					]);
					$this->list = $this->list->merge($fallbackSuppliers);
					continue;
				}

				$apiSuppliers = Collection::make($response->json());

				$formatted = $apiSuppliers->map(function ($supplier) use ($creation_time) {
					// Χρήση του SupplierFactory::raw() για ασφαλή κατασκευή array δεδομένων
					return SupplierFactory::new()->raw([
						'code'           => $supplier['code'] ?? 'SUPP-' . Str::upper(Str::random(5)) . mt_rand(100, 999),
						'name'           => $supplier['name'] ?? fake()->company(),
						'company_name'   => $supplier['company_name'] ?? null,
						'email'          => $supplier['email'] ?? null,
						'phone'          => $supplier['phone'] ?? fake()->phoneNumber(),
						'website'        => $supplier['website'] ?? null,
						'tax_number'     => $supplier['tax_number'] ?? null,
						'address'        => $supplier['address'] ?? null,
						'city'           => $supplier['city'] ?? null,
						'state'          => $supplier['state'] ?? null,
						'country'        => $supplier['country'] ?? null,
						'postal_code'    => $supplier['postal_code'] ?? null,
						'contact_person' => $supplier['contact_person'] ?? null,
						'contact_phone'  => $supplier['contact_phone'] ?? null,
						'credit_limit'   => $supplier['credit_limit'] ?? 0,
						'payment_terms'  => $supplier['payment_terms'] ?? 'cash',
						'notes'          => $supplier['notes'] ?? null,
						'is_active'      => !isset($supplier['is_active']) || $supplier['is_active'],
						'created_at'     => $creation_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at'     => $creation_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
					]);
				});

				$this->list = $this->list->merge($formatted);
			}

			// Ασφαλές Chunked Bulk Insert
			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
				Supplier::query()->insert($chunk->toArray());
			});

			$this->command->info('🔗 Linking suppliers to products via optimized pivot chunking...');

			// Παίρνουμε ΜΟΝΟ τα IDs των προμηθευτών για να προστατεύσουμε τη RAM
			$supplierIds = Supplier::pluck('id');

			if ($supplierIds->isEmpty()) {
				$this->command->error('No suppliers available to link with products.');
				return;
			}

			// Χρησιμοποιούμε chunk() στα προϊόντα για να μην κρασάρει ο Seeder (OOM Safe)
			Product::chunk(self::BATCH_SIZE, function ($products) use ($supplierIds) {
				DB::transaction(function () use ($products, $supplierIds) {
					$pivotBuffer = [];

					foreach ($products as $product) {
						// Επιλογή 1-3 τυχαίων IDs προμηθευτών
						$randomIds = $supplierIds->random(mt_rand(1, min(3, $supplierIds->count())));

						foreach ($randomIds as $index => $id) {
							$pivotBuffer[] = [
								'product_id'     => $product->id,
								'supplier_id'    => $id,
								'price'          => fake()->randomFloat(2, $product->cost_price * 0.9, $product->cost_price * 1.4),
								'lead_time_days' => fake()->numberBetween(3, 21),
								'is_preferred'   => ($index === 0),
								'moq'            => fake()->numberBetween(1, 64),
								'created_at'     => now()->subSeconds(mt_rand(64, 24 * 3600 * 30)),
								'updated_at'     => now()->addSeconds(mt_rand(64, 24 * 3600 * 30)),
							];
						}
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