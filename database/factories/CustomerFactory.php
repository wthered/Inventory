<?php

	namespace Database\Factories;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use App\Models\Customer;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Str;

	/**
	 * @extends Factory<Customer>
	 */
	/**
	 * @extends Factory<Customer>
	 */
	class CustomerFactory extends Factory {
		protected $model = Customer::class;

		/**
		 * Define the model's default state.
		 */
		public function definition(): array {
			return [
				'code'             => 'CUST-' . Str::upper(fake()->unique()->lexify('?????')) . mt_rand(100, 999),
				'name'             => fake()->name(),
				'email'            => fake()->unique()->safeEmail(),
				'phone'            => fake()->phoneNumber(),
				'company_name'     => fake()->optional(0.6)->company(),
				'tax_number'       => fake()->unique()->regexify('[0-9]{9}'),
				'billing_address'  => fake()->address(),
				'shipping_address' => fake()->address(),
				'city'             => fake()->city(),
				'state'            => fake()->state(),
				'country'          => fake()->country(),
				'postal_code'      => fake()->postcode(),
				'customer_type'    => fake()->randomElement(CustomerType::cases())->value, // Individual ή Company
				'credit_limit'     => fake()->randomFloat(2, 0, 50000),
				'payment_terms'    => fake()->randomElement(PaymentTerms::cases())->value, // cash, credit_30 κλπ
				'notes'            => fake()->optional(0.3)->sentence(),
				'is_active'        => true,
				'created_at'       => now(),
				'updated_at'       => now(),
			];
		}
	}