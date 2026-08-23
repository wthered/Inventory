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

			$categories = Category::query()->has('brands')->whereNotNull('parent_id')->with('brands:id')->pluck('id');
			$dataMap = $this->getCategoryDataMap();

			while ($categories->shuffle()->isNotEmpty()) {
				// Pick a random key currently present in the collection
				$randomKey = $categories->keys()->random();

				// Remove and return the category ID at that key
				$category = $categories->pull($randomKey);

				// Resolve data type group
				$dataType = array_rand($dataMap);
				foreach ($dataMap as $key => $map) {
					if (in_array($category, $map['cats'])) {
						$dataType = $key;
						break;
					}
				}

				// Fetch brands attached to this category
				$brands = Category::query()->find($category)->brands()->pluck('id');
				if ($brands->isEmpty()) {
					continue;
				}

				// Build product records (2 * 1,172 * 877 = 2,055,688 products) will take more than 8 hours
				// 1,172 children categories and 877 brands
				for ($i = 1; $i <= 16 * $brands->count(); $i++) {
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
					$barcode = sprintf('2%04d%03d%05d', $category, $brandId, (($i - 1) % 99999) + 1);

					$this->list->push([
						'sku'             => Str::upper(fake()->bothify('SKU-####-????-').$shortId),
						'slug'            => Str::slug($productName).'-'.Str::upper($shortId),
						'barcode'         => $barcode,
						'name'            => $productName,
						'description'     => fake()->paragraph(),
						'category_id'     => $category,
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

				$this->command->getOutput()->write("[Category #".($randomKey + 1)." / ".$categories->count()."] Assigned products in category: #".$category."...\r");
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
						// Laptops & Desktops
						'High-End Laptop', 'Ultra-Slim Ultrabook', 'Pro Workstation Desktop', 'Gaming Laptop Extreme',
						'Compact Mini PC', 'Convertible 2-in-1 Laptop',

						// Smartphones & Mobile Tech
						'Ultra-Slim Smartphone', 'Flagship 5G Smartphone', 'Foldable Smartphone Pro',
						'Rugged Outdoor Smartphone',
						'Smartwatch Series X', 'Fitness Tracker Band',

						// Audio & Visual
						'Noise Cancelling Headphones', 'Wireless Earbuds Pro', '4K Curved Gaming Monitor',
						'OLED Cinema Display',
						'Studio Soundbar Wireless', 'Bluetooth Party Speaker', 'Webcam 4K Ultra HD',

						// Peripherals & Input
						'Mechanical Keyboard RGB', 'Wireless Gaming Mouse', 'Ergonomic Vertical Mouse',
						'Streamer Controller Deck',
						'USB-C Multi-Port Hub', 'HD Graphic Tablet',

						// Gaming Gear
						'Gaming Console Pro', 'Handheld Gaming Console', 'VR Headset System',
						'Wireless Gamepad Controller',
						'Flight Simulator Joystick', 'Racing Wheel Pedals Bundle',

						// Components & Storage
						'External SSD 2TB', 'NVMe M.2 Internal SSD', 'Graphics Card RTX Edition', 'DDR5 Gaming RAM Kit',
						'High-Speed Wi-Fi 6E Router', 'Portable Power Bank 20000mAh',
					],
				],
				'appliances'          => [
					'cats'  => [2, 8], // Home Appliances, Kitchenware
					'names' => [
						// Major Kitchen Appliances
						'Smart Refrigerator', 'French Door Fridge', 'Induction Cooktop', 'Digital Microwave',
						'Dishwasher Silent-Run', 'Built-in Electric Oven', 'Range Hood Extractor',

						// Small Kitchen Appliances
						'Air Fryer XL', 'Espresso Coffee Machine', 'Stand Mixer Pro', 'High-Speed Power Blender',
						'Electric Pressure Cooker', 'Multi-Slice Toaster', 'Electric Water Kettle',
						'Juice Extractor Press',

						// Cleaning & Floor Care
						'Robot Vacuum Cleaner', 'Cordless Stick Vacuum', 'Steam Cleaner Mop', 'Carpet Spot Cleaner',

						// Home Comfort & Climate
						'Smart Inverter Air Conditioner', 'Purifier Air Filter', 'Ultrasonic Room Humidifier',
						'Ceramic Tower Heater', 'Bladeless Oscillating Fan',

						// Laundry & Garment Care
						'Front-Load Washing Machine', 'Tumble Clothes Dryer', 'Electric Steam Iron',
						'Garment Steamer Pro',
					],
				],
				'furniture'           => [
					'cats'  => [6, 9, 14], // Furniture, Lighting, Gardening
					'names' => [
						// Office & Indoor Furniture
						'Ergonomic Office Chair', 'Minimalist Oak Desk', 'Velvet Sofa Bed', 'Ceramic Dining Table',
						'Bookshelf 5-Tier', 'Leather Recliner Armchair', 'Modular Corner Couch', 'Wooden Coffee Table',
						'King Size Storage Bed Frame', 'Spacious Wardrobe Cabinet',

						// Lighting
						'LED Floor Lamp', 'Modern Wall Sconce', 'Industrial Pendant Light', 'Smart Bedside Table Lamp',
						'Crystal Chandelier Light', 'Track Lighting System', 'Dimmable Ceiling Fixture',

						// Gardening & Outdoor Living
						'Garden Lounge Set', 'Outdoor Solar Light', 'Teak Patio Dining Set', 'Rattan Hanging Egg Chair',
						'Heavy-Duty Garden Hose Reel', 'Ergonomic Pruning Shears', 'Automatic Lawn Sprinkler',
						'Terracotta Planter Pot Set', 'Foldable Sun Lounger',
					],
				],
				'fashion'             => [
					'cats'  => [11, 20], // Fashion, Travel & Luggage
					'names' => [
						// Everyday Apparel
						'Premium Cotton Hoodie', 'Slim Fit Denim Jeans', 'Waterproof Windbreaker', 'Wool Blend Coat',
						'Casual Oxford Shirt', 'Classic Leather Jacket', 'Oversized Graphic T-Shirt',
						'Tailored Blazer Jacket',

						// Footwear & Accessories
						'Running Sports Shoes', 'Leather Crossbody Bag', 'Silk Neck Tie', 'Polarized Sunglasses',
						'Minimalist Leather Belt', 'Canvas Tote Bag', 'Suede Ankle Boots',
						'Performance Athletic Shorts',

						// Travel, Luggage & Outdoor Gear
						'Hard-Shell Suitcase', 'Canvas Travel Backpack', 'Expandable Carry-On Trolley',
						'Waterproof Duffle Bag',
						'Leather Weekender Travel Bag', 'Compact Travel Toiletry Kit', 'Anti-Theft RFID Backpack',
					],
				],
				'lifestyle'           => [
					'cats'  => [10, 12, 19], // Sports, Beauty, Health & Wellness
					'names' => [
						// Fitness & Sports Equipment
						'Yoga Mat Anti-Slip', 'Adjustable Dumbbell Set', 'Deep Tissue Massage Gun',
						'Resistance Loop Bands',
						'Pilates Reformer Ring', 'Foam Muscle Roller', 'Insulated Stainless Steel Water Bottle',

						// Beauty & Skincare
						'Hydrating Face Serum', 'Professional Ionic Hair Dryer', 'Anti-Aging Night Cream',
						'Sonic Facial Cleansing Brush', 'SPF 50 Solar Face Sunscreen', 'Nourishing Hair Mask Oil',

						// Personal Care & Hygiene
						'Electric Toothbrush', 'Water Dental Flosser', 'Beard Grooming Trimmer Kit',
						'Hair Straightener Flat Iron', 'Electric Shaver Wet & Dry',

						// Health, Supplements & Wellness
						'Whey Protein Isolate', 'Digital Blood Pressure Monitor', 'Lavender Essential Oil Diffuser',
						'Multivitamin Daily Capsules', 'Pulse Oximeter Finger Monitor', 'Infrared Forehead Thermometer',
					],
				],
				'office_edu'          => [
					'cats'  => [7, 13, 18], // Office Supplies, Books, Toys & Games
					'names' => [
						// Office Supplies & Stationery
						'Refillable Fountain Pen', 'Hardcover Journal', 'A4 Printing Paper 500 Sheets',
						'Desk Organizer Set',
						'Fine Tip Markers', 'Ergonomic Desktop Calculator', 'Heavy-Duty Paper Shredder',
						'Adjustable Laptop Stand',
						'Gel Ink Pen Set 10-Pack', 'Magnetic Whiteboard Board',

						// Books & Literature
						'Bestseller Mystery Novel', 'Hardcover Sci-Fi Anthology', 'Self-Improvement Guide',
						'Illustrated World History',
						'Gourmet Recipe Cookbook', 'Childrens Storybook Treasury', 'Classic Poetry Collection',

						// Toys, Games & Learning
						'Board Game Classic Edition', 'Educational Building Blocks', 'Science Kit for Kids',
						'1000-Piece Jigsaw Puzzle',
						'Remote Control Car 4WD', 'Strategy Card Game Set', 'Wooden Educational Abacus',
						'Interactive Plush Toy',
					],
				],
				'automotive_pet_food' => [
					'cats'  => [15, 16, 17], // Automotive, Pets, Food & Beverages
					'names' => [
						// Automotive & Car Care
						'Synthetic Engine Oil', 'Microfiber Car Cleaning Kit', 'Dashboard Camera 1080p',
						'Portable Tire Inflator',
						'Heavy-Duty Jump Starter', 'All-Weather Rubber Floor Mats', 'OBD2 Diagnostic Scanner',
						'High-Gloss Car Wax',

						// Pet Supplies & Accessories
						'Grain-Free Dog Food', 'Interactive Cat Toy', 'Pet Grooming Brush', 'Automatic Pet Feeder',
						'Ergonomic Dog Harness', 'Orthopedic Pet Bed', 'Clumping Cat Litter', 'Pet Water Fountain',

						// Food & Beverages
						'Organic Ground Coffee', 'Natural Mineral Water', 'Energy Drink Sugar-Free',
						'Extra Virgin Olive Oil',
						'Dark Chocolate Bar 70%', 'Artisanal Green Tea Bags', 'Raw Wildflower Honey',
						'Whole Grain Granola Cereal',
					],
				],
			];
		}

		private function generateSpecifications(string $type): array {
			return match ($type) {
				'tech_heavy'          => [
					'Brand'           => fake()->randomElement([
						'TechCorp', 'Apex', 'Nova', 'Pulse', 'CyberX', 'Vortex',
						'Spectra', 'QuantumTech', 'Zenith', 'AeroByte', 'Hyperion'
					]),
					'Model'           => Str::upper(fake()->bothify('??-###')),
					'Warranty'        => fake()->randomElement([
						'12 Months Limited', '24 Months Full', '36 Months Manufacturer'
					]),
					'Power / Battery' => fake()->randomElement([
						// Mobile / Battery
						'4000 mAh Li-Po', '5000 mAh Fast-Charge', '6500 mAh Long-Life', 'Up to 20 Hours Battery',
						// Desktop / Mains Power
						'Rechargeable Li-Ion', 'USB-C Bus Powered', 'Corded AC (100-240V)', '750W 80+ Gold PSU'
					]),
					'Connectivity'    => fake()->randomElement([
						'Wi-Fi 7, Bluetooth 5.4', 'Wi-Fi 6E, Bluetooth 5.3', 'Bluetooth 5.2, Low Energy',
						'USB-C 4.0, Thunderbolt 4', 'Wireless 2.4GHz + Bluetooth Dual Mode',
						'Gigabit Ethernet, HDMI 2.1'
					]),
				],

				'appliances'          => [
					'Brand'             => fake()->randomElement([
						'HomeLux', 'EcoChef', 'PureAir', 'Titan', 'Aero', 'Omni',
						'ThermaTech', 'VortexHome', 'KlimaPro', 'SavorKitchen', 'NordicFrost'
					]),
					'Energy Rating'     => fake()->randomElement([
						'A+++', 'A++', 'A+', 'A', 'B', 'Class A (EU New Standard)'
					]),
					'Power Consumption' => fake()->numberBetween(300, 3000).'W',
					'Material'          => fake()->randomElement([
						// Metals & Finishes
						'Brushed Stainless Steel', 'Matte Black Metal', 'Fingerprint-Resistant Steel', 'Cast Iron',
						// Modern & Glass
						'Tempered Safety Glass', 'High-Gloss Enamel Coating',
						// Plastics & Ceramics
						'BPA-Free Food-Grade Plastic', 'Heat-Resistant Ceramic', 'Anodized Aluminium'
					]),
					'Warranty'          => fake()->randomElement([
						'1 Year Full Warranty', '2 Years Limited', '3 Years Parts & Labor',
						'5 Years Motor & Compressor', '10 Years Direct Drive Guarantee'
					]),
				],

				'furniture'           => [
					'Brand'             => fake()->randomElement([
						'NordicWood', 'Habitat', 'Moderno', 'CraftedHome', 'UrbanSpace',
						'Kōshō Design', 'Artisan Loft', 'Fireside & Oak', 'Velvet & Steel', 'Lumina Home'
					]),
					'Material'          => fake()->randomElement([
						// Woods
						'Solid Oak', 'Teak Wood', 'Walnut Veneer', 'Reclaimed Pine', 'Solid Ash', 'Mahogany',
						// Metals & Structural
						'Aluminium Frame', 'Powder-Coated Steel', 'Brassed Iron', 'Tempered Glass',
						// Fabrics & Upholstery
						'Velvet Fabric', 'Genuine Leather', 'Bouclé Upholstery', 'Linen Blend', 'Microfiber Suede',
						// Modern & Outdoor
						'Rattan & Cane', 'High-Density Polyethylene', 'Cast Concrete'
					]),
					'Dimensions'        => fake()->numberBetween(50, 200).'x'.fake()->numberBetween(40, 100).'x'.fake()->numberBetween(40, 120).' cm',
					'Color'             => fake()->randomElement([
						// Neutrals & Woods
						'Natural Oak', 'Warm Beige', 'Charcoal Gray', 'Matte Black', 'Cream White', 'Walnut Brown',
						// Earthy & Deep Tones
						'Emerald Green', 'Navy Blue', 'Terracotta', 'Mustard Yellow', 'Forest Green', 'Rust Orange',
						// Metallic & Accents
						'Brushed Brass', 'Gunmetal Metal', 'Smoky Quartz', 'Olive Drab'
					]),
					'Assembly Required' => fake()->randomElement([
						'Yes (Tools Included)', 'No (Pre-assembled)', 'Partial'
					]),
				],

				'fashion'             => [
					'Brand'             => fake()->randomElement([
						'Vanguard', 'Aura Apparel', 'UrbanKnit', 'TrailGear', 'Velour'
					]),
					'Size'              => fake()->randomElement(['S', 'M', 'L', 'XL', 'One Size', 'Carry-On 55cm']),
					'Material'          => fake()->randomElement([
						'100% Organic Cotton', 'Genuine Leather', 'Waterproof Nylon', 'Wool Blend'
					]),
					'Care Instructions' => fake()->randomElement([
						'Machine Wash 30°C', 'Hand Wash Only', 'Dry Clean Only', 'Wipe Clean'
					]),
					'Gender / Style'    => fake()->randomElement(['Unisex', 'Men\'s', 'Women\'s']),
				],

				'lifestyle'           => [
					'Brand'              => fake()->randomElement([
						'Vitality', 'FlexSport', 'GlowSkin', 'ZenLife', 'NutriPure'
					]),
					'Volume / Weight'    => fake()->randomElement(['50ml', '100ml', '1kg', '2kg', 'Standard']),
					'Ingredients / Type' => fake()->randomElement([
						'100% Natural', 'Hypoallergenic', 'BPA-Free Silicone', 'Whey Concentrate'
					]),
					'Expiration Date'    => fake()->dateTimeBetween('+1 year', '+3 years')->format('M Y'),
					'Country of Origin'  => fake()->randomElement(['Germany', 'USA', 'Japan', 'France', 'Greece']),
				],

				'office_edu'          => [
					'Brand'              => fake()->randomElement([
						'PaperCraft', 'SmartOffice', 'EduPlay', 'MasterMind', 'Lumina'
					]),
					'Age Group'          => fake()->randomElement(['All Ages', '6+ Years', '3+ Years', 'Adults']),
					'Format / Type'      => fake()->randomElement([
						'Hardcover', 'Pack of 10', '500 Sheets', 'Boxed Set'
					]),
					'Paper Weight / Mat' => fake()->randomElement([
						'80 gsm', 'Recycled Cardboard', 'Non-Toxic Plastic', 'Fine Metal'
					]),
					'Language / Edition' => fake()->randomElement(['English', 'Multilingual', 'Standard Edition']),
				],

				'automotive_pet_food' => [
					'Brand'              => fake()->randomElement([
						'DrivePro', 'PetFeast', 'AutoCare', 'WildPaws', 'PureBrew'
					]),
					'Net Quantity'       => fake()->randomElement(['1L', '5L', '2.5kg', '10kg', '500g', 'Pack of 12']),
					'Formulation / Type' => fake()->randomElement([
						'Fully Synthetic', 'Grain-Free', 'Organic', 'Heavy-Duty'
					]),
					'Shelf Life'         => fake()->randomElement(['12 Months', '24 Months', 'Best Before 18 Months']),
					'Safety Certificate' => fake()->randomElement([
						'ISO 9001 Certified', 'Vet Approved', 'CE Certified'
					]),
				],

				default               => [
					'Brand' => fake()->company(),
					'Model' => Str::upper(fake()->bothify('??-###')),
				],
			};
		}
	}
