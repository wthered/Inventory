<?php

	namespace Database\Factories\HumanResources;

	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\EmployeeDetail;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<EmployeeDetail>
	 */
	class EmployeeDetailFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			return [
				'employee_id'             => Employee::factory(),
				'afm'                     => fake()->numerify('#########'),
				'social_security'         => fake()->numerify('###########'),
				'id_card_number'          => strtoupper(fake()->bothify('??######')),
				'birth_date'              => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
				'address'                 => fake()->streetAddress(),
				'city'                    => fake()->city(),
				'postal_code'             => fake()->postcode(),
				'iban'                    => fake()->iban('GR'),
				'emergency_contact_name'  => fake()->name(),
				'emergency_contact_phone' => fake()->phoneNumber(),
			];
		}
	}