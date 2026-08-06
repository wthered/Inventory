<?php

	namespace Database\Seeders;

	use App\Models\Country;
	use Illuminate\Database\Seeder;
	use Illuminate\Http\Client\ConnectionException;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Str;

	class CountrySeeder extends Seeder {
		/**
		 * Run the database seeds across 8 paginated steps.
		 *
		 * @throws ConnectionException
		 */
		public function run(): void {
			$limit = 25;
			$totalSteps = 11;

			for ($step = 0; $step < $totalSteps; $step++) {
				$offset = $step * $limit;

				$this->command->info("Fetching batch ".($step + 1)."/".$totalSteps." (offset=".$offset.")...");

				$response = Http::timeout(15)
				                ->retry(3, 2000)
				                ->withToken('rc_live_8e59ad65846046359a2af02eac868d67')
				                ->get('https://api.restcountries.com/countries/v5', [
					                'limit'  => $limit,
					                'offset' => $offset,
					                'pretty' => 1,
				                ]);

				if ($response->failed()) {
					$this->command->error("Failed to fetch countries at offset ".$offset);
					continue;
				}

				$data = Collection::make($response->json())->get('data');
				$countries = $data['objects'] ?? [];

				if (empty($countries)) {
					$this->command->warn("No items returned at offset {$offset}. Ending pagination.");
					break;
				}

				foreach ($countries as $country) {
					$code = $country['codes']['alpha_2'] ?? null;

					// Skip entries without standard 2-letter codes
					if (empty($code)) {
						continue;
					}

					// Extract calling code safely
					$callingCode = $country['calling_codes'][0] ?? null;
					$phoneCode = $callingCode ? '+'.ltrim($callingCode, '+') : null;

					Country::query()->updateOrCreate(
						['code' => Str::upper($code)],
						[
							'name'       => $country['names']['common'] ?? $code,
							'code_alpha' => $country['codes']['alpha_3'] ?? null,
							'phone_code' => $phoneCode,
							'is_active'  => false,
						]
					);
				}
			}

			// 1. Reset all countries to inactive
			Country::query()->update(['is_active' => false]);

			// 2. Activate only the target 6 countries (using GB instead of UK)
			Country::query()->withoutGlobalScopes()
			       ->whereIn('code', ['GR', 'FR', 'DE', 'US', 'CY', 'GB'])
			       ->update(['is_active' => true]);

			$this->command->info('Countries seeded successfully!');
		}
	}
