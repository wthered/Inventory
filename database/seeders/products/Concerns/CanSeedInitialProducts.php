<?php

	namespace Database\Seeders\products\Concerns;

	use App\Models\Category;
	use App\Models\Product;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	trait CanSeedInitialProducts {

		protected function insertInitialProducts(): Carbon {
			$this->command->info('Creating initial product records...');

			$categories = Category::query()->has('brands')->with('brands:id')->pluck('id');
			$initialTime = Carbon::now(config('app.timezone'));
			$dataMap = $this->getCategoryDataMap();

			foreach ($categories as $category) {

				// 1. Παίρνουμε ένα τυχαίο KEY (string) ως default
				$dataType = array_rand($dataMap);

				// 2. Αναζήτηση αν η κατηγορία ανήκει σε συγκεκριμένο group
				foreach ($dataMap as $key => $map) {
					if (in_array($category, $map['cats'])) {
						$dataType = $key;
						break;
					}
				}

				$brands = Category::query()->find($category)->brands()->pluck('id');

				for ($i = 0; $i < 16; $i++) {
					$uniqueId = Str::uuid7()->toString();
					$shortId = substr(explode('-', $uniqueId)[0], 0, 8);
					$product_time = Carbon::now();

					// Δημιουργία ρεαλιστικού ονόματος
					$baseName = fake()->randomElement($dataMap[$dataType]['names']);
					$productName = $baseName.' '.fake()->numerify().' '.Str::upper(fake()->word());

					$costPrice = fake()->randomFloat(2, 5, 512);
					$sellingPrice = fake()->randomFloat(2, 0.75 * $costPrice, 1.25 * $costPrice);
					$stock = ['min' => fake()->numberBetween(8, 1024)];
					$stock['max'] = $stock['min'] + mt_rand(256, 4096);
					$stock['current'] = mt_rand($stock['min'], $stock['max']);

					$this->list->push([
						'sku'             => Str::upper(fake()->bothify('SKU-####-????-').$shortId),
						'slug'            => Str::slug($productName).'-'.Str::upper($shortId),
						'barcode'         => fake()->ean13(),
						'name'            => $productName,
						'description'     => fake()->optional()->paragraph(),
						'category_id'     => $category,
						'brand_id'        => $brands->random(),
						'cost_price'      => $costPrice,
						'selling_price'   => $sellingPrice,
						'discount_price'  => fake()->boolean() ? fake()->randomFloat(2, 0.66 * $sellingPrice, 0.75 * $sellingPrice) : $sellingPrice,
						'unit'            => fake()->randomElement(['pcs', 'kg', 'liter', 'pack']),
						'track_inventory' => fake()->boolean(90),
						'min_stock_level' => $stock['min'],
						'current_stock'   => $stock['current'],
						'max_stock_level' => $stock['max'],
						'reorder_point'   => mt_rand($stock['min'], $stock['max']),
						'is_active'       => fake()->boolean(95),

						// Παραγωγή specs βάσει τύπου
						'specifications'  => json_encode($this->generateSpecifications($dataType)),

						'created_at' => $product_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => $product_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
					]);

//					if ($this->list->count() >= static::BATCH_SIZE) {
//						Product::insertOrIgnore($this->list->toArray());
//						$this->list = collect();
//					}
				}

				$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
					Product::query()->insertOrIgnore($chunk->toArray());
				});
				$this->list = collect();
			}

			if ($this->list->isNotEmpty()) {
				Product::query()->insertOrIgnore($this->list->toArray());
				$this->list = collect();
			}

			return $initialTime;
		}

		private function getCategoryDataMap(): array {
			return [
				'tech_heavy'          => [
					'cats'  => [1, 3, 4, 5], // Electronics, Computers, Smartphones, Gaming
					'names' => [
						'High-End Laptop', 'Ultra-Slim Smartphone', 'Gaming Console Pro',
						'Mechanical Keyboard RGB', 'Noise Cancelling Headphones', '4K Curved Monitor',
						'Smartwatch Series X', 'External SSD 2TB', 'Wireless Gaming Mouse'
					],
				],
				'appliances'          => [
					'cats'  => [2, 8], // Home Appliances, Kitchenware
					'names' => [
						'Smart Refrigerator', 'Air Fryer XL', 'Espresso Coffee Machine',
						'Robot Vacuum Cleaner', 'Induction Cooktop', 'Digital Microwave',
						'Stand Mixer Pro', 'Electric Steam Iron', 'Dishwasher Silent-Run'
					],
				],
				'furniture'           => [
					'cats'  => [6, 9, 14], // Furniture, Lighting, Gardening
					'names' => [
						'Ergonomic Office Chair', 'Minimalist Oak Desk', 'Velvet Sofa Bed',
						'LED Floor Lamp', 'Ceramic Dining Table', 'Modern Wall Sconce',
						'Garden Lounge Set', 'Outdoor Solar Light', 'Bookshelf 5-Tier'
					],
				],
				'fashion'             => [
					'cats'  => [11, 20], // Fashion, Travel & Luggage
					'names' => [
						'Premium Cotton Hoodie', 'Slim Fit Denim Jeans', 'Waterproof Windbreaker',
						'Leather Crossbody Bag', 'Running Sports Shoes', 'Hard-Shell Suitcase',
						'Wool Blend Coat', 'Silk Neck Tie', 'Canvas Travel Backpack'
					],
				],
				'lifestyle'           => [
					'cats'  => [10, 12, 19], // Sports, Beauty, Health & Wellness
					'names' => [
						'Yoga Mat Anti-Slip', 'Adjustable Dumbbell Set', 'Hydrating Face Serum',
						'Electric Toothbrush', 'Whey Protein Isolate', 'Digital Blood Pressure Monitor',
						'Lavender Essential Oil', 'Professional Hair Dryer', 'Massage Roller'
					],
				],
				'office_edu'          => [
					'cats'  => [7, 13, 18], // Office Supplies, Books, Toys & Games
					'names' => [
						'Refillable Fountain Pen', 'Hardcover Journal', 'Board Game Classic Edition',
						'Educational Building Blocks', 'A4 Printing Paper 500 Sheets', 'Desk Organizer Set',
						'Science Kit for Kids', 'Bestseller Mystery Novel', 'Fine Tip Markers'
					],
				],
				'automotive_pet_food' => [
					'cats'  => [15, 16, 17], // Automotive, Pets, Food & Beverages
					'names' => [
						'Synthetic Engine Oil', 'Microfiber Car Cleaning Kit', 'Grain-Free Dog Food',
						'Interactive Cat Toy', 'Organic Ground Coffee', 'Natural Mineral Water',
						'Energy Drink Sugar-Free', 'Pet Grooming Brush', 'Dashboard Camera 1080p'
					],
				]
			];
		}

		private function generateSpecifications(string $type): array {
			return match ($type) {
				'tech_heavy' => [
					'Brand'        => fake()->company(),
					'Model'        => Str::upper(fake()->bothify('??-###')),
					'Warranty'     => '24 Months',
					'Battery'      => fake()->randomElement(['4000 mAh', '5000 mAh', 'N/A']),
					'Connectivity' => 'Bluetooth 5.2, Wi-Fi 6',
				],
				'appliances' => [
					'Energy Class' => fake()->randomElement(['A+++', 'A++', 'A+']),
					'Power'        => fake()->numberBetween(500, 2400).'W',
					'Material'     => 'Stainless Steel',
					'Capacity'     => fake()->numberBetween(1, 10).' L',
				],
				'furniture'  => [
					'Material'   => fake()->randomElement(['Solid Wood', 'Metal', 'High-Grade Plastic', 'Fabric']),
					'Dimensions' => fake()->numberBetween(40, 200).'x'.fake()->numberBetween(40, 200).'x'.fake()->numberBetween(40, 100).' cm',
					'Assembly'   => fake()->randomElement(['Required', 'Pre-assembled']),
					'Weight'     => fake()->numberBetween(2, 50).' kg',
				],
				'fashion'    => [
					'Size'     => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
					'Color'    => fake()->safeColorName(),
					'Material' => fake()->randomElement(['100% Cotton', 'Polyester', 'Leather', 'Silk', 'Wool']),
					'Gender'   => fake()->randomElement(['Men', 'Women', 'Unisex']),
				],
				'lifestyle'  => [
					'Weight'      => fake()->randomElement(['500g', '1kg', '2kg', 'N/A']),
					'Skin Type'   => fake()->randomElement(['All types', 'Oily', 'Dry', 'Sensitive']),
					'Expiration'  => '12 Months after opening',
					'Ingredients' => 'Organic & Natural',
				],
				'office_edu' => [
					'Pages'     => fake()->randomElement(['100', '200', '500']),
					'Material'  => 'Recycled Paper',
					'Age'       => '3+ years',
					'Ink Color' => fake()->randomElement(['Black', 'Blue', 'Red', 'Multi']),
				],
				default      => [
					'Origin'    => fake()->country(),
					'Pack Size' => 'Single Unit',
					'Quality'   => 'Premium Grade',
				],
			};
		}
	}
