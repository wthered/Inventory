<?php

	namespace Database\Seeders\Orders;

	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;
	use Illuminate\Database\Seeder;

	class SalesOrderHistorySeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Παίρνουμε όλες τις παραγγελίες μαζί με τα items τους
			$sales = SalesOrder::with('items')->get();

			// Παίρνουμε τα IDs των χρηστών με τα κατάλληλα δικαιώματα για να μοιράσουμε τις ενέργειες τυχαία
			// Οι χρήστες πρέπει να είναι εδώ και όχι παρακάτω για να μη γίνονται multiple database queries

			// --- 2. Επιβεβαίωση / Σε Εκκρεμότητα ---
			// Ανάκτηση των IDs του προσωπικού που έχει δικαίωμα να επεξεργαστεί/επιβεβαιώσει μια πώληση
			$salesStaff = User::permission('sales_order.update')->pluck('id');

			// --- 3. Σε Επεξεργασία (Picking / Packing στην Αποθήκη) ---
			// Ανάκτηση των IDs του προσωπικού που έχει δικαίωμα επεξεργασίας/αποστολής στην αποθήκη
			$warehouseStaff = User::permission('sales_order.ship')->pluck('id');

			// --- 4. Απεστάλη (Shipped) ---
			// Ανάκτηση όλων των χρηστών που έχουν το δικαίωμα για Picking / Packing / Shipping
			$shippedStaff = User::permission('sales_order.ship')->pluck('id');

			// --- 5. Παραδόθηκε / Ολοκληρώθηκε ---
			// Ανάκτηση των IDs των χρηστών που έχουν δικαίωμα Παράδοσης (ship) ή Έγκρισης/Ολοκλήρωσης (approve)
			$fulfillmentStaff = User::permission(['sales_order.ship', 'sales_order.approve'])->pluck('id');

			// --- 6. Διαχείριση Ακυρωμένων (Cancelled) ---
			// Ανάκτηση των IDs των χρηστών που έχουν δικαίωμα Ακύρωσης / Διαγραφής πωλήσεων
			$cancellationStaffIds = User::permission('sales_order.delete')->pluck('id');

			foreach ($sales->shuffle() as $sale) {
				$orderDate = $sale->order_date;
				$creatorId = $sale->created_by;

				// --- 1. Πάντα ξεκινάμε με τη Δημιουργία (Draft) ---
				$sale->history()->create([
					'sales_order_id' => $sale->id,
					'action'         => 'order_created',
					'event'          => 'Δημιουργία Παραγγελίας',
					'description'    => "Η παραγγελία ".$sale->order_number." καταχωρήθηκε ως Πρόχειρο στο σύστημα.",
					'details'        => ['status_id' => SalesOrderStatus::DRAFT->value],
					'user_id'        => $creatorId,
					'updated_at'     => $orderDate->setHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
				]);

				// Αν η παραγγελία έμεινε Draft, σταματάμε εδώ
				if ($sale->status_id === SalesOrderStatus::DRAFT) {
					continue;
				}

				// --- 2. Επιβεβαίωση / Σε Εκκρεμότητα ---
				$sale->history()->create([
					'action'      => 'status_changed',
					'event'       => 'Επιβεβαίωση Παραγγελίας',
					'description' => "Η παραγγελία εγκρίθηκε και προχώρησε σε κατάσταση εκκρεμότητας.",
					'details'     => [
						'old_status' => SalesOrderStatus::DRAFT->value,
						'new_status' => SalesOrderStatus::PENDING->value
					],
					'user_id'     => $salesStaff->random(),
					'created_at'  => $orderDate->copy()->startOfDay()->addHours(11), // 11:00
					'updated_at'  => $orderDate->copy()->startOfDay()->addHours(11),
				]);

				if ($sale->status_id === SalesOrderStatus::PENDING) {
					continue;
				}

				// --- 3. Σε Επεξεργασία (Picking / Packing στην Αποθήκη) ---
				if (in_array($sale->status_id, [
					SalesOrderStatus::PROCESSING, SalesOrderStatus::SHIPPED, SalesOrderStatus::DELIVERED,
					SalesOrderStatus::COMPLETED
				])) {
					$sale->history()->create([
						'action'      => 'warehouse_processing',
						'event'       => 'Σε Επεξεργασία (Αποθήκη)',
						'description' => "Ξεκίνησε η διαδικασία συλλογής (Picking) των προϊόντων στην αποθήκη.",
						'details'     => ['status_id' => SalesOrderStatus::PROCESSING->value],
						'user_id'     => $warehouseStaff->random(),
						'created_at'  => $orderDate->copy()->startOfDay()->addHours(14), // 14:00
						'updated_at'  => $orderDate->copy()->startOfDay()->addHours(14),
					]);
				}

				if ($sale->status_id === SalesOrderStatus::PROCESSING) {
					continue;
				}

				// --- 4. Απεστάλη (Shipped) ---
				// Χρησιμοποιούμε την πραγματική ημερομηνία αποστολής αν υπάρχει, αλλιώς +1 μέρα
				$shippingDate = $sale->shipping_date ?? $orderDate->copy()->addDay();

				if (in_array($sale->status_id, [
					SalesOrderStatus::SHIPPED, SalesOrderStatus::DELIVERED, SalesOrderStatus::COMPLETED
				])) {
					$sale->history()->create([
						'action'      => 'order_shipped',
						'event'       => 'Απεστάλη',
						'description' => "Η παραγγελία παραδόθηκε στη μεταφορική εταιρεία για αποστολή.",
						'details'     => ['status_id' => SalesOrderStatus::SHIPPED->value],
						'user_id'     => $shippedStaff->random(),
						'created_at'  => $shippingDate->addHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
						'updated_at'  => $shippingDate,
					]);
				}

				if ($sale->status_id === SalesOrderStatus::SHIPPED) {
					continue;
				}

				// --- 5. Παραδόθηκε / Ολοκληρώθηκε ---
				if (in_array($sale->status_id, [SalesOrderStatus::DELIVERED, SalesOrderStatus::COMPLETED])) {
					$deliveryDate = $shippingDate->copy()->addDays(mt_rand(2, 10));

					$sale->history()->create([
						'action'      => 'order_delivered',
						'event'       => 'Παραδόθηκε / Ολοκληρώθηκε',
						'description' => "Η παραγγελία παραδόθηκε επιτυχώς στον πελάτη.",
						'details'     => ['status_id' => $sale->status_id->value],
						'user_id'     => $fulfillmentStaff->random(),
						'created_at'  => $deliveryDate->addHours(16)->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
						'updated_at'  => $deliveryDate,
					]);
				}

				// --- 6. Διαχείριση Ακυρωμένων (Cancelled) ---
				if ($sale->status_id === SalesOrderStatus::CANCELLED) {
					$sale->history()->create([
						'action'      => 'order_cancelled',
						'event'       => 'Ακυρώθηκε',
						'description' => "Η παραγγελία ακυρώθηκε κατόπιν επικοινωνίας με τον πελάτη.",
						'details'     => ['status_id' => SalesOrderStatus::CANCELLED->value],
						'user_id'     => $cancellationStaffIds->random(),
						'created_at'  => $orderDate->addHours(mt_rand(0, 23))->setMinutes(mt_rand(0, 59))->setSeconds(mt_rand(0, 59)),
						'updated_at'  => $orderDate,
					]);
				}
			}
		}
	}