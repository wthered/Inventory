<?php

	namespace Database\Factories;

	use App\Models\Supplier;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Str;

	/**
	 * @extends Factory<Supplier>
	 */
	class SupplierFactory extends Factory {
		protected $model = Supplier::class;

		/**
		 * Define the model's default state.
		 */
		public function definition(): array {
			$this_time = now();
			return [
				'code'           => 'SUPP-' . Str::upper(fake()->unique()->lexify('?????')) . mt_rand(100, 999),
				'name'           => fake()->company(),
				'company_name'   => fake()->company() . ' LTD',
				'email'          => fake()->unique()->companyEmail(),
				'phone'          => fake()->phoneNumber(),
				'website'        => fake()->url(),
				'tax_number'     => fake()->unique()->regexify('[0-9]{9}'),
				'address'        => fake()->streetAddress(),
				'city'           => fake()->city(),
				'state'          => fake()->state(),
				'country'        => fake()->country(),
				'postal_code'    => fake()->postcode(),
				'contact_person' => fake()->name(),
				'contact_phone'  => fake()->phoneNumber(),
				'credit_limit'   => fake()->randomFloat(2, 0, 100000),
				'payment_terms'  => fake()->randomElement([
					'cash',
					'credit_7',
					'credit_15',
					'credit_30',
					'credit_60',
					'credit_90'
				]),
				'notes'          => fake()->optional(0.33)->paragraph(),
				'is_active'      => fake()->boolean(0.75),
				'created_at'     => $this_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
				'updated_at'     => $this_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
			];
		}
	}