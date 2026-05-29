<?php

	namespace Database\Seeders\inventories;

	use App\Enums\TransferStatus;
	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	class TransferStatusSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$now = Carbon::now(config('app.timezone'));

			// Παίρνουμε ΟΛΑ τα cases από το Enum
			$statuses = Collection::make(TransferStatus::cases())->map(function ($status) use ($now) {
				return [
					'id'         => $status->value, // ✅ Εδώ μπαίνουν τα IDs 1 έως 13
					'name'       => $status->label(),
					'slug'       => Str::slug($status->label()),
					'color'      => $status->color(),
					'created_at' => $now->copy()->subMonths(mt_rand(1, today()->month))->subDays(mt_rand(1, today()->dayOfYear()))->toDateTimeString(),
					'updated_at' => $now->toDateTimeString(),
				];
			});

			// Χρησιμοποιούμε upsert ή truncate/insert για να είμαστε σίγουροι
			DB::table('transfer_statuses')->delete();
			DB::table('transfer_statuses')->insert($statuses->toArray());

			print("Seeded " . $statuses->count() . " transfer statuses from Enum.\n");
		}
	}