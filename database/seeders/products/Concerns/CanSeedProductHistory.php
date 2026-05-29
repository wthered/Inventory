<?php

	namespace Database\Seeders\products\Concerns;

	use App\Enums\ProductHistoryAction;
	use App\Models\Product;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	trait CanSeedProductHistory {
		protected function seedHistoryForProduct(Product $product, array $users): void {
			$allPossibleActions = ProductHistoryAction::cases();
			$lastDate           = Carbon::now()->subMonths();
			$currentPrice       = $product->selling_price;
			$currentStock       = $product->current_stock;

			// Δημιουργούμε 3-8 τυχαίες ενέργειες ιστορικού ανά προϊόν
			for ($i = 0; $i < mt_rand(3, 8); $i++) {
				$randomAction = fake()->randomElement($allPossibleActions);
				$lastDate     = $lastDate->copy()->addDays(mt_rand(1, today()->day))->addMinutes(mt_rand(0, 59));

				$newValue = null;

				switch ($randomAction) {
					case ProductHistoryAction::PRICE_UPDATED:
						$oldValue     = $currentPrice;
						$currentPrice += fake()->randomFloat(2, -10, 20);
						$newValue     = max(1, $currentPrice);
						break;

					case ProductHistoryAction::STOCK_ADJUSTED:
						$oldValue     = $currentStock;
						$currentStock += rand(-5, 15);
						$newValue     = max(0, $currentStock);
						break;

					default:
						$oldValue = 'Initial System Entry';
						$newValue = 'Confirmed';
						break;
				}

				DB::table('product_history')->insert([
					'product_id' => $product->id,
					'user_id'    => fake()->randomElement($users),
					'action'     => $randomAction->value,
					'details'    => json_encode([
						'old_value' => $oldValue,
						'new_value' => $newValue,
						'note'      => fake()->sentence(),
					]),
					'ip_address' => fake()->ipv4(),
					'reference'  => Str::upper(Str::random(8)) . '-' . $lastDate->format('Y-m-d'),
					'created_at' => $lastDate,
					'updated_at' => $lastDate,
				]);
			}
		}
	}