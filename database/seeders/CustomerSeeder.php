<?php

	namespace Database\Seeders;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use App\Models\Customer;
	use Carbon\Carbon;
	use Database\Factories\CustomerFactory;
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
			$this->command->info('📡 Fetching customer data from Internet & mapping via CustomerFactory...');
			$this->createCustomerList();
			$this->command->info('✅ Customer seeding complete with ' . $this->requests . ' requests done and ' . Customer::query()->count() . ' customers.!');
		}

		/**
		 * @throws ConnectionException
		 */
		private function createCustomerList(): void {
			$creation_time = Carbon::now(config('app.timezone'));

			for ($round = 0; $round < $this->rounds; $round++) {
				$response = Http::withHeaders([
					'X-API-Key' => config('services.mockaroo.token'),
				])->get('https://my.api.mockaroo.com/customers.json');

				$this->requests++;

				if ($response->failed()) {
					$this->command->error('❌ Mockaroo API dropped connection. Using fallback CustomerFactory state.');

					// Fallback: Αν πέσει το API, παράγουμε 64 records από το Factory απευθείας για να μη σταματήσει το build
					$fallbackData = CustomerFactory::new()->count(64)->raw([
						'created_at' => $creation_time,
						'updated_at' => $creation_time,
					]);
					$this->list = $this->list->merge($fallbackData);
					continue;
				}

				// Επιτυχία API: Mapping των εξωτερικών δεδομένων πάνω στο Factory structure
				$apiCustomers = Collection::make($response->json());

				$formatted = $apiCustomers->map(function ($customer) use ($creation_time) {
					// Χρήση του factory ->raw() για να εξασφαλίσουμε ομοιομορφία
					// και συμπλήρωση τυχόν πεδίων που λείπουν από το API payload
					return CustomerFactory::new()->raw([
						'code'             => $customer['code'] ?? 'CUST-' . Str::upper(Str::random(5)) . mt_rand(100, 999),
						'name'             => $customer['name'] ?? fake()->name(),
						'email'            => $customer['email'] ?? null,
						'phone'            => $customer['phone'] ?? fake()->phoneNumber(),
						'company_name'     => $customer['company_name'] ?? null,
						'tax_number'       => $customer['tax_number'] ?? null,
						'billing_address'  => $customer['billing_address'] ?? null,
						'shipping_address' => $customer['shipping_address'] ?? null,
						'city'             => $customer['city'] ?? null,
						'state'            => $customer['state'] ?? null,
						'country'          => $customer['country'] ?? null,
						'postal_code'      => $customer['postal_code'] ?? null,
						'customer_type'    => $customer['customer_type'] ?? CustomerType::INDIVIDUAL->value,
						'credit_limit'     => $customer['credit_limit'] ?? 0,
						'payment_terms'    => $customer['payment_terms'] ?? PaymentTerms::CASH->value,
						'notes'            => $customer['notes'] ?? null,
						'is_active'        => !isset($customer['is_active']) || $customer['is_active'],
						'created_at'       => $creation_time,
						'updated_at'       => $creation_time,
					]);
				});

				$this->list = $this->list->merge($formatted);
			}

			// Chunked Upsert για προστασία μνήμης και αποφυγή Integrity Constraint Violations
			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $customers) {
				try {
					$formattedCustomers = $customers->toArray();

					Customer::query()->upsert(
						$formattedCustomers,
						['code'], // Μοναδικό key
						[
							'name', 'email', 'phone', 'company_name', 'tax_number',
							'billing_address', 'shipping_address', 'city', 'state',
							'country', 'postal_code', 'customer_type', 'credit_limit',
							'payment_terms', 'notes', 'is_active', 'updated_at'
						]
					);
				} catch (QueryException $e) {
					Log::error('Failed to process customer chunk: ' . $e->getMessage());
				}
			});

			$this->command->info("We have found " . Customer::query()->count() . " / " . $this->list->count() . " customers in database");
			$this->list = Customer::query()->pluck('id');
		}
	}