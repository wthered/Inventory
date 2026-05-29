<?php

	namespace Database\Seeders\inventories\Concerns;

	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	trait CanSeedAdjustments {
		abstract public function flushList(): void;

		/**
		 * @throws Throwable
		 */
		/**
		 * @throws Throwable
		 */
		protected function seedAdjustments(Collection $products, Collection $users): void {
			$this->command->info('Generating adjustment transactions safely...');

			$reasonMap = [
				'stocktake' => TransactionReason::STOCKTAKE,
				'damaged'   => TransactionReason::DAMAGED,
				'expired'   => TransactionReason::EXPIRED,
				'theft'     => TransactionReason::THEFT,
				'found'     => TransactionReason::FOUND,
				'other'     => TransactionReason::OTHER,
			];
			$reasons   = array_keys($reasonMap);

			// 1. Παίρνουμε 1000 τυχαία locations ΜΙΑ φορά
			$locationsList = DB::table('warehouse_locations')->inRandomOrder()->limit(1024)->get([
				'id',
				'warehouse_id'
			]);



			for ($i = 0; $i < 1000; $i++) {
				$product = $products->random();
				$user    = $users->random();

				// 2. ΕΠΙΛΟΓΗ ΣΥΓΚΕΚΡΙΜΕΝΟΥ LOCATION
				$location = $locationsList->random();

				$type       = fake()->boolean() ? TransactionType::IN : TransactionType::OUT;
				$reasonKey  = $reasons[array_rand($reasons)];
				$enumReason = $reasonMap[$reasonKey];

				$quantityBefore = mt_rand(24, 160);
				$adjQty         = mt_rand(1, 64);
				$quantityAfter  = ($type === TransactionType::IN) ? $quantityBefore + $adjQty : $quantityBefore - $adjQty;
				$createdAt      = now()->subDays(mt_rand(0, 90));

				// 3. ΠΡΟΣΘΗΚΗ $location ΚΑΙ $i ΣΤΟ use
				DB::transaction(function () use ($product, $location, $user, $type, $reasonKey, $enumReason, $adjQty, $quantityBefore, $quantityAfter, $createdAt, $i) {

					// 4. ΔΙΟΡΘΩΣΗ adjustment_number (Str::padLeft στο $i)
					$adjustment = StockAdjustment::create([
						'adjustment_number' => 'ADJ-' . $createdAt->format('Ymd') . '-' . Str::padLeft($i, 5, '0'),
						'warehouse_id'      => $location->warehouse_id,
						'adjustment_date'   => $createdAt,
						'status'            => 'approved',
						'created_by'        => $user,
						// Βεβαιώσου ότι παίρνεις το ID
						'approved_by'       => $user,
						'approved_at'       => $createdAt,
						'notes'             => $this->generateAdjustmentNote($type->value, $adjQty, $reasonKey),
						'created_at'        => $createdAt,
					]);

					// 5. ΧΡΗΣΗ ΤΟΥ $location (όχι της λίστας)
					StockAdjustmentItem::create([
						'stock_adjustment_id' => $adjustment->id,
						'product_id'          => $product->id,
						'location_id'         => $location->id,
						'type'                => $type->value,
						'reason'              => $reasonKey,
						'quantity'            => $adjQty,
						'quantity_before'     => $quantityBefore,
						'quantity_after'      => $quantityAfter,
						'unit_cost'           => $product->cost_price ?? 10,
						'created_at'          => $createdAt,
					]);

					InventoryTransaction::create([
						'batch_number'    => InventoryTransaction::generateTransactionNumber('ADJ'),
						'product_id'      => $product->id,
						'warehouse_id'    => $location->warehouse_id,
						'location_id'     => $location->id,
						'type'            => $type->value,
						'reason'          => $enumReason->value,
						'quantity'        => ($type === TransactionType::OUT) ? -$adjQty : $adjQty,
						'quantity_before' => $quantityBefore,
						'quantity_after'  => $quantityAfter,
						'reference_type'  => StockAdjustment::class,
						'reference_id'    => $adjustment->id,
						'created_by'      => $user,
						'created_at'      => $createdAt,
					]);
				});
			}
		}

		private function generateAdjustmentNote(string $type, int $quantity, string $reason): string {
			$notes = [
				'stocktake' => [
					'in'  => "Found extra units",
					'out' => "Missing units during stocktake"
				],
				'damaged'   => ['out' => "Units damaged - written off"],
				'expired'   => ['out' => "Expired units disposed"],
				'theft'     => ['out' => "Reported theft"],
				'found'     => ['in' => "Found in unexpected location"],
			];

			return $notes[$reason][$type] ?? "Regular adjustment of $quantity units";
		}
	}