<?php

	namespace Database\Seeders\products;

	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\DB;

	class BrandSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$brands = [
				// 1. Electronics
				[
					'name'        => 'Sony',
					'slug'        => 'sony',
					'description' => 'Global leader in electronics and entertainment systems.',
					'logo'        => 'sony_logo.png',
					'website'     => 'https://www.sony.com/',
					'is_active'   => true,
					'category_id' => 1,
				],
				[
					'name'        => 'Samsung',
					'slug'        => 'samsung',
					'description' => 'Electronics, home appliances, and smartphone technology.',
					'logo'        => 'samsung_logo.png',
					'website'     => 'https://www.samsung.com',
					'is_active'   => true,
					'category_id' => 1,
				],
				// 2. Home Appliances
				[
					'name'        => 'Bosch',
					'slug'        => 'bosch',
					'description' => 'German multinational engineering and technology company specializing in appliances.',
					'logo'        => 'bosch_logo.png',
					'website'     => 'https://www.bosch.com/',
					'is_active'   => true,
					'category_id' => 2,
				],
				[
					'name'        => 'Whirlpool',
					'slug'        => 'whirlpool',
					'description' => 'American multinational manufacturer of home appliances.',
					'logo'        => 'whirlpool_logo.png',
					'website'     => 'https://www.whirlpool.com/',
					'is_active'   => true,
					'category_id' => 2,
				],
				// 3. Computers & Laptops
				[
					'name'        => 'Dell',
					'slug'        => 'dell',
					'description' => 'Computers, servers, and technology solutions.',
					'logo'        => 'dell_logo.png',
					'website'     => 'https://www.dell.com',
					'is_active'   => true,
					'category_id' => 3,
				],
				[
					'name'        => 'HP',
					'slug'        => 'hp',
					'description' => 'Leading manufacturer of personal computers and printers.',
					'logo'        => 'hp_logo.png',
					'website'     => 'https://www.hp.com',
					'is_active'   => true,
					'category_id' => 3,
				],
				[
					'name'        => 'Lenovo',
					'slug'        => 'lenovo',
					'description' => 'Personal computers, laptops, and electronics.',
					'logo'        => null,
					'website'     => 'https://www.lenovo.com',
					'is_active'   => true,
					'category_id' => 3,
				],
				[
					'name'        => 'Apple',
					'slug'        => 'apple',
					'description' => 'Premium computers and laptops (MacBook, iMac, Mac mini, Mac Studio).',
					'logo'        => 'apple_logo.png',
					'website'     => 'https://www.apple.com',
					'is_active'   => true,
					'category_id' => 3,
				],
				// 4. Smartphones
				[
					'name'        => 'Google',
					'slug'        => 'google',
					'description' => 'Manufacturer of the Pixel line of smartphones.',
					'logo'        => 'google_logo.png',
					'website'     => 'https://store.google.com/',
					'is_active'   => true,
					'category_id' => 4,
				],
				// 5. Gaming
				[
					'name'        => 'Nintendo',
					'slug'        => 'nintendo',
					'description' => 'Japanese multinational consumer electronics and video game company.',
					'logo'        => 'nintendo_logo.png',
					'website'     => 'https://www.nintendo.com/',
					'is_active'   => true,
					'category_id' => 5,
				],
				[
					'name'        => 'Razer',
					'slug'        => 'razer',
					'description' => 'Global lifestyle brand for gamers, offering peripherals and hardware.',
					'logo'        => 'razer_logo.png',
					'website'     => 'https://www.razer.com/',
					'is_active'   => true,
					'category_id' => 5,
				],
				[
					'name'        => 'Custom Gaming PCs',
					'slug'        => 'custom-gaming-pcs',
					'description' => 'Custom-built high-performance gaming desktops tailored to specifications.',
					'logo'        => null,
					'website'     => null,
					'is_active'   => true,
					'category_id' => 5,
				],
				[
					'name'        => 'Prebuilt Gaming PCs',
					'slug'        => 'prebuilt-gaming-pcs',
					'description' => 'Factory-assembled gaming desktops ready to use.',
					'logo'        => null,
					'website'     => null,
					'is_active'   => true,
					'category_id' => 5,
				],
				[
					'name'        => 'Budget Gaming PCs',
					'slug'        => 'budget-gaming-pcs',
					'description' => 'Affordable gaming PCs for casual gamers.',
					'logo'        => null,
					'website'     => null,
					'is_active'   => true,
					'category_id' => 5,
				],
				// 6. Furniture
				[
					'name'        => 'IKEA',
					'slug'        => 'ikea',
					'description' => 'Swedish multinational furniture retailer known for flat-pack furniture.',
					'logo'        => 'ikea_logo.png',
					'website'     => 'https://www.ikea.com/',
					'is_active'   => true,
					'category_id' => 6,
				],
				[
					'name'        => 'Herman Miller',
					'slug'        => 'herman-miller',
					'description' => 'Premium brand specializing in ergonomic office chairs and modern furniture.',
					'logo'        => 'herman_miller_logo.png',
					'website'     => 'https://www.hermanmiller.com/',
					'is_active'   => true,
					'category_id' => 6,
				],
				// 7. Office Supplies
				[
					'name'        => '3M',
					'slug'        => '3m',
					'description' => 'Known for Post-it Notes, Scotch tape, and various adhesives and office materials.',
					'logo'        => '3m_logo.png',
					'website'     => 'https://www.3m.com/',
					'is_active'   => true,
					'category_id' => 7,
				],
				[
					'name'        => 'Pilot',
					'slug'        => 'pilot',
					'description' => 'Japanese manufacturer of high-quality writing instruments and pens.',
					'logo'        => 'pilot_logo.png',
					'website'     => 'https://pilotpen.us/',
					'is_active'   => true,
					'category_id' => 7,
				],

				// 8. Kitchenware
				[
					'name'        => 'KitchenAid',
					'slug'        => 'kitchenaid',
					'description' => 'Iconic brand known for stand mixers and premium kitchen appliances.',
					'logo'        => 'kitchenaid_logo.png',
					'website'     => 'https://www.kitchenaid.com/',
					'is_active'   => true,
					'category_id' => 8,
				],
				[
					'name'        => 'Cuisinart',
					'slug'        => 'cuisinart',
					'description' => 'Specializes in food processors and high-quality culinary tools.',
					'logo'        => 'cuisinart_logo.png',
					'website'     => 'https://www.cuisinart.com/',
					'is_active'   => true,
					'category_id' => 8,
				],

				// 9. Lighting
				[
					'name'        => 'Philips Hue',
					'slug'        => 'philips-hue',
					'description' => 'Market leader in smart lighting and connected LED systems.',
					'logo'        => 'philips_logo.png',
					'website'     => 'https://www.philips-hue.com/',
					'is_active'   => true,
					'category_id' => 9,
				],
				[
					'name'        => 'Ikea Lighting',
					'slug'        => 'ikea-lighting',
					'description' => 'Affordable and functional home lighting fixtures and bulbs.',
					'logo'        => null,
					'website'     => 'https://www.ikea.com/us/en/cat/lighting-10702/',
					'is_active'   => true,
					'category_id' => 9,
				],

				// 10. Sports & Outdoors
				[
					'name'        => 'Nike',
					'slug'        => 'nike',
					'description' => 'World-renowned brand for athletic footwear, apparel, and equipment.',
					'logo'        => 'nike_logo.png',
					'website'     => 'https://www.nike.com',
					'is_active'   => true,
					'category_id' => 10,
				],
				[
					'name'        => 'The North Face',
					'slug'        => 'the-north-face',
					'description' => 'Specializes in outerwear, fleece, and camping equipment.',
					'logo'        => 'tnf_logo.png',
					'website'     => 'https://www.thenorthface.com/',
					'is_active'   => true,
					'category_id' => 10,
				],
				[
					'name'        => 'Adidas',
					'slug'        => 'adidas',
					'description' => 'Sports clothing, athletic apparel, and accessories.',
					'logo'        => null,
					'website'     => 'https://www.adidas.com',
					'is_active'   => true,
					'category_id' => 10,
				],
				// 11. Fashion & Clothing
				[
					'name'        => 'Levi\'s',
					'slug'        => 'levis',
					'description' => 'American clothing company known globally for its denim jeans.',
					'logo'        => 'levis_logo.png',
					'website'     => 'https://www.levi.com/',
					'is_active'   => true,
					'category_id' => 11,
				],
				[
					'name'        => 'Zara',
					'slug'        => 'zara',
					'description' => 'Fast fashion and lifestyle brand offering trendy clothing for men and women.',
					'logo'        => 'zara_logo.png',
					'website'     => 'https://www.zara.com',
					'is_active'   => true,
					'category_id' => 11,
				],
				[
					'name'        => 'H&M',
					'slug'        => 'hm',
					'description' => 'Fashion and quality clothing for men, women, and children.',
					'logo'        => null,
					'website'     => 'https://www.hm.com',
					'is_active'   => true,
					'category_id' => 11,
				],

				// 12. Beauty & Personal Care
				[
					'name'        => 'L\'Oréal',
					'slug'        => 'loreal',
					'description' => 'French personal care company focusing on hair, skin, and makeup.',
					'logo'        => 'loreal_logo.png',
					'website'     => 'https://www.loreal.com/',
					'is_active'   => true,
					'category_id' => 12,
				],
				[
					'name'        => 'Dove',
					'slug'        => 'dove',
					'description' => 'Brand of personal care products owned by Unilever.',
					'logo'        => 'dove_logo.png',
					'website'     => 'https://www.dove.com/',
					'is_active'   => true,
					'category_id' => 12,
				],

				// 13. Books & Stationery
				[
					'name'        => 'Moleskine',
					'slug'        => 'moleskine',
					'description' => 'Manufacturer of high-quality notebooks, planners, and diaries.',
					'logo'        => 'moleskine_logo.png',
					'website'     => 'https://www.moleskine.com/',
					'is_active'   => true,
					'category_id' => 13,
				],
				[
					'name'        => 'Penguin Random House',
					'slug'        => 'penguin-random-house',
					'description' => 'One of the world\'s largest book publishers, covering all genres.',
					'logo'        => 'penguin_logo.png',
					'website'     => 'https://global.penguinrandomhouse.com/',
					'is_active'   => true,
					'category_id' => 13,
				],

				// 14. Gardening
				[
					'name'        => 'Fiskars',
					'slug'        => 'fiskars',
					'description' => 'Finnish company specializing in gardening tools, scissors, and cutting implements.',
					'logo'        => 'fiskars_logo.png',
					'website'     => 'https://www.fiskars.com/',
					'is_active'   => true,
					'category_id' => 14,
				],
				[
					'name'        => 'Scotts Miracle-Gro',
					'slug'        => 'scotts-miracle-gro',
					'description' => 'Provider of lawn, garden, and plant care products.',
					'logo'        => 'scotts_logo.png',
					'website'     => 'https://www.scotts.com/',
					'is_active'   => true,
					'category_id' => 14,
				],

				// 15. Automotive
				[
					'name'        => 'Castrol',
					'slug'        => 'castrol',
					'description' => 'Brand of industrial and automotive lubricants.',
					'logo'        => 'castrol_logo.png',
					'website'     => 'https://www.castrol.com/',
					'is_active'   => true,
					'category_id' => 15,
				],
				[
					'name'        => 'Michelin',
					'slug'        => 'michelin',
					'description' => 'Leading global tire manufacturer.',
					'logo'        => 'michelin_logo.png',
					'website'     => 'https://www.michelin.com/',
					'is_active'   => true,
					'category_id' => 15,
				],
				// 16. Pets
				[
					'name'        => 'Purina',
					'slug'        => 'purina',
					'description' => 'Manufacturer of dog and cat food and pet products.',
					'logo'        => 'purina_logo.png',
					'website'     => 'https://www.purina.com/',
					'is_active'   => true,
					'category_id' => 16,
				],
				[
					'name'        => 'Blue Buffalo',
					'slug'        => 'blue-buffalo',
					'description' => 'Brand focused on natural and healthy pet foods.',
					'logo'        => 'blue_buffalo_logo.png',
					'website'     => 'https://bluebuffalo.com/',
					'is_active'   => true,
					'category_id' => 16,
				],

				// 17. Food & Beverages
				[
					'name'        => 'Coca-Cola',
					'slug'        => 'coca-cola',
					'description' => 'Iconic global beverage and soft drink company.',
					'logo'        => 'coca_cola_logo.png',
					'website'     => 'https://www.coca-cola.com',
					'is_active'   => true,
					'category_id' => 17,
				],
				[
					'name'        => 'Nestlé',
					'slug'        => 'nestle',
					'description' => 'Global food and beverage company offering a wide range of products.',
					'logo'        => 'nestle_logo.png',
					'website'     => 'https://www.nestle.com/',
					'is_active'   => true,
					'category_id' => 17,
				],
				[
					'name'        => 'Pepsi',
					'slug'        => 'pepsi',
					'description' => 'Beverages, soft drinks, and snacks.',
					'logo'        => null,
					'website'     => 'https://www.pepsi.com/',
					'is_active'   => true,
					'category_id' => 17,
				],

				// 18. Toys & Games
				[
					'name'        => 'Lego',
					'slug'        => 'lego',
					'description' => 'Danish toy production company known for building block toys.',
					'logo'        => 'lego_logo.png',
					'website'     => 'https://www.lego.com/',
					'is_active'   => true,
					'category_id' => 18,
				],
				[
					'name'        => 'Hasbro',
					'slug'        => 'hasbro',
					'description' => 'American multinational toy and board game company (e.g., Monopoly, Nerf).',
					'logo'        => 'hasbro_logo.png',
					'website'     => 'https://shop.hasbro.com/',
					'is_active'   => true,
					'category_id' => 18,
				],

				// 19. Health & Wellness
				[
					'name'        => 'GNC',
					'slug'        => 'gnc',
					'description' => 'Specializes in health and nutrition related products, including vitamins and supplements.',
					'logo'        => 'gnc_logo.png',
					'website'     => 'https://www.gnc.com/',
					'is_active'   => true,
					'category_id' => 19,
				],
				[
					'name'        => 'Fitbit',
					'slug'        => 'fitbit',
					'description' => 'Known for wearable technology, fitness trackers, and smartwatches.',
					'logo'        => 'fitbit_logo.png',
					'website'     => 'https://www.fitbit.com/',
					'is_active'   => true,
					'category_id' => 19,
				],

				// 20. Travel & Luggage
				[
					'name'        => 'Samsonite',
					'slug'        => 'samsonite',
					'description' => 'Global leader in luggage and travel accessories.',
					'logo'        => 'samsonite_logo.png',
					'website'     => 'https://www.samsonite.com/',
					'is_active'   => true,
					'category_id' => 20,
				],
				[
					'name'        => 'Tumi',
					'slug'        => 'tumi',
					'description' => 'Premium travel lifestyle brand known for high-end luggage and bags.',
					'logo'        => 'tumi_logo.png',
					'website'     => 'https://www.tumi.com/',
					'is_active'   => true,
					'category_id' => 20,
				],

				// Generic / Uncategorized Brand
				[
					'name'        => 'Generic Brand',
					'slug'        => 'generic',
					'description' => 'Products that are unbranded or generic.',
					'logo'        => null,
					'website'     => null,
					'is_active'   => true,
					'category_id' => null,
				],
			];

			foreach ($brands as $brand) {
				DB::table('brands')->insert($brand + [
						'created_at' => Carbon::now()->subHours(mt_rand(1, 23))->subMinutes(mt_rand(1, 59))->subSeconds(mt_rand(1, 59))->timezone(config('app.timezone'))->toDateTimeString(),
						'updated_at' => Carbon::now()->addHours(mt_rand(1, 23))->addMinutes(mt_rand(1, 59))->addSeconds(mt_rand(1, 59))->timezone(config('app.timezone'))->toDateTimeString()
					]);
			}
		}
	}
