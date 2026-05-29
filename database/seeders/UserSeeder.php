<?php

	namespace Database\Seeders;

	use App\Models\User;
	use Carbon\Carbon;
	use GuzzleHttp\Exception\RequestException;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Str;
	use Spatie\Permission\Models\Role;

	class UserSeeder extends ParentSeeder {

		private int $rounds = 2;

		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$user_data = $this->factory();
			if ($user_data->isEmpty()) {
				$this->command->error("No users to seed. Skipping...");
				return;
			}

			$many_users = $user_data->count();
			$adminIndex = rand(0, $user_data->count() - 1);
			$roles      = Role::all();

			$user_data->each(function ($user, $index) use ($many_users, $adminIndex, $roles, $user_data) {
				// 1. Fixed Date Logic: Use ->copy() to prevent the base date from drifting every loop
				$createdAt = Carbon::now(config('app.timezone'))->subDays(rand(1, Carbon::today()->daysInMonth))->subHours(rand(1, 59))->subMinutes(rand(1, 59));

				// 2. Verified At logic
				$verifiedAt = isset($user['verified_at']) ? Carbon::createFromTimestamp($user['verified_at']) : $createdAt->copy()->addHours(mt_rand(1, 23));

				// 3. Create the User
				$that_user = User::create([
					'name'              => $user['username'],
					'email'             => $user['email'],
					'email_verified_at' => $verifiedAt->isValid() && fake()->boolean() ? $verifiedAt->timezone(config('app.timezone'))->toDateTimeString() : null,
					'password'          => $user['password'],
					'remember_token'    => $user['remember'],
					'created_at'        => $createdAt->toDateTimeString(),
					'updated_at'        => $createdAt->addDays(mt_rand(1, 8))->toDateTimeString(),
				]);

				// 4. Role Assignment Logic
				// Make the first user an Admin, otherwise pick a random role
				if ($index === $adminIndex) {
					$that_user->assignRole('admin');
				} else {
					$that_user->assignRole($roles->random()->name);
				}

				// 5. Create Account Details
				$last_seen = Carbon::createFromTimestamp($user['last_seen_at'])->timezone(config('app.timezone'));

				$that_user->account()->create([
					'first_name'    => $user['forename'],
					'last_name'     => $user['lastname'],
					'phone'         => fake()->optional(0.7)->regexify('69[237]\d{7}'),
					'avatar'        => "https://robohash.org/" . $user['token'] . ".png?size=64x64&set=set" . mt_rand(1, 5),
					'is_active'     => fake()->boolean(90),
					'last_login_at' => $last_seen->isValid() ? $last_seen->toDateTimeString() : Carbon::now()->toDateTimeString(),
				]);

				// 6. Clean Progress Output
				$percent = number_format(100 * ($index + 1) / $many_users, 3);
				print("[" . $percent . "% done] Created User ID: " . $that_user->id . " (" . $user['username'] . ")........\r");
			});

			print("\nSeeding completed successfully.\n");
			$users = User::query()->get();

			$brother = $users->random();
			$brother->account()->delete();
			$birthday = Carbon::create(1987, 8, 28, mt_rand(0, 23), mt_rand(0, 59), mt_rand(0, 59), config('app.timezone', 'Europe/Athens'));

			// 1. Πρώτα ενημερώνουμε τα στοιχεία του User (για να πάρει το 'terry87')
			$brother->update([
				'name'              => 'terry87',
				'email'             => 'lefteris@pliassas.gr',
				'email_verified_at' => Carbon::createFromTimestamp(mt_rand($birthday->unix(), time()))->timezone(config('app.timezone'))->toDateTimeString(),
				'password'          => Hash::make('lefteris1987'),
				'remember_token'    => fake()->boolean() ? Str::random(32) : null,
				'updated_at'        => Carbon::now()->subMonths(mt_rand(0, Carbon::today()->month))->subDays(mt_rand(0, Carbon::today()->day))->toDateTimeString(),
			]);

			// 2. Τώρα δημιουργούμε/ενημερώνουμε το account.
			$brother->account()->create([
				'username'      => $brother->name,
				'first_name'    => 'Λευτέρης',
				'last_name'     => 'Πλιάσσας',
				'phone'         => fake()->optional(0.7)->regexify('69[237]\d{7}'),
				'avatar'        => 'https://robohash.org/' . Str::lower(Str::random(32)) . '.png?size=256x256&set=set' . fake()->randomElement([1, 2, 3, 4, 5]),
				'is_active'     => true,
				'last_login_at' => fake()->boolean() ? Carbon::yesterday()->setHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59))->toDateTimeString() : null,
				'created_at'    => $birthday->copy()->timezone(config('app.timezone')),
				'updated_at'    => now(),
			]);

			// Optional: Re-assign role if needed
			$brother->assignRole('admin');

			if ($users->count() > 0) {
				$myself = fake()->randomElement($users);
				$myself->account()->delete();

				$myself->update([
					'name'              => 'wthered',
					'email'             => 'wthered@gmail.com',
					'email_verified_at' => Carbon::createFromDate(2000 + mt_rand(0, 25), mt_rand(1, Carbon::today()->month), mt_rand(1, Carbon::today()->day))->timezone(config('app.timezone'))->toDateTimeString(),
					'password'          => Hash::make('!w727dt3d'),
					'remember_token'    => fake()->boolean() ? Str::random(32) : null,
					'updated_at'        => Carbon::now()->subMonths(mt_rand(1, Carbon::today()->month))->subDays(mt_rand(1, Carbon::today()->day))->timezone(config('app.timezone'))->toDateTimeString(),
				]);

				$myself->account()->create([
					'first_name' => 'William',
					'last_name'  => 'Wallace',
					'is_active'  => true,
					'updated_at' => Carbon::tomorrow()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59))->timezone(config('app.timezone'))->toDateTimeString(),
				]);
			} else {
				$myself = User::query()->create([
					'name'              => 'wthered',
					'email'             => 'wthered@gmail.com',
					'email_verified_at' => Carbon::createFromDate(2000 + mt_rand(0, 25), mt_rand(1, Carbon::today()->month), mt_rand(1, Carbon::today()->day))->timezone(config('app.timezone'))->toDateTimeString(),
					'password'          => Hash::make('!w727dt3d'),
					'remember_token'    => fake()->boolean() ? Str::random(32) : null,
					'created_at'        => Carbon::now()->subMonths(mt_rand(0, Carbon::today()->month))->subDays(mt_rand(0, Carbon::today()->day))->timezone(config('app.timezone'))->toDateTimeString(),
					'updated_at'        => Carbon::now()->addMonths(mt_rand(0, Carbon::today()->month))->addDays(mt_rand(0, Carbon::today()->day))->timezone(config('app.timezone'))->toDateTimeString(),
				]);

				$myself->account()->create([
					'first_name' => 'William',
					'last_name'  => 'Wallace',
					'phone'      => '6977729952',
					'avatar'     => 'https://image.tmdb.org/t/p/original/d4f4cQ9EiYuvNMjT1IB2h06KoRx.jpg',
					'is_active'  => true,
					'updated_at' => Carbon::tomorrow()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59))->timezone(config('app.timezone'))->toDateTimeString(),
				]);
			}

			$myself->assignRole('admin');
			$this->command->info(User::query()->count() . ' users created using ' . $this->requests . ' requests');
		}

		private function factory(): Collection {
			$users = Collection::empty();
			try {
				$password = Hash::make(Str::password(mt_rand(12, 16)));
				for ($round = 0; $round < $this->rounds; $round++) {
					$this->requests++;
					$response = Http::withHeaders([
						'X-API-Key' => config('services.mockaroo.token'),
					])->get('https://my.api.mockaroo.com/movielens_users.json');

					if ($response->successful()) {
						// Get response body as string
						$body = $response->getBody()->getContents();

						foreach (json_decode($body, true) as $user) {
							$this->list->push($user);
						}
						$this->command->info("[" . __CLASS__ . "::" . __LINE__ . "] Start seeding round #" . ($round + 1) . " of " . $this->rounds);
						$users = $this->list->shuffle();
					} else {
						$this->command->error('❌ Failed to fetch users from external API ');
						$birth = Carbon::createFromDate(1980, mt_rand(1, Carbon::today()->month), mt_rand(1, Carbon::today()->daysInMonth));
						for ($u = 0; $u < self::BATCH_SIZE / $this->rounds; $u++) {
							$nick     = fake()->userName() . Str::random(4);
							$name     = fake()->firstName();
							$last     = fake()->lastName();
							$birth    = $birth->addDays(mt_rand(1, Carbon::today()->dayOfMonth))->timezone(config('app.timezone'));
							$verified = fake()->boolean();
							$users->push([
								'username'          => $nick . '-' . $birth->format('Y'),
								'email'             => $nick . '@' . fake()->domainName(),
								'password'          => $password,
								'remember'          => $verified && fake()->boolean() ? Str::random(64) : null,
								'hash'              => Str::random(32),
								'salt'              => Str::random(32),
								'otp'               => Str::random(8),
								'email_verified_at' => $verified ? Carbon::createFromTimestamp(mt_rand($birth->unix(), Carbon::now()->unix()))->timezone(config('app.timezone'))->format('Y-m-d') : null,
								'forename'          => $name,
								'lastname'          => $last,
								'male'              => fake()->boolean(),
								'birth'             => Carbon::createFromTimestamp(fake()->numberBetween(0, time()))->format('Y-m-d'),
								'joined'            => fake()->numerify('##########'),
								'token'             => Str::random(32),
								'country'           => fake()->countryCode(),
								'last_seen_at'      => $birth->isValid() ? mt_rand($birth->unix(), Carbon::now()->unix()) : Carbon::now()->unix(),
								'avatar'            => "https://robohash.org/" . Str::lower(Str::random(32)) . ".jpg?size=128x128&set=set" . Collection::range(1, 5)->random(),
								'description_short' => fake()->realText(),
								'description_long'  => fake()->realText(512),
							]);
						}
						print("[Round " . ($round + 1) . " of " . $this->rounds . "] I have created " . $users->count() . " users" . PHP_EOL);
					}
				}
				return $users;
			} catch (RequestException $e) {
				// Handle errors (network issues, 4xx/5xx responses, etc.)
				if ($e->hasResponse()) {
					echo "HTTP Error: " . $e->getResponse()->getStatusCode() . "\n";
				} else {
					echo "Request Error: " . $e->getMessage() . "\n";
				}
			} catch (ConnectionException $e) {
				$this->command->error("Connection error: " . $e->getMessage());
			}
			return Collection::empty();
		}
	}
