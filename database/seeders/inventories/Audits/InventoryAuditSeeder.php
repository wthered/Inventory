<?php

	namespace Database\Seeders\inventories\Audits;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\User;
	use App\Models\Warehouse;
	use DB;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Str;

	class InventoryAuditSeeder extends Seeder {
		public function run(): void {
			$warehouses = Warehouse::pluck('id');
			$users      = User::pluck('id');

			if ($warehouses->isEmpty() || $users->isEmpty()) {
				$this->command->warn('Skipping Audits: No warehouses or users found.');
				return;
			}

			$audits = [];
			$now    = Carbon::now();

			// Δημιουργούμε 10 απογραφές
			for ($i = 1; $i <= 10; $i++) {
				$status      = fake()->randomElement(SalesOrderStatus::cases());
				$startedAt   = $status !== SalesOrderStatus::DRAFT->value ? $now->copy()->subDays(rand(1, 30)) : null;
				$completedAt = $status === SalesOrderStatus::COMPLETED->value ? $startedAt?->copy()->addHours(rand(2, 24)) : null;

				$audits[] = [
					'audit_number' => 'AUD-' . $now->format('Y') . '-' . Str::upper(Str::random(6)),
					'warehouse_id' => $warehouses->random(),
					'created_by'   => $users->random(),
					'status'       => $status,
					'started_at'   => $startedAt,
					'completed_at' => $completedAt,
					'notes'        => fake()->optional()->realText(),
					'created_at'   => $now,
					'updated_at'   => $now,
				];
			}

			DB::table('inventory_audits')->insert($audits);

			// Μετά την κεφαλίδα, καλούμε τον Seeder των Items
			$this->call(InventoryAuditItemSeeder::class);
		}
	}
