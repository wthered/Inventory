<?php

	namespace Database\Factories;

	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Carbon;

	class WarehouseLocationFactory extends Factory {
		protected $model = WarehouseLocation::class;

		public function definition(): array {
			// Generic fallback values (κυρίως για Unit Tests)
			return [
				'warehouse_id'     => Warehouse::factory(),
				'code'             => 'LOC-'.$this->faker->unique()->bothify('??-##'),
				'name'             => 'Generic Slot '.$this->faker->word(),
				'zone'             => 1,
				'aisle'            => 1,
				'rack'             => 1,
				'shelf'            => 1,
				'bin'              => 1,
				'capacity'         => 100.00,
				'current_capacity' => 0.00,
				'description'      => $this->faker->sentence(),
				'is_active'        => true,
				'created_at'       => Carbon::now(),
				'updated_at'       => Carbon::now(),
			];
		}

		/**
		 * Custom State για τη δημιουργία δομημένου Grid Lore αποθήκης
		 */
		public function generateGrid(Warehouse $warehouse): self {
			return $this->state(function (array $attributes) use ($warehouse) {
				return []; // Επιστρέφουμε άδειο, γιατί θα κάνουμε override στο sequence ή bulk
			})->afterMaking(function (WarehouseLocation $location) {
				// Δε χρειαζόμαστε ενέργειες post-make
				print("End of creation Location ".$location->id."\n");
			});
		}

		/**
		 * Helper μέθοδος που παράγει το array για bulk insert χρησιμοποιώντας το factory definition
		 */
		public function makeGridData(Warehouse $warehouse): array {
			$locations = [];
			$now = Carbon::now(config('app.timezone'))->subYears(mt_rand(0, 45))->subMonths(mt_rand(0, 11))->subDays(mt_rand(0, 30));

			$capacity = $warehouse->zones * $warehouse->aisles * $warehouse->racks * $warehouse->shelves * $warehouse->bins;

			for ($z = 1; $z <= $warehouse->zones; $z++) {
				for ($a = 1; $a <= $warehouse->aisles; $a++) {
					for ($r = 1; $r <= $warehouse->racks; $r++) {
						for ($s = 1; $s <= $warehouse->shelves; $s++) {
							for ($b = 1; $b <= $warehouse->bins; $b++) {

								// Fancy Enterprise name με μηδενικά (π.χ. 01-02-01)
								$gridName = sprintf("%02d-%02d-%02d-%02d-%02d", $z, $a, $r, $s, $b);
								$locationCode = sprintf("%s-Z%d-A%d-R%d-S%d-B%d", $warehouse->code, $z, $a, $r, $s, $b);

								$locations[] = [
									'warehouse_id'     => $warehouse->id,
									'code'             => $locationCode,
									'name'             => $warehouse->name." • ".$gridName,
									'zone'             => $z,
									'aisle'            => $a,
									'rack'             => $r,
									'shelf'            => $s,
									'bin'              => $b,
									'capacity'         => $capacity,
									'current_capacity' => mt_rand(0, $capacity),
									'description'      => "Storage unit slot located in Zone ".$z.", Aisle ".$a,
									'is_active'        => fake()->boolean(),
									'created_at'       => $now->subHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
									'updated_at'       => $now->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
								];
							}
						}
					}
				}
			}

			return $locations;
		}
	}