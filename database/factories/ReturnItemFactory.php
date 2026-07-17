<?php

	namespace Database\Factories;

	use App\Enums\Stock\QualityStatus;
	use App\Models\Product;
	use App\Models\StockReturn;
	use App\Models\StockReturnItem;
	use Illuminate\Database\Eloquent\Factories\Factory;

	/**
	 * @extends Factory<StockReturnItem>
	 */
	class ReturnItemFactory extends Factory {
		protected $model = StockReturnItem::class;

		/**
		 * Define the model's default state.
		 *
		 * @return array<string, mixed>
		 */
		public function definition(): array {
			$quality = fake()->randomElement(QualityStatus::cases());

			return [
				'stock_return_id'  => StockReturn::factory(),
				'product_id'       => Product::factory(),
				'location_id'      => null,
				'quantity'         => mt_rand(1, 12),
				'unit_cost'        => function (array $attributes) {
					return Product::find($attributes['product_id'])->cost_price ?? mt_rand(64, 256);
				},
				'quality_status'   => $quality->value,
				'is_restockable'   => !in_array($quality, [QualityStatus::DAMAGED, QualityStatus::DEFECTIVE]),
				'inspection_notes' => $quality !== QualityStatus::NEW
					? "Automatic Flag: Product returned as " . $quality->value . " and requires secondary physical evaluation."
					: null,
				'restocked_at'     => null,
				'created_at'       => now()->subHours(mt_rand(1, 72)),
				'updated_at'       => now()->addHours(mt_rand(1, 72)),
			];
		}
	}