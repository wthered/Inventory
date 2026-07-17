<?php

	namespace Database\Seeders;

	use App\Models\Inventories\InventoryTransaction;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;

	class ParentSeeder extends Seeder {

		/**
		 * @var int The size of the batch to insert at one time.
		 */
		protected const BATCH_SIZE = 512;
		protected Collection $list;
		protected Carbon     $startDate;
		protected int        $requests;

		public function __construct() {
			$this->list      = Collection::empty();
			$this->startDate = Carbon::now(config('app.timezone'))
				->subYears(mt_rand(Carbon::today()->yearOfCentury(), Carbon::today()->yearOfCentury() + 20))
				->subMonths(mt_rand(0, Carbon::today()->month))
				->subDays(mt_rand(0, Carbon::today()->day));
			$this->requests  = 0;
		}

		public function flushList(): void {
			if ($this->list->isEmpty()) {
				return;
			}

			$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
				InventoryTransaction::query()->insert($chunk->toArray());
			});

			$this->list = Collection::empty();
		}

		protected function generateStates(): Collection {
			return Collection::make([
				[
					'name' => 'Alabama',
					'code' => 'AL',
					'zip_pattern' => '35###'
				],
				[
					'name' => 'Alaska',
					'code' => 'AK',
					'zip_pattern' => '99###'
				],
				[
					'name' => 'Arizona',
					'code' => 'AZ',
					'zip_pattern' => ['85###', '86###']
				],
				[
					'name' => 'Arkansas',
					'code' => 'AR',
					'zip_pattern' => ['71###', '72###']
				],
				[
					'name' => 'California',
					'code' => 'CA',
					'zip_pattern' => ['90###', '91###', '92###', '93###', '94###', '95###', '96###']
				],
				[
					'name' => 'Colorado',
					'code' => 'CO',
					'zip_pattern' => ['80###', '81###']
				],
				[
					'name' => 'Connecticut',
					'code' => 'CT',
					'zip_pattern' => '06###'
				],
				[
					'name' => 'Delaware',
					'code' => 'DE',
					'zip_pattern' => '19###'
				],
				[
					'name' => 'Florida',
					'code' => 'FL',
					'zip_pattern' => ['32###', '33###', '34###']
				],
				[
					'name' => 'Georgia',
					'code' => 'GA',
					'zip_pattern' => ['30###', '31###']
				],
				[
					'name' => 'Hawaii',
					'code' => 'HI',
					'zip_pattern' => '96###'
				],
				[
					'name' => 'Idaho',
					'code' => 'ID',
					'zip_pattern' => '83###'
				],
				[
					'name' => 'Illinois',
					'code' => 'IL',
					'zip_pattern' => ['60###', '61###', '62###']
				],
				[
					'name' => 'Indiana',
					'code' => 'IN',
					'zip_pattern' => ['46###', '47###']
				],
				[
					'name' => 'Iowa',
					'code' => 'IA',
					'zip_pattern' => ['50###', '51###', '52###']
				],
				[
					'name' => 'Kansas',
					'code' => 'KS',
					'zip_pattern' => ['66###', '67###']
				],
				[
					'name' => 'Kentucky',
					'code' => 'KY',
					'zip_pattern' => ['40###', '41###', '42###']
				],
				[
					'name' => 'Louisiana',
					'code' => 'LA',
					'zip_pattern' => ['70###', '71###']
				],
				[
					'name' => 'Maine',
					'code' => 'ME',
					'zip_pattern' => ['03###', '04###']
				],
				[
					'name' => 'Maryland',
					'code' => 'MD',
					'zip_pattern' => ['20###', '21###']
				],
				[
					'name' => 'Massachusetts',
					'code' => 'MA',
					'zip_pattern' => ['01###', '02###', '05###']
				],
				[
					'name' => 'Michigan',
					'code' => 'MI',
					'zip_pattern' => ['48###', '49###']
				],
				[
					'name' => 'Minnesota',
					'code' => 'MN',
					'zip_pattern' => ['55###', '56###']
				],
				[
					'name' => 'Mississippi',
					'code' => 'MS',
					'zip_pattern' => ['38###', '39###']
				],
				[
					'name' => 'Missouri',
					'code' => 'MO',
					'zip_pattern' => ['63###', '64###', '65###']
				],
				[
					'name' => 'Montana',
					'code' => 'MT',
					'zip_pattern' => '59###'
				],
				[
					'name' => 'Nebraska',
					'code' => 'NE',
					'zip_pattern' => '68###'
				],
				[
					'name' => 'Nevada',
					'code' => 'NV',
					'zip_pattern' => ['88###', '89###']
				],
				[
					'name' => 'New Hampshire',
					'code' => 'NH',
					'zip_pattern' => '03###'
				],
				[
					'name' => 'New Jersey',
					'code' => 'NJ',
					'zip_pattern' => ['07###', '08###']
				],
				[
					'name' => 'New Mexico',
					'code' => 'NM',
					'zip_pattern' => ['87###', '88###']
				],
				[
					'name' => 'New York',
					'code' => 'NY',
					'zip_pattern' => ['10###', '11###', '12###', '13###', '14###']
				],
				[
					'name' => 'North Carolina',
					'code' => 'NC',
					'zip_pattern' => ['27###', '28###']
				],
				[
					'name' => 'North Dakota',
					'code' => 'ND',
					'zip_pattern' => '58###'
				],
				[
					'name' => 'Ohio',
					'code' => 'OH',
					'zip_pattern' => ['43###', '44###', '45###']
				],
				[
					'name' => 'Oklahoma',
					'code' => 'OK',
					'zip_pattern' => ['73###', '74###']
				],
				[
					'name' => 'Oregon',
					'code' => 'OR',
					'zip_pattern' => '97###'
				],
				[
					'name' => 'Pennsylvania',
					'code' => 'PA',
					'zip_pattern' => ['15###', '16###', '17###', '18###', '19###']
				],
				[
					'name' => 'Rhode Island',
					'code' => 'RI',
					'zip_pattern' => ['02###', '06###', '09###']
				],
				[
					'name' => 'South Carolina',
					'code' => 'SC',
					'zip_pattern' => '29###'
				],
				[
					'name' => 'South Dakota',
					'code' => 'SD',
					'zip_pattern' => '57###'
				],
				[
					'name' => 'Tennessee',
					'code' => 'TN',
					'zip_pattern' => ['37###', '38###']
				],
				[
					'name' => 'Texas',
					'code' => 'TX',
					'zip_pattern' => ['75###', '76###', '77###', '78###', '79###', '88###', '89###']
				],
				[
					'name' => 'Utah',
					'code' => 'UT',
					'zip_pattern' => '84###'
				],
				[
					'name' => 'Vermont',
					'code' => 'VT',
					'zip_pattern' => '05###'
				],
				[
					'name' => 'Virginia',
					'code' => 'VA',
					'zip_pattern' => ['22###', '23###', '24###']
				],
				[
					'name' => 'Washington',
					'code' => 'WA',
					'zip_pattern' => ['98###', '99###']
				],
				[
					'name' => 'West Virginia',
					'code' => 'WV',
					'zip_pattern' => ['24###', '25###', '26###']
				],
				[
					'name' => 'Wisconsin',
					'code' => 'WI',
					'zip_pattern' => ['53###', '54###']
				],
				[
					'name' => 'Wyoming',
					'code' => 'WY',
					'zip_pattern' => '82###'
				],
				[
					'name' => 'District of Columbia',
					'code' => 'DC',
					'zip_pattern' => '20###'
				],
			])->shuffle();
		}
	}
