<?php

	namespace Database\Seeders\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use App\Models\User;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Arr;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	class PurchaseOrderHistorySeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {

			$totalHistoryCount = 0;
			$historyInserts    = [];
			$now               = Carbon::now();

			// 1. Load IDs for efficient random selection (avoids loading full Eloquent models)
			$purchaseOrderIds = PurchaseOrder::query()->pluck('id')->all();
			$userIds          = User::query()->pluck('id')->all();

			if (empty($purchaseOrderIds) || empty($userIds)) {
				$this->command->warn("Skipping PurchaseOrderHistorySeeder: No PurchaseOrders or Users found.");
				return;
			}

			// 2. Loop through every Purchase Order ID to create a history trail
			foreach ($purchaseOrderIds as $order_id) {

				// Randomly select a user who initiated these changes
				$userId = Arr::random($userIds);

				// Randomly generate between 4 and 8 events per Purchase Order
				$numEvents = mt_rand(4, 8);

				for ($i = 0; $i < $numEvents; $i++) {

					// Simulate event time: occurred randomly within the last 3 days (72 hours)
					$hoursAgo = mt_rand(0, 72);

					// Generate a precise, random timestamp for creation/update
					$historyTime = $now->copy()->subHours($hoursAgo)->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59))->timezone(config('app.timezone', 'UTC'))->toDateTimeString();

					// Event type rotation
					$events = [
						'Created',
						'Status Changed',
						'Item Updated',
						'Vendor Note Added',
						'Invoice Attached',
						'Received'
					];
					$event  = Arr::random($events);

					// Simple dynamic description based on event
					$description = match ($event) {
						'Created' => 'Purchase Order initialized.',
						'Status Changed' => 'Status updated to ' . Arr::random([
								'Awaiting Approval',
								'Sent to Vendor',
								'Completed'
							]),
						'Item Updated' => 'Quantity or price adjusted for an item.',
						'Vendor Note Added' => 'Vendor communication added to the record.',
						'Invoice Attached' => 'Received and attached invoice document.',
						'Received' => 'Partial or full receipt recorded.',
						default => 'General update.',
					};

					$historyInserts[] = [
						'purchase_order_id' => $order_id,
						'user_id'           => $userId,
						'action'            => Str::lower(Str::replace(' ', '_', $event)),
						'event'             => $event,
						'details'           => $description,
						'description'       => $description,
						'created_at'        => $historyTime,
						'updated_at'        => $historyTime,
					];

					$totalHistoryCount++;

					// 3. Chunked Insertion: If the batch hits the size limit, insert and clear
					if (count($historyInserts) >= self::BATCH_SIZE) {
						DB::table('purchase_order_histories')->insert($historyInserts);
						$historyInserts = [];
					}
				}
			}

			// 4. Insert remaining records
			if (!empty($historyInserts)) {
				DB::table('purchase_order_histories')->insert($historyInserts);
			}

			$this->command->info("Successfully seeded " . $totalHistoryCount . " Purchase Order history records via chunked insertion.");
		}
	}
