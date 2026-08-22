<?php

	namespace Database\Seeders\inventories\Audits;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use DB;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Str;

	class InventoryAuditSeeder extends Seeder {
		public function run(): void {
			$warehouses = Warehouse::query()->pluck('id');
			$users = User::query()->pluck('id');

			if ($warehouses->isEmpty() || $users->isEmpty()) {
				$this->command->warn('Skipping Audits: No warehouses or users found.');
				return;
			}

			$audits = [];
			$now = Carbon::now();

			// Δημιουργούμε 10 απογραφές
			for ($i = 1; $i <= 10; $i++) {
				$status = fake()->randomElement(SalesOrderStatus::cases());
				$startedAt = $status !== SalesOrderStatus::DRAFT->value ? $now->copy()->subDays(mt_rand(1, 30)) : null;
				$completedAt = $status === SalesOrderStatus::COMPLETED->value ? $startedAt?->copy()->addHours(rand(2, 24)) : null;

				$type = fake()->randomElement([SalesOrder::class, PurchaseOrder::class]);

				$warehouse = $warehouses->random();
				$audits[] = [
					'audit_number'   => 'AUD-'.today()->format('Y-m-d').'-W'.$warehouse.'-'.Str::upper(Str::random(4)),
					'warehouse_id'   => $warehouse,
					'created_by'     => $users->random(),
					'status'         => $status,
					'started_at'     => $startedAt,
					'completed_at'   => $completedAt,
					'notes'          => fake()->randomElement($this->generateNotes()),
					'auditable_type' => $type,
					'auditable_id'   => mt_rand(1, 10),
					'metadata'       => json_encode([
						'exception_class' => $type,
						'file'            => __FILE__,
						'line'            => __LINE__,
						'validated_data'  => [],
					]),
					'created_at'     => $now,
					'updated_at'     => $now,
				];
			}

			DB::table('inventory_audits')->insert($audits);

			// Μετά την κεφαλίδα, καλούμε τον Seeder των Items
			$this->call(InventoryAuditItemSeeder::class);
		}

		private function generateNotes(): array {
			// Define realistic audit notes array
			return [
				// Physical Inventory Stock Take
				'Quarterly full warehouse physical stock count.',
				'Annual cycle count for high-value SKU categories.',
				'Routine spot check for Zone A racks following shift transition.',
				'Discrepancy reconciliation after quarterly physical count.',

				// Exception & Error Logs
				'System exception logged: Product quantity mismatch detected during sale fulfillment.',
				'Failed line item reservation: Specified location lacks sufficient physical stock.',
				'Location resolution failure during sales order header update.',
				'Manual stock adjustment triggered due to damaged inventory report.',

				// Transfers & Receiving
				'Inbound shipment reconciliation against purchase order manifests.',
				'Inter-warehouse transfer validation audit prior to dispatch.',
			];
		}
	}
