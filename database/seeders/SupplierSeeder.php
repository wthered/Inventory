<?php

	namespace Database\Seeders;

	use App\Models\Product;
	use App\Models\Supplier;
	use DB;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
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
			for ($i = 0; $i < $this->rounds; $i++) {
				$response = Http::withHeaders([
					'X-API-Key' => '9a523780',
				])->get('https://my.api.mockaroo.com/product_suppliers.json');

				$this->requests++;
				if ($response->ok()) {
					Collection::make($response->json())->each(function ($supplier) {
						$this->list->push($supplier)->unique();
					});
//					$this->command->info('Fetching of ' . $this->list->count() . ' suppliers from external API has been successful.');
					$this->command->info('Fetching ' . $this->list->count() . ' suppliers from the external API was successful');
				} else {
					$this->command->error('❌ Failed to fetch suppliers from external API in iteration ' . ($i + 1) . ' of ' . $this->rounds . ' rounds.');
					for ($index = 0; $index < 1024; $index++) {
						$this->list->push([
							'code'           => Str::upper(Str::random(8))."-".Str::uuid7()->toString(),
							'name'           => fake()->name(),
							'company_name'   => fake()->company(),
							'email'          => fake()->email(),
							'phone'          => fake()->phoneNumber(),
							'tax_number'     => fake()->randomElement([
								0,
								8,
								18,
								24,
								36
							]),
							'address'        => fake()->address(),
							'city'           => fake()->city(),
							'state'          => json_encode($this->generateStates()->random()),
							'country'        => fake()->country(),
							'postal_code'    => fake()->postcode(),
							'contact_person' => fake()->name(),
							'contact_phone'  => fake()->phoneNumber(),
							'credit_limit'   => fake()->randomFloat(2, 0, 10000),
							'payment_terms'  => fake()->randomElement([
								'cash',
								'credit_7',
								'credit_15',
								'credit_30',
								'credit_60',
								'credit_90'
							]),
						]);
					}
				}
			}

			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
				Supplier::query()->insert($chunk->toArray());
			});

			$this->command->info('🔗 Linking suppliers to products...');

			// Παίρνουμε μόνο τα IDs των προμηθευτών για να γλιτώσουμε μνήμη
			$supplierIds = Supplier::pluck('id');

			// Χρησιμοποιούμε chunk για τα 5840 προϊόντα
			Product::chunk(self::BATCH_SIZE, function ($products) use ($supplierIds) {
				DB::transaction(function () use ($products, $supplierIds) {
					foreach ($products as $product) {
						// Επιλογή 1-3 τυχαίων IDs
						$randomIds = $supplierIds->random(mt_rand(1, 3));

						$syncData = [];
						foreach ($randomIds as $index => $id) {
							$syncData[$id] = [
								'price'          => fake()->randomFloat(2, $product->cost_price * 0.9, $product->cost_price * 1.4),
								'lead_time_days' => fake()->numberBetween(3, 21),
								'is_preferred'   => ($index === 0),
								'moq'            => fake()->numberBetween(1, 50),
								'created_at'     => now()->subSeconds(mt_rand(64, 24 * 3600 * 30)),
								'updated_at'     => now()->addSeconds(mt_rand(64, 24 * 3600 * 30)),
							];
						}
						// Χρησιμοποιούμε sync(..., false) για να μην διαγράψει υπάρχοντα αν ξανατρέξει
						$product->suppliers()->syncWithoutDetaching($syncData);
					}
				});
				$this->command->comment('Processed '.self::BATCH_SIZE.' products...');
			});

			$this->command->info('Creation of ' . $this->list->count() . ' suppliers ended with ' . $this->requests . ' requests.');
		}
	}
