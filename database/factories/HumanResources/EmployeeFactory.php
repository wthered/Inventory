<?php

	namespace Database\Factories\HumanResources;

	use App\Models\HumanResources\Employee;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<Employee>
	 */
	class EmployeeFactory extends Factory {
		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			$hired_at = fake()->dateTimeBetween('-5 years', '-2 days');

			return [
				'employee_code'    => 'EMP-'.fake()->unique()->numberBetween(1000, 9999),
				'hire_date'        => $hired_at->format('Y-m-d'),
				'termination_date' => fake()->boolean(20)
					? fake()->dateTimeBetween($hired_at, 'yesterday')->format('Y-m-d')
					: null,
			];
		}

		/**
		 * Αυτόματη δημιουργία των 1-to-1 Details μόλις φτιαχτεί ο Employee!
		 */
		public function configure(): static {
			return $this->afterCreating(function (Employee $employee) {
				$employee->detail()->create([
					'afm'                     => fake()->numerify('#########'),
					'social_security'         => fake()->numerify('###########'),
					'id_card_number'          => strtoupper(fake()->lexify('??')).fake()->numerify('######'),
					'birth_date'              => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
					'address'                 => fake()->streetAddress(),
					'city'                    => fake()->city(),
					'postal_code'             => fake()->postcode(),
					'iban'                    => fake()->iban('GR'),
					'emergency_contact_name'  => fake()->name(),
					'emergency_contact_phone' => fake()->phoneNumber(),
				]);
			});
		}
	}
