<?php

	namespace Database\Seeders\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use App\Models\User;
	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	class PurchaseOrderHistorySeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$totalHistoryCount = 0;
			$historyInserts = [];

			$purchaseOrders = PurchaseOrder::all();
			$users = User::query()->pluck('id');

			if ($purchaseOrders->isEmpty() || $users->isEmpty()) {
				$this->command->warn('Purchase Order found:'.$purchaseOrders->count());
				$this->command->warn('Users found:'.$users->count());
				$this->command->warn("Skipping ".__CLASS__.": No PurchaseOrders or Users found.");
				return;
			}

			foreach ($purchaseOrders as $orderRecord) {
				$userId = $users->random();
				$baseTime = Carbon::parse($orderRecord->order_date);

				$events = ['Created', 'Approved', 'Items Verified', 'Status Shifted'];

				// 2 έως 4 λογικά βήματα ιστορικού ανά παραγγελία
				for ($i = 0; $i < mt_rand(2, 4); $i++) {
					$event = $events[$i] ?? 'General Update';
					$historyTime = $baseTime->copy()->addHours($i * mt_rand(2, 24));

					$description = match ($event) {
						'Created'        => 'Purchase Order initialized in the system.',
						'Approved'       => 'Order reviewed and approved by warehouse authorities.',
						'Items Verified' => 'Physical goods matching delivery notes verified.',
						'Status Shifted' => 'Order state migrated to its final status code.',
						default          => 'System automation state trigger.',
					};

					// Δομημένα JSON details όπως απαιτεί η νέα μας έκδοση
					$jsonDetails = [
						'triggered_by' => 'User ID '.$userId,
						'context'      => Str::slug($event, '_'),
						'snapshot'     => [
							'status_id'   => $orderRecord->status_id,
							'grand_total' => $orderRecord->grand_total
						]
					];

					$historyInserts[] = [
						'purchase_order_id' => $orderRecord->id,
						'user_id'           => $userId,
						'action'            => Str::lower(Str::replace(' ', '_', $event)),
						'event'             => $event,
						'details'           => json_encode($jsonDetails), // Cast σε JSON string για το insert
						'description'       => $description,
						'created_at'        => $historyTime->toDateTimeString(),
						'updated_at'        => $historyTime->toDateTimeString(),
					];

					$totalHistoryCount++;

					if (count($historyInserts) >= self::BATCH_SIZE) {
						DB::table('purchase_order_histories')->insert($historyInserts);
						$historyInserts = [];
					}
				}
			}

			if (!empty($historyInserts)) {
				DB::table('purchase_order_histories')->insert($historyInserts);
			}

			$this->command->info("Successfully seeded ".$totalHistoryCount." purchase order history log entries.");
		}
	}
