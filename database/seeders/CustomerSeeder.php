<?php

	namespace Database\Seeders;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use App\Models\Customer;
	use Carbon\Carbon;
	use Illuminate\Database\QueryException;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Str;

	class CustomerSeeder extends ParentSeeder {
		private int $rounds = 8;

		/**
		 * Run the database seeds.
		 *
		 * @throws ConnectionException
		 */
		public function run(): void {
			$this->command->info('📡 Fetching customer data from Internet...');
			$this->createCustomerList();
			$this->command->info('✅ Customer seeding complete with ' . $this->requests . ' requests done and ' . Customer::count() . ' customers.!');
		}

		/**
		 * @throws ConnectionException
		 */
		private function createCustomerList(): void {
			$this->list = Collection::empty();
			$creation_time = Carbon::now(config('app.timezone'));
			for ($round = 0; $round < $this->rounds; $round++) {
				// Make the API request
				$response = Http::withHeaders([
					'X-API-Key' => config('services.mockaroo.token'),
				])->get('https://my.api.mockaroo.com/customers.json');

				$this->requests++;

				if ($response->failed()) {
					$this->command->error('❌ Failed to fetch customers from external API');
					$customerStates = $this->generateStates();
					for ($index = 0; $index < 1024; $index++) {
						$customerState = $customerStates->random();
						$this->list->push([
							'code'             => Str::uuid7()->toString(),
							'name'             => fake()->company(),
							'email'            => fake()->freeEmail(),
							'phone'            => fake()->phoneNumber(),
							'company_name'     => fake()->company(),
							'tax_number'       => fake()->randomElement([
								0,
								8,
								18,
								24,
								36
							]),
							'billing_address'  => fake()->streetAddress(),
							'shipping_address' => fake()->address(),
							'city'             => fake()->city(),
							'state'            => $customerState['name'],
							'country'          => fake()->country(),
							'postal_code'      => fake()->postcode(),
							'customer_type'    => fake()->randomElement(CustomerType::cases())->value,
							'credit_limit'     => fake()->randomFloat(2, 0, 8 * 1024),
							'payment_terms'    => fake()->randomElement(PaymentTerms::cases())->value,
							'notes'            => fake()->realText(),
							'is_active'        => fake()->boolean(),
							'created_at'       => $creation_time->subHours(mt_rand(1, 23))->subMinutes(mt_rand(1, 59))->subSeconds(mt_rand(1, 59))->timezone(config('app.timezone'))->toDateTimeString(),
							'updated_at'       => $creation_time->addHours(mt_rand(1, 23))->addMinutes(mt_rand(1, 59))->addSeconds(mt_rand(1, 59))->timezone(config('app.timezone'))->toDateTimeString(),
						]);
					}
				} else {
					// ΣΠΟΥΔΑΙΟ: Χρησιμοποίησε merge για να μην χάνεις τους προηγούμενους γύρους
					$newData = Collection::make($response->json());
					$this->list = $this->list->merge($newData)->unique('code')->values();
//					$this->command->info("Fetched " . $this->list->count() . " customers from external API");
				}
				$this->command->info('[Round ' . Str::padLeft($round + 1, Str::length(8)) . ' of ' . $this->rounds . '] 💾 Will seed ' . $this->list->count() . ' customers...');
			}

			// Το Insert πρέπει να γίνει ΕΞΩ από το loop (όπως το έχεις, αλλά τώρα η λίστα θα είναι γεμάτη)
			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $customers) {
				try {
					Customer::query()->insert($customers->shuffle()->toArray());
				} catch (QueryException $e) {
					// Log the error for debugging
					Log::error('Failed to create customer: ' . $e->getMessage());
					Log::error('Customer data: ', $customers->toArray());
				}
			});

			$this->command->info("We have found " . Customer::query()->count() . " customers in Database.");
			$this->list = Customer::query()->pluck('id');
			Customer::query()->where('updated_at', '<', 'created_at')->get()->each(function ($customer) use (&$creation_time) {
				$customer->update([
					'updated_at' => $creation_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
				]);
			});
		}
	}
