<?php

	namespace Database\Seeders\Stock;

	use App\Enums\TransferStatus;
	use App\Models\StockTransfer;
	use App\Models\User;
	use App\Models\Warehouse;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;

	class StockTransferSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$warehouses = Warehouse::pluck('id');
			$users = User::pluck('id');

			if ($warehouses->count() < 2) {
				$this->command->error('You need at least 2 warehouses to seed transfers.');
				return;
			}

			Collection::range(1, 128)->each(function ($i) use ($warehouses, $users) {
				$source = $warehouses->random();
				// Διασφάλιση ότι source != target
				$target = $warehouses->filter(fn($id) => $id !== $source)->random();

				$createdAt = now()->subDays(mt_rand(2, 60));
				$status = fake()->randomElement(TransferStatus::cases());

				$approvedAt = null;
				$receivedAt = null;

				// Λογική εγκρίσεων
				if (!in_array($status, [TransferStatus::DRAFT, TransferStatus::PENDING])) {
					$approvedAt = $createdAt->copy()->addHours(mt_rand(1, 24));
				}

				// Λογική παραλαβών
				if (in_array($status, [TransferStatus::COMPLETED, TransferStatus::PARTIAL])) {
					$receivedAt = ($approvedAt ?? $createdAt)->copy()->addDays(mt_rand(1, 5));
				}

				// Εδώ χρησιμοποιούμε Create για να τρέξουν τυχόν Booted Events/Observers
				// αν έχεις ορίσει αυτόματη δημιουργία TransferNumber
				StockTransfer::create([
					'transfer_number'        => StockTransfer::generateTransferNumber(),
					'source_warehouse_id'    => $source,
					'target_warehouse_id'    => $target,
					'status_id'              => $status->value,
					'transfer_date'          => $createdAt,
					'expected_delivery_date' => $createdAt->copy()->addDays(mt_rand(2, 7)),
					'approved_at'            => $approvedAt,
					'approved_by'            => $approvedAt ? $users->random() : null,
					'received_at'            => $receivedAt,
					'received_by'            => $receivedAt ? $users->random() : null,
					'created_by'             => $users->random(),
					'notes'                  => fake()->optional(0.7)->sentence(), // 70% πιθανότητα για note
					'created_at'             => $createdAt,
					'updated_at'             => $receivedAt ?? $approvedAt ?? $createdAt,
				]);
			});

			$this->command->info('Finished seeding 128 Stock Transfers.');
		}
	}
