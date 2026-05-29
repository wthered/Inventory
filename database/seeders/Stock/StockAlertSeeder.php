<?php

	namespace Database\Seeders\Stock;

	use App\Enums\Inventory\AlertStatus;
	use App\Enums\Inventory\AlertType;
	use App\Models\Product;
	use App\Models\StockAlert;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Collection;

	class StockAlertSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$products   = Product::query()->pluck('id');
			$warehouses = Warehouse::query()->pluck('id');
			$administrators = User::role('admin')->pluck('id');

			if ($products->isEmpty() || $warehouses->isEmpty()) {
				$this->command->warn("Πρέπει να έχεις προϊόντα και αποθήκες για να τρέξει ο StockAlertSeeder.");
				return;
			}

			// 1. Δημιουργία μερικών "Low Stock" Alerts (Active)
			foreach (Collection::range(1, 5) as $index) {
				StockAlert::query()->create([
					'product_id'         => $products->random(),
					'warehouse_id'       => $warehouses->random(),
					'alert_type'         => AlertType::LOW_STOCK,
					'current_quantity'   => mt_rand(1, 8),
					'threshold_quantity' => 10,
					'message'            => 'Το απόθεμα έπεσε κάτω από το όριο ασφαλείας.',
					'status'             => AlertStatus::ACTIVE,
				]);
			}

			// 2. Δημιουργία ενός "Expired" Alert
			StockAlert::query()->create([
				'product_id'       => $products->random(),
				'warehouse_id'     => $warehouses->random(),
				'alert_type'       => AlertType::EXPIRED,
				'current_quantity' => mt_rand(1, 16),
				'expiry_date'      => now()->subDays(5)->toDateString(),
				'message'          => 'Προσοχή! Το προϊόν έχει λήξει.',
				'status'           => AlertStatus::ACTIVE,
			]);

			// 3. Δημιουργία "Expiring Soon" Alert
			StockAlert::query()->create([
				'product_id'       => $products->random(),
				'warehouse_id'     => $warehouses->random(),
				'alert_type'       => AlertType::EXPIRING_SOON,
				'current_quantity' => mt_rand(1, 25),
				'expiry_date'      => now()->addDays(10),
				'message'          => 'Η ημερομηνία λήξης πλησιάζει (εντός 10 ημερών).',
				'status'           => AlertStatus::ACTIVE,
			]);

			// 4. Δημιουργία μερικών "Resolved" Alerts (για να τεστάρουμε το Scope)
			// Αυτά δεν θα φαίνονται αν έχεις το Global Scope ενεργό
			foreach (Collection::range(1, 3) as $index) {
				StockAlert::query()->create([
					'product_id'         => $products->random(),
					'warehouse_id'       => $warehouses->random(),
					'alert_type'         => AlertType::OUT_OF_STOCK,
					'current_quantity'   => 0,
					'threshold_quantity' => 5,
					'message'            => 'Έγινε αναπλήρωση αποθέματος.',
					'status'             => AlertStatus::RESOLVED,
					'resolved_at'        => now()->subHours(rand(1, 24)),
					'resolved_by'        => $administrators->random(),
				]);
			}
		}
	}
