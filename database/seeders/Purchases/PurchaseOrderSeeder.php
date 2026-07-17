<?php

	namespace Database\Seeders\Purchases;

	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Str;

	class PurchaseOrderSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Έλεγχος αν υπάρχουν τα απαραίτητα δεδομένα
			if (!Supplier::query()->exists() || !Warehouse::query()->exists() || !User::query()->exists()) {
				$this->command->warn('⚠️ Missing suppliers, warehouses, or users. Skipping '.__CLASS__.'.');
				return;
			}

			$suppliers = Supplier::query()->pluck('id')->toArray();
			$warehouses = Warehouse::query()->pluck('id')->toArray();

			// Επιλέγουμε χρήστες που έχουν δικαίωμα να δημιουργήσουν ή να εγκρίνουν PO
			$staffUsers = User::role(['purchase_manager'])->pluck('id')->toArray();

			// Δημιουργία τυχαίου αριθμού παραγγελιών αγοράς
			for ($order = 0; $order <= mt_rand(128, 512); $order++) {
				$orderDate = fake()->dateTimeBetween('-6 months');

				// Αρχικά οικονομικά δεδομένα (θα ενημερωθούν σωστά αφού μπουν τα items αν χρειαστεί,
				// αλλά βάζουμε ρεαλιστικές τιμές βάσης)
				$subtotal = fake()->randomFloat(2, 500, 5000);
				$tax = $subtotal * 0.24; // 24% ΦΠΑ
				$discount = fake()->randomFloat(2, 0, 200);

				$expected = fake()->optional(0.8)->dateTimeBetween($orderDate, '+2 weeks');
				PurchaseOrder::query()->create([
					'po_number'       => 'PO-' . $orderDate->format('Ymd') . '-' . Str::upper(Str::random(4)),
					'supplier_id'     => fake()->randomElement($suppliers),
					'warehouse_id'    => fake()->randomElement($warehouses),
					'status_id'       => fake()->randomElement(PurchaseOrderStatus::openStatuses()),
					'order_date'      => $orderDate, // Το Eloquent θα το μετατρέψει αυτόματα σε Y-m-d λόγω του cast
					'expected_date'   => $expected,
					'received_at'     => fake()->boolean() ? Carbon::parse($orderDate)->addDays(mt_rand(1, 180)) : null,
					'subtotal'        => $subtotal,
					'tax_amount'      => $tax,
					'discount_amount' => $discount,
					'grand_total'     => $subtotal + $tax - $discount,
					'created_by'      => fake()->randomElement($staffUsers),
					'notes'           => fake()->optional(0.3)->sentence(),
					'created_at'      => Carbon::now()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
					'updated_at'      => Carbon::now()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
				]);
			}

			$this->command->info('✅ '.__CLASS__.' completed successfully.');
		}
	}
