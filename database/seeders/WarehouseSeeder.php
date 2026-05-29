<?php

	namespace Database\Seeders;

	use App\Enums\WarehouseType;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Str;

	class WarehouseSeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 *
		 * @throws ConnectionException
		 */
		public function run(): void {
			$response = Http::withHeaders([
				'X-API-Key' => config('services.mockaroo.token'),
			])
				->get('https://my.api.mockaroo.com/product_warehouses.json');

			$this->requests++;

			$users = User::query()
				->pluck('id')
				->sort()
				->unique()
				->values();

			$names = Collection::make([
				[
					'name'        => 'Hobbiton',
					'description' => 'The peaceful village in the Shire where Bilbo and Frodo Baggins lived',
				],
				[
					'name'        => 'Bag End',
					'description' => 'The famous underground home of Bilbo and later Frodo Baggins in Hobbiton',
				],
				[
					'name'        => 'Michel Delving',
					'description' => 'The chief town and administrative center of the Shire',
				],
				[
					'name'        => 'Bree',
					'description' => 'The main settlement at the crossroads in Bree-land, home to both Men and Hobbits (The Prancing Pony inn is located here)',
				],
				[
					'name'        => 'Rivendell',
					'description' => 'The hidden Elven valley refuge of Lord Elrond (also called Imladris)',
				],
				[
					'name'        => 'Minas Tirith',
					'description' => 'The White City and capital of Gondor, built on seven levels against the mountain',
				],
				[
					'name'        => 'Edoras',
					'description' => 'The golden hall and capital city of Rohan, seat of King Théoden',
				],
				[
					'name'        => 'Isengard',
					'description' => 'Saruman\'s fortress, centered around the tall tower of Orthanc',
				],
				[
					'name'        => 'Helm\'s Deep',
					'description' => 'The ancient fortress and valley refuge of Rohan, site of a major battle',
				],
				[
					'name'        => 'Osgiliath',
					'description' => 'The ruined former capital of Gondor, straddling the River Anduin',
				],
				[
					'name'        => 'Minas Morgul',
					'description' => 'The cursed city (formerly Minas Ithil), now the stronghold of the Nazgûl',
				],
				[
					'name'        => 'Dale',
					'description' => 'The prosperous human city rebuilt near the Lonely Mountain after Smaug',
				],
				[
					'name'        => 'Esgaroth',
					'description' => 'Also called Lake-town, the wooden town built on the Long Lake',
				],
				[
					'name'        => 'Erebor',
					'description' => 'The Lonely Mountain, the great Dwarven kingdom and city under the mountain',
				],
				[
					'name'        => 'The Grey Havens',
					'description' => 'Mithlond — the ancient Elven harbor city from which ships depart to the Undying Lands',
				],
				[
					'name'        => 'Fornost',
					'description' => 'Fornost Erain — the ruined northern capital of the lost kingdom of Arnor',
				],
				[
					'name'        => 'Dol Amroth',
					'description' => 'The beautiful coastal city and principality in southern Gondor',
				],
				[
					'name'        => 'Tuckborough',
					'description' => 'A large village in the Tookland region of the Shire, home of the Took family',
				],
				[
					'name'        => 'Bywater',
					'description' => 'A small village in the Shire, site of the Battle of Bywater',
				],
			]);

			$this->list = Collection::empty();
			if ($response->successful()) {
				$availableNames = $names->shuffle();
				$this->list     = Collection::make($response->json())
					->map(function ($warehouse) use ($users, &$availableNames, &$names) {
						if ($availableNames->isEmpty()) {
							// Αν τελειώσουν τα ονόματα, ξαναγέμισε
							$availableNames = $names->shuffle();
						}

						$name                     = $availableNames->pop();
						$warehouse['name']        = $name['name'];
						$warehouse['description'] = $name['description'];
						$warehouse['manager_id']  = $users->random();
						$warehouse['created_at']  = Carbon::now(config('app.timezone'))
							->subHours(mt_rand(0, 23))
							->subMinutes(mt_rand(0, 59))
							->subSeconds(mt_rand(0, 59))
							->toDateTimeString();
						$warehouse['updated_at']  = Carbon::now(config('app.timezone'))
							->addHours(mt_rand(0, 23))
							->addMinutes(mt_rand(0, 59))
							->addSeconds(mt_rand(0, 59))
							->toDateTimeString();
						return $warehouse;
					});
			} else {
				$this->command->error('❌ Failed to fetch warehouses from external API after ' . $this->requests . ' requests...');
				$stateNames = $this->generateStates();
				$state      = $stateNames->random();

				// Λήψη του pattern (μπορεί να είναι string ή array)
				$pattern = $state['zip_pattern'];

				// Αν το pattern είναι array, επέλεξε ένα τυχαίο prefix
				if (is_array($pattern)) {
					$pattern = $pattern[array_rand($pattern)];
				}

				$names->shuffle()->each(function ($warehouse) use (&$stateNames, &$users, $pattern) {

					$locationState = $stateNames->random();

					$capacity = fake()->randomFloat(2, 32, 1024);
					$this->list->push([
						'code'             => Str::uuid7()->toString(),
						'name'             => $warehouse['name'],
						'type'             => fake()->randomElement(WarehouseType::cases())->value,
						'description'      => $warehouse['description'],
						'address'          => fake()->address(),
						'city'             => fake()->city(),
						'state'            => $locationState['name'],
						'country'          => fake()->country(),
						'postal_code'      => fake()->numerify($pattern),
						'phone'            => fake()->optional()->regexify('/^69[237]\d{7}$/'),
						'email'            => fake()->email(),
						'manager_id'       => $users->random(),
						'zones'            => mt_rand(1, 8),
						'aisles'           => mt_rand(1, 16),
						'racks'            => mt_rand(1, 8),
						'shelves'          => mt_rand(1, 8),
						'bins'             => mt_rand(1, 16),
						'capacity'         => $capacity,
						'current_capacity' => $capacity * fake()->randomFloat(2, 0, 1),
						'is_primary'       => fake()->boolean(),
						'created_at'       => Carbon::now(config('app.timezone'))->subHours(mt_rand(1, 23))->subMinutes(mt_rand(1, 59))->subSeconds(mt_rand(1, 59))->toDateTimeString(),
						'updated_at'       => Carbon::now(config('app.timezone'))->addHours(mt_rand(1, 23))->addMinutes(mt_rand(1, 59))->addSeconds(mt_rand(1, 59))->toDateTimeString(),
					]);
				});
			}
			Warehouse::query()->insert($this->list->shuffle()->toArray());
			$this->command->info($this->list->count() . ' warehouses seeded successfully using ' . $this->requests . ' requests...');
		}
	}
