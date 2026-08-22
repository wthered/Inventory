<?php

	namespace Database\Seeders\products\Concerns;

	use App\Models\Category;
	use App\Models\Product;
	use Carbon\Carbon;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Str;

	trait CanSeedInitialProducts {

		protected function insertInitialProducts(): Carbon {
			$initialTime = Carbon::now(config('app.timezone'));
			$this->command->info('['.$initialTime->format('H:i:s').'] Creating initial product records...');

			$categories = Category::query()->has('brands')->with('brands:id')->pluck('id');
			$dataMap = $this->getCategoryDataMap();

			// Prepare your shuffled collection of Category IDs
			$categoriesList = Category::query()->pluck('id')->shuffle();

			while ($categoriesList->isNotEmpty()) {
				// Pick a random key currently present in the collection
				$randomKey = $categoriesList->keys()->random();

				// Remove and return the category ID at that key
				$categoryId = $categoriesList->pull($randomKey);

				// Resolve data type group
				$dataType = array_rand($dataMap);
				foreach ($dataMap as $key => $map) {
					if (in_array($categoryId, $map['cats'])) {
						$dataType = $key;
						break;
					}
				}

				// Fetch brands attached to this category
				$brands = Category::query()->find($categoryId)->brands()->pluck('id');
				if ($brands->isEmpty()) {
					continue;
				}

				// Build product records (2 * 1,197 * 877 = 2,099,538 products)
				for ($i = 1; $i <= 2 * $categories->count() * $brands->count(); $i++) {
					$shortId = Str::lower(Str::random(8));
					$productTime = Carbon::now();

					$baseName = fake()->randomElement($dataMap[$dataType]['names']);
					$productName = $baseName.' '.fake()->numerify().' '.Str::upper(fake()->word());

					$costPrice = fake()->randomFloat(2, 5, 512);
					$sellingPrice = fake()->randomFloat(2, 0.75 * $costPrice, 1.25 * $costPrice);

					$minStock = fake()->numberBetween(1024, 16 * 1024);
					$maxStock = $minStock + mt_rand(256, 4096);
					$brandId = $brands->random();

					// EAN-13 Barcode: 1 (prefix '2') + 4 (Cat ID) + 3 (Brand ID) + 5 (Seq) = 13 digits
					$barcode = sprintf('2%04d%03d%05d', $categoryId, $brandId, (($i - 1) % 99999) + 1);

					$this->list->push([
						'sku'             => Str::upper(fake()->bothify('SKU-####-????-').$shortId),
						'slug'            => Str::slug($productName).'-'.Str::upper($shortId),
						'barcode'         => $barcode,
						'name'            => $productName,
						'description'     => fake()->paragraph(),
						'category_id'     => $categoryId,
						'brand_id'        => $brandId,
						'cost_price'      => $costPrice,
						'selling_price'   => $sellingPrice,
						'discount_price'  => fake()->boolean() ? fake()->randomFloat(2, 0.66 * $sellingPrice, 0.75 * $sellingPrice) : $sellingPrice,
						'tax_percent'     => fake()->randomElement([6, 13, 24]),
						'unit'            => fake()->randomElement(['pcs', 'kg', 'liter', 'pack']),
						'weight'          => fake()->numberBetween(10, 4096),
						'track_inventory' => fake()->boolean(90),
						'min_stock_level' => $minStock,
						'current_stock'   => mt_rand($minStock, $maxStock),
						'max_stock_level' => $maxStock,
						'reorder_point'   => mt_rand($minStock, $maxStock),
						'is_active'       => fake()->boolean(95),
						'specifications'  => json_encode($this->generateSpecifications($dataType)),
						'created_at'      => $productTime->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59)),
						'updated_at'      => $productTime->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59)),
					]);

					// Batch insert every 2 * 1024 items to prevent MySQL parameter limits
					// Seeding breaks at 2978 rows per batch
					if ($this->list->count() >= 2048) {
						$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
							Product::query()->insert($chunk->toArray());
						});

						$this->list = Collection::empty();
					}
				}

				$this->command->getOutput()->write("[Category #".$randomKey." / ".$categoriesList->count()."] Assigned products in category: #".$categoryId."...\r");
			}

			// Final flush for any remaining items left in memory
			if ($this->list->isNotEmpty()) {
				$this->list->chunk(self::BATCH_SIZE)->each(function (Collection $chunk) {
					Product::query()->insert($chunk->toArray());
				});

				$this->list = Collection::empty();
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
