<?php

	namespace Database\Factories;

	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Product;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Carbon;

	class ProductFactory extends Factory {
		protected $model = Product::class;

		public function definition(): array {
			return [
				'sku'             => $this->faker->word(),
				'barcode'         => $this->faker->word(),
				'name'            => $this->faker->name(),
				'slug'            => $this->faker->slug(),
				'description'     => $this->faker->text(),
				'cost_price'      => $this->faker->word(),
				'selling_price'   => $this->faker->word(),
				'discount_price'  => $this->faker->word(),
				'unit'            => $this->faker->word(),
				'min_stock_level' => $this->faker->randomNumber(),
				'max_stock_level' => $this->faker->randomNumber(),
				'reorder_point'   => $this->faker->randomNumber(),
				'current_stock'   => $this->faker->randomNumber(),
				'track_inventory' => $this->faker->boolean(),
				'is_active'       => $this->faker->boolean(),
				'specifications'  => $this->faker->words(),
				'created_at'      => Carbon::now(),
				'updated_at'      => Carbon::now(),
				'images'          => $this->faker->word(),
				'category_id' => Category::query()->factory(),
				'brand_id'    => Brand::query()->factory(),
			];
		}
	}
