<?php

	namespace Database\Seeders;

	use App\Models\City;
	use App\Models\Country;
	use Illuminate\Database\Seeder;

	class CitySeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$countries = Country::query()->whereIn('code', ['GR', 'DE', 'US', 'CY', 'FR', 'UK'])->pluck('id', 'code');

			if ($countries->isEmpty()) {
				return;
			}

			// Specific high-priority city lists
			$specificCities = [
				'GR' => [
					['name' => 'Athens', 'state' => 'Attica', 'postal_code' => '104 31'],
					['name' => 'Thessaloniki', 'state' => 'Central Macedonia', 'postal_code' => '546 21'],
					['name' => 'Patras', 'state' => 'Western Greece', 'postal_code' => '262 21'],
					['name' => 'Heraklion', 'state' => 'Crete', 'postal_code' => '712 01'],
					['name' => 'Larissa', 'state' => 'Thessaly', 'postal_code' => '412 21'],
					['name' => 'Volos', 'state' => 'Thessaly', 'postal_code' => '382 21'],
					['name' => 'Ioannina', 'state' => 'Epirus', 'postal_code' => '452 21'],
					['name' => 'Trikala', 'state' => 'Thessaly', 'postal_code' => '421 00'],
					['name' => 'Chalcis', 'state' => 'Central Greece', 'postal_code' => '341 00'],
					['name' => 'Serres', 'state' => 'Central Macedonia', 'postal_code' => '621 10'],
					['name' => 'Alexandroupoli', 'state' => 'Eastern Macedonia and Thrace', 'postal_code' => '681 00'],
					['name' => 'Xanthi', 'state' => 'Eastern Macedonia and Thrace', 'postal_code' => '671 00'],
					['name' => 'Kalamata', 'state' => 'Peloponnese', 'postal_code' => '241 00'],
					['name' => 'Kavala', 'state' => 'Eastern Macedonia and Thrace', 'postal_code' => '653 02'],
					['name' => 'Chania', 'state' => 'Crete', 'postal_code' => '731 31'],
					['name' => 'Lamia', 'state' => 'Central Greece', 'postal_code' => '351 00'],
					['name' => 'Komotini', 'state' => 'Eastern Macedonia and Thrace', 'postal_code' => '691 00'],
					['name' => 'Rhodes', 'state' => 'South Aegean', 'postal_code' => '851 00'],
					['name' => 'Agrinio', 'state' => 'Western Greece', 'postal_code' => '301 00'],
					['name' => 'Katerini', 'state' => 'Central Macedonia', 'postal_code' => '601 00'],
					['name' => 'Corfu', 'state' => 'Ionian Islands', 'postal_code' => '491 00'],
					['name' => 'Tripoli', 'state' => 'Peloponnese', 'postal_code' => '221 00'],
					['name' => 'Rethymno', 'state' => 'Crete', 'postal_code' => '741 00'],
					['name' => 'Mytilene', 'state' => 'North Aegean', 'postal_code' => '811 00'],
				],
				// Germany (DE)
				'DE' => [
					['name' => 'Berlin', 'state' => 'Berlin', 'postal_code' => '10115'],
					['name' => 'Hamburg', 'state' => 'Hamburg', 'postal_code' => '20095'],
					['name' => 'Munich', 'state' => 'Bavaria', 'postal_code' => '80331'],
					['name' => 'Cologne', 'state' => 'North Rhine-Westphalia', 'postal_code' => '50667'],
					['name' => 'Frankfurt', 'state' => 'Hesse', 'postal_code' => '60311'],
					['name' => 'Stuttgart', 'state' => 'Baden-Württemberg', 'postal_code' => '70173'],
					['name' => 'Düsseldorf', 'state' => 'North Rhine-Westphalia', 'postal_code' => '40213'],
					['name' => 'Leipzig', 'state' => 'Saxony', 'postal_code' => '04109'],
					['name' => 'Dortmund', 'state' => 'North Rhine-Westphalia', 'postal_code' => '44135'],
					['name' => 'Essen', 'state' => 'North Rhine-Westphalia', 'postal_code' => '45127'],
					['name' => 'Bremen', 'state' => 'Bremen', 'postal_code' => '28195'],
					['name' => 'Dresden', 'state' => 'Saxony', 'postal_code' => '01067'],
					['name' => 'Hanover', 'state' => 'Lower Saxony', 'postal_code' => '30159'],
					['name' => 'Nuremberg', 'state' => 'Bavaria', 'postal_code' => '90402'],
					['name' => 'Duisburg', 'state' => 'North Rhine-Westphalia', 'postal_code' => '47051'],
				],

				// France (FR)
				'FR' => [
					['name' => 'Paris', 'state' => 'Île-de-France', 'postal_code' => '75001'],
					['name' => 'Marseille', 'state' => 'Provence-Alpes-Côte d\'Azur', 'postal_code' => '13001'],
					['name' => 'Lyon', 'state' => 'Auvergne-Rhône-Alpes', 'postal_code' => '69001'],
					['name' => 'Toulouse', 'state' => 'Occitanie', 'postal_code' => '31000'],
					['name' => 'Nice', 'state' => 'Provence-Alpes-Côte d\'Azur', 'postal_code' => '06000'],
					['name' => 'Nantes', 'state' => 'Pays de la Loire', 'postal_code' => '44000'],
					['name' => 'Montpellier', 'state' => 'Occitanie', 'postal_code' => '34000'],
					['name' => 'Strasbourg', 'state' => 'Grand Est', 'postal_code' => '67000'],
					['name' => 'Bordeaux', 'state' => 'Nouvelle-Aquitaine', 'postal_code' => '33000'],
					['name' => 'Lille', 'state' => 'Hauts-de-France', 'postal_code' => '59000'],
					['name' => 'Rennes', 'state' => 'Brittany', 'postal_code' => '35000'],
					['name' => 'Reims', 'state' => 'Grand Est', 'postal_code' => '51100'],
					['name' => 'Toulon', 'state' => 'Provence-Alpes-Côte d\'Azur', 'postal_code' => '83000'],
				],

				// United Kingdom (GB)
				'GB' => [
					['name' => 'London', 'state' => 'England', 'postal_code' => 'EC1A 1BB'],
					['name' => 'Birmingham', 'state' => 'England', 'postal_code' => 'B1 1AA'],
					['name' => 'Glasgow', 'state' => 'Scotland', 'postal_code' => 'G1 1QX'],
					['name' => 'Manchester', 'state' => 'England', 'postal_code' => 'M1 1AD'],
					['name' => 'Liverpool', 'state' => 'England', 'postal_code' => 'L1 8JQ'],
					['name' => 'Edinburgh', 'state' => 'Scotland', 'postal_code' => 'EH1 1YZ'],
					['name' => 'Bristol', 'state' => 'England', 'postal_code' => 'BS1 2AG'],
					['name' => 'Leeds', 'state' => 'England', 'postal_code' => 'LS1 1UR'],
					['name' => 'Sheffield', 'state' => 'England', 'postal_code' => 'S1 2JA'],
					['name' => 'Belfast', 'state' => 'Northern Ireland', 'postal_code' => 'BT1 5GS'],
					['name' => 'Cardiff', 'state' => 'Wales', 'postal_code' => 'CF10 1DD'],
					['name' => 'Newcastle upon Tyne', 'state' => 'England', 'postal_code' => 'NE1 1AD'],
					['name' => 'Nottingham', 'state' => 'England', 'postal_code' => 'NG1 1A1'],
				],
				'US' => [
					['name' => 'New York', 'state' => 'New York', 'postal_code' => '10001'],
					['name' => 'Los Angeles', 'state' => 'California', 'postal_code' => '90001'],
					['name' => 'Chicago', 'state' => 'Illinois', 'postal_code' => '60601'],
					['name' => 'Houston', 'state' => 'Texas', 'postal_code' => '77001'],
					['name' => 'Phoenix', 'state' => 'Arizona', 'postal_code' => '85001'],
					['name' => 'Philadelphia', 'state' => 'Pennsylvania', 'postal_code' => '19102'],
					['name' => 'San Antonio', 'state' => 'Texas', 'postal_code' => '78201'],
					['name' => 'San Diego', 'state' => 'California', 'postal_code' => '92101'],
					['name' => 'Dallas', 'state' => 'Texas', 'postal_code' => '75201'],
					['name' => 'San Jose', 'state' => 'California', 'postal_code' => '95101'],
					['name' => 'Austin', 'state' => 'Texas', 'postal_code' => '78701'],
					['name' => 'Jacksonville', 'state' => 'Florida', 'postal_code' => '32202'],
					['name' => 'San Francisco', 'state' => 'California', 'postal_code' => '94102'],
					['name' => 'Columbus', 'state' => 'Ohio', 'postal_code' => '43215'],
					['name' => 'Indianapolis', 'state' => 'Indiana', 'postal_code' => '46204'],
					['name' => 'Seattle', 'state' => 'Washington', 'postal_code' => '98101'],
					['name' => 'Denver', 'state' => 'Colorado', 'postal_code' => '80202'],
					['name' => 'Washington', 'state' => 'District of Columbia', 'postal_code' => '20001'],
					['name' => 'Boston', 'state' => 'Massachusetts', 'postal_code' => '02108'],
					['name' => 'Miami', 'state' => 'Florida', 'postal_code' => '33101'],
					['name' => 'Atlanta', 'state' => 'Georgia', 'postal_code' => '30303'],
					['name' => 'Las Vegas', 'state' => 'Nevada', 'postal_code' => '89101'],
				],
				'CY' => [
					['name' => 'Nicosia', 'state' => 'Nicosia', 'postal_code' => '1010'],
					['name' => 'Limassol', 'state' => 'Limassol', 'postal_code' => '3010'],
				],
			];

			foreach ($countries as $code => $countryId) {
//				dd($specificCities[$code]);
				if (isset($specificCities[$code])) {
					foreach ($specificCities[$code] as $city) {
						City::query()->updateOrCreate(
							[
								'country_id' => $countryId,
								'name'       => $city['name'],
							],
							[
								'state'       => $city['state'],
								'postal_code' => $city['postal_code'],
								'is_active'   => true,
							]
						);
					}
				} else {
					// Generate 2 to 4 factory cities for any country not in the specific list
					City::factory(rand(2, 4))->create([
						'country_id' => $countryId,
					]);
				}
			}
		}
	}