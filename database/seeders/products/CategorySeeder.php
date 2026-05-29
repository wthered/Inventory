<?php

	namespace Database\Seeders\products;

	use Carbon\Carbon;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\DB;

	class CategorySeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Parent categories
			$categories = [
				[
					'name'        => 'Electronics',
					'slug'        => 'electronics',
					'parent_id'   => null,
					'description' => 'Electronic devices, gadgets, and accessories',
					'sort_order'  => 1
				],
				[
					'name'        => 'Home Appliances',
					'slug'        => 'home-appliances',
					'parent_id'   => null,
					'description' => 'Appliances and devices for household use',
					'sort_order'  => 2
				],
				[
					'name'        => 'Computers & Laptops',
					'slug'        => 'computers-laptops',
					'parent_id'   => null,
					'description' => 'Desktops, laptops, and computer peripherals',
					'sort_order'  => 3
				],
				[
					'name'        => 'Smartphones',
					'slug'        => 'smartphones',
					'parent_id'   => null,
					'description' => 'Mobile phones and related accessories',
					'sort_order'  => 4
				],
				[
					'name'        => 'Gaming',
					'slug'        => 'gaming',
					'parent_id'   => null,
					'description' => 'Gaming consoles, accessories, and PC games',
					'sort_order'  => 5
				],
				[
					'name'        => 'Furniture',
					'slug'        => 'furniture',
					'parent_id'   => null,
					'description' => 'Home and office furniture items',
					'sort_order'  => 6
				],
				[
					'name'        => 'Office Supplies',
					'slug'        => 'office-supplies',
					'parent_id'   => null,
					'description' => 'Stationery, printers, and office essentials',
					'sort_order'  => 7
				],
				[
					'name'        => 'Kitchenware',
					'slug'        => 'kitchenware',
					'parent_id'   => null,
					'description' => 'Cooking tools, utensils, and appliances',
					'sort_order'  => 8
				],
				[
					'name'        => 'Lighting',
					'slug'        => 'lighting',
					'parent_id'   => null,
					'description' => 'Indoor and outdoor lighting solutions',
					'sort_order'  => 9
				],
				[
					'name'        => 'Sports & Outdoors',
					'slug'        => 'sports-outdoors',
					'parent_id'   => null,
					'description' => 'Fitness, outdoor gear, and sports equipment',
					'sort_order'  => 10
				],
				[
					'name'        => 'Fashion & Clothing',
					'slug'        => 'fashion-clothing',
					'parent_id'   => null,
					'description' => 'Men’s, women’s, and kids’ apparel and accessories',
					'sort_order'  => 11
				],
				[
					'name'        => 'Beauty & Personal Care',
					'slug'        => 'beauty-personal-care',
					'parent_id'   => null,
					'description' => 'Cosmetics, skincare, and hygiene products',
					'sort_order'  => 12
				],
				[
					'name'        => 'Books & Stationery',
					'slug'        => 'books-stationery',
					'parent_id'   => null,
					'description' => 'Books, notebooks, and educational materials',
					'sort_order'  => 13
				],
				[
					'name'        => 'Gardening',
					'slug'        => 'gardening',
					'parent_id'   => null,
					'description' => 'Plants, tools, outdoor furniture, and garden accessories',
					'sort_order'  => 14
				],
				[
					'name'        => 'Automotive',
					'slug'        => 'automotive',
					'parent_id'   => null,
					'description' => 'Car parts, accessories, and maintenance items',
					'sort_order'  => 15
				],
				[
					'name'        => 'Pets',
					'slug'        => 'pets',
					'parent_id'   => null,
					'description' => 'Pet food, accessories, and care products',
					'sort_order'  => 16
				],
				[
					'name'        => 'Food & Beverages',
					'slug'        => 'food-beverages',
					'parent_id'   => null,
					'description' => 'Groceries, snacks, and drinks',
					'sort_order'  => 17
				],
				[
					'name'        => 'Toys & Games',
					'slug'        => 'toys-games',
					'parent_id'   => null,
					'description' => 'Toys, games, and educational play items',
					'sort_order'  => 18
				],
				[
					'name'        => 'Health & Wellness',
					'slug'        => 'health-wellness',
					'parent_id'   => null,
					'description' => 'Vitamins, supplements, and personal care products',
					'sort_order'  => 19
				],
				[
					'name'        => 'Travel & Luggage',
					'slug'        => 'travel-luggage',
					'parent_id'   => null,
					'description' => 'Suitcases, bags, travel accessories',
					'sort_order'  => 20
				],
			];

			foreach ($categories as $category) {
				DB::table('categories')->insert($category + [
						'id'         => $category['sort_order'],
						'is_active'  => true,
						'created_at' => Carbon::now()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => Carbon::now()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59))
					]);
			}

			$childCategories = [
				// 1. Electronics
				['name'=>'Laptops','slug'=>'laptops','parent_id'=>1,'description'=>'All types of laptops and notebooks','sort_order'=>1],
				['name'=>'Tablets','slug'=>'tablets','parent_id'=>1,'description'=>'Tablet devices and accessories','sort_order'=>2],
				['name'=>'Desktops','slug'=>'desktops','parent_id'=>1,'description'=>'Desktop computers and workstations','sort_order'=>3],
				['name'=>'Refurbished Monitors','slug'=>'Refurbished-monitors','parent_id'=>1,'description'=>'Computer monitors and screens','sort_order'=>4],
				['name'=>'Cameras','slug'=>'cameras','parent_id'=>1,'description'=>'Digital cameras, DSLRs, and accessories','sort_order'=>5],
				['name'=>'Audio Equipment','slug'=>'audio-equipment','parent_id'=>1,'description'=>'Speakers, headphones, and sound systems','sort_order'=>6],
				['name'=>'Wearables','slug'=>'wearables','parent_id'=>1,'description'=>'Smartwatches, fitness trackers, and wearable tech','sort_order'=>7],
				['name'=>'Gaming Consoles','slug'=>'gaming-consoles','parent_id'=>1,'description'=>'Video game consoles and accessories','sort_order'=>8],
				['name'=>'Smart Home','slug'=>'smart-home','parent_id'=>1,'description'=>'Smart home devices and automation','sort_order'=>9],
				['name'=>'Networking','slug'=>'networking','parent_id'=>1,'description'=>'Routers, modems, and networking equipment','sort_order'=>10],
				['name'=>'Storage Devices','slug'=>'storage-devices','parent_id'=>1,'description'=>'External drives, SSDs, and memory cards','sort_order'=>11],
				['name'=>'Printers & Scanners','slug'=>'printers-scanners','parent_id'=>1,'description'=>'Printers, scanners, all-in-one devices and accessories','sort_order'=>12],
				['name'=>'Projectors & Accessories','slug'=>'projectors-accessories','parent_id'=>1,'description'=>'Projectors, screens, and related accessories','sort_order'=>13],
				['name'=>'Cables & Adapters','slug'=>'cables-adapters','parent_id'=>1,'description'=>'HDMI, USB, charging cables, and adapters','sort_order'=>14],
				['name'=>'Computer Components','slug'=>'computer-components','parent_id'=>1,'description'=>'GPUs, CPUs, RAM, motherboards, and PC components','sort_order'=>15],
				['name'=>'Drones & Robotics','slug'=>'drones-robotics','parent_id'=>1,'description'=>'Consumer drones, robotics kits, and accessories','sort_order'=>16],
				['name'=>'TVs & Home Entertainment','slug'=>'tvs-home-entertainment','parent_id'=>1,'description'=>'Televisions, soundbars, media players, and home theater','sort_order'=>17],

				// 2. Home Appliances
				['name'=>'Refrigerators','slug'=>'refrigerators','parent_id'=>2,'description'=>'Fridges, freezers, and combos','sort_order'=>1],
				['name'=>'Washing Machines','slug'=>'washing-machines','parent_id'=>2,'description'=>'Front-load, top-load, and portable washing machines','sort_order'=>2],
				['name'=>'Dryers','slug'=>'dryers','parent_id'=>2,'description'=>'Clothes dryers and drying solutions','sort_order'=>3],
				['name'=>'Microwave Ovens','slug'=>'microwave-ovens','parent_id'=>2,'description'=>'Microwaves and convection ovens','sort_order'=>4],
				['name'=>'Dishwashers','slug'=>'dishwashers','parent_id'=>2,'description'=>'Automatic dishwashing machines','sort_order'=>5],
				['name'=>'Vacuum Cleaners','slug'=>'vacuum-cleaners','parent_id'=>2,'description'=>'Vacuum cleaners and carpet cleaners','sort_order'=>6],
				['name'=>'Air Conditioners','slug'=>'air-conditioners','parent_id'=>2,'description'=>'Cooling and heating AC units','sort_order'=>7],
				['name'=>'Heaters','slug'=>'heaters','parent_id'=>2,'description'=>'Room heaters and portable heating devices','sort_order'=>8],
				['name'=>'Coffee Machines','slug'=>'coffee-machines','parent_id'=>2,'description'=>'Espresso machines, drip coffee makers','sort_order'=>9],
				['name'=>'Blenders & Mixers','slug'=>'blenders-mixers','parent_id'=>2,'description'=>'Food processors, blenders, and mixers','sort_order'=>10],
				['name'=>'Irons & Steamers','slug'=>'irons-steamers','parent_id'=>2,'description'=>'Clothing irons and garment steamers','sort_order'=>11],

				// 3. Computers & Laptops
				['name'=>'High-Performance Laptops','slug'=>'high-performance-laptops','parent_id'=>3,'description'=>'Laptops optimized for gaming, content creation, and heavy workloads','sort_order'=>1],
				['name'=>'Business Laptops','slug'=>'business-laptops','parent_id'=>3,'description'=>'Laptops designed for office productivity and professional use','sort_order'=>2],
				['name'=>'Student Laptops','slug'=>'student-laptops','parent_id'=>3,'description'=>'Affordable and portable laptops for students','sort_order'=>3],
				['name'=>'Ultrabooks','slug'=>'ultrabooks','parent_id'=>3,'description'=>'Slim, lightweight laptops with long battery life','sort_order'=>4],
				['name'=>'2-in-1 Laptops / Convertibles','slug'=>'2-in-1-laptops','parent_id'=>3,'description'=>'Laptops with touchscreens that convert into tablets','sort_order'=>5],
				['name'=>'MacBooks','slug'=>'macbooks','parent_id'=>3,'description'=>'Apple MacBook Air and MacBook Pro models','sort_order'=>6],
				['name'=>'Refurbished Laptops','slug'=>'refurbished-laptops','parent_id'=>3,'description'=>'Certified pre-owned and refurbished laptops','sort_order'=>7],
				['name'=>'All-in-One PC','slug'=>'all-in-one-pc','parent_id'=>3,'description'=>'All-in-one desktop computers','sort_order'=>8],
				['name'=>'iMac','slug'=>'imac','parent_id'=>3,'description'=>'Apple iMac desktops','sort_order'=>9],
				['name'=>'Mac mini / Mac Studio','slug'=>'mac-mini-studio','parent_id'=>3,'description'=>'Apple Mac mini and Mac Studio desktops','sort_order'=>10],
				['name'=>'Refurbished Desktops','slug'=>'refurbished-desktops','parent_id'=>3,'description'=>'Certified refurbished desktop computers','sort_order'=>11],
				['name'=>'Gaming PCs','slug'=>'gaming-pcs','parent_id'=>3,'description'=>'High-performance desktops for gaming','sort_order'=>12],
				['name'=>'Gaming Workstations','slug'=>'gaming-workstations','parent_id'=>3,'description'=>'Extremely high-end desktops optimized for competitive gaming, streaming, and content creation.','sort_order'=>13],
				['name'=>'Mid-Range Gaming PCs','slug'=>'mid-range-gaming-pcs','parent_id'=>3,'description'=>'Performance-focused gaming desktops offering the best value for mid-level budgets.','sort_order'=>14],
				['name'=>'Mini Gaming PCs','slug'=>'mini-gaming-pcs','parent_id'=>3,'description'=>'Compact gaming PCs with small form factor','sort_order'=>15],
				['name'=>'VR-Ready PCs','slug'=>'vr-ready-pcs','parent_id'=>3,'description'=>'Gaming PCs optimized for virtual reality experiences','sort_order'=>16],
				['name'=>'Budget Gaming PCs','slug'=>'budget-gaming-pcs','parent_id'=>3,'description'=>'Affordable gaming PCs for casual gamers','sort_order'=>17],
				['name'=>'Monitors','slug'=>'monitors','parent_id'=>3,'description'=>'Computer monitors and screens','sort_order'=>18],
				['name'=>'Screens','slug'=>'screens','parent_id'=>3,'description'=>'Additional screens and accessories','sort_order'=>19],
				['name'=>'Keyboards','slug'=>'keyboards','parent_id'=>3,'description'=>'Mechanical and membrane keyboards','sort_order'=>20],
				['name'=>'Mice','slug'=>'mice','parent_id'=>3,'description'=>'Gaming and office mice','sort_order'=>21],
				['name'=>'Mouse-Keyboard Sets','slug'=>'mouse-keyboard-sets','parent_id'=>3,'description'=>'Bundled mouse and keyboard sets','sort_order'=>22],
				['name'=>'Headsets','slug'=>'headsets','parent_id'=>3,'description'=>'Gaming and office headphones with microphone','sort_order'=>23],
				['name'=>'Web Cameras','slug'=>'web-cameras','parent_id'=>3,'description'=>'Webcams for streaming, meetings, and recording','sort_order'=>24],
				['name'=>'Speakers','slug'=>'speakers','parent_id'=>3,'description'=>'Desktop speakers and multimedia sound systems','sort_order'=>25],
				['name'=>'Subwoofers','slug'=>'subwoofers','parent_id'=>3,'description'=>'Subwoofers for enhanced bass in sound systems','sort_order'=>26],
				['name'=>'Mouse Pads','slug'=>'mouse-pads','parent_id'=>3,'description'=>'Gaming and office mouse pads','sort_order'=>27],
				['name'=>'USB Hubs','slug'=>'usb-hubs','parent_id'=>3,'description'=>'USB expansion hubs and adapters','sort_order'=>28],
				['name'=>'Cleaning','slug'=>'cleaning','parent_id'=>3,'description'=>'Cleaning kits and tools for computers','sort_order'=>29],
				['name'=>'Digitizers','slug'=>'digitizers','parent_id'=>3,'description'=>'Graphics tablets and pen input devices','sort_order'=>30],
				['name'=>'UPS','slug'=>'ups','parent_id'=>3,'description'=>'Uninterruptible power supplies for desktops and workstations','sort_order'=>31],
				['name'=>'Batteries','slug'=>'batteries','parent_id'=>3,'description'=>'Laptop and peripheral batteries','sort_order'=>32],
				['name'=>'Monitor Cables & Adapters','slug'=>'monitor-cables-adapters','parent_id'=>3,'description'=>'HDMI, USB, charging cables, and adapters','sort_order'=>33],
				['name'=>'PC Components','slug'=>'pc-components','parent_id'=>3,'description'=>'Motherboards, GPUs, RAM, and other components','sort_order'=>34],
				['name'=>'USB Sticks / Storage','slug'=>'usb-sticks-storage','parent_id'=>3,'description'=>'SSDs, HDDs, external drives, and memory cards','sort_order'=>35],
				['name'=>'Laptop Accessories','slug'=>'laptop-accessories','parent_id'=>3,'description'=>'Laptop bags, cooling pads, and docks','sort_order'=>36],
				['name'=>'Software','slug'=>'software','parent_id'=>3,'description'=>'Operating systems, productivity, and security software','sort_order'=>37],
				['name'=>'Routers','slug'=>'routers','parent_id'=>3,'description'=>'Routers for home and office networks','sort_order'=>38],
				['name'=>'DSL Modem / Routers','slug'=>'dsl-modem-routers','parent_id'=>3,'description'=>'DSL modems combined with routers for home and office','sort_order'=>39],
				['name'=>'Access Points / Wi-Fi Extenders','slug'=>'access-points-wifi-extenders','parent_id'=>3,'description'=>'Wireless access points and Wi-Fi range extenders','sort_order'=>40],
				['name'=>'Mesh WiFi Systems','slug'=>'mesh-wifi-systems','parent_id'=>3,'description'=>'Mesh WiFi systems for whole-home coverage','sort_order'=>41],
				['name'=>'Powerline Adapters','slug'=>'powerline-adapters','parent_id'=>3,'description'=>'Powerline adapters for wired networking via electrical circuits','sort_order'=>42],
				['name'=>'Switches','slug'=>'switches','parent_id'=>3,'description'=>'Network switches for connecting multiple devices','sort_order'=>43],
				['name'=>'Network Cables','slug'=>'network-cables','parent_id'=>3,'description'=>'Ethernet and networking cables','sort_order'=>44],
				['name'=>'USB Network Cards','slug'=>'usb-network-cards','parent_id'=>3,'description'=>'USB network adapters for wired or wireless connections','sort_order'=>45],
				['name'=>'File Servers / NAS','slug'=>'file-servers-nas','parent_id'=>3,'description'=>'Network-attached storage and file servers','sort_order'=>46],
				['name'=>'4G - 5G Modems','slug'=>'4g-5g-modems','parent_id'=>3,'description'=>'Cellular modems for mobile internet','sort_order'=>47],
				['name'=>'IP Cameras','slug'=>'ip-cameras','parent_id'=>3,'description'=>'Network cameras for surveillance and monitoring','sort_order'=>48],
				['name'=>'Network Sensors','slug'=>'network-sensors','parent_id'=>3,'description'=>'Sensors that communicate over the network','sort_order'=>49],
				['name'=>'Smart Lights','slug'=>'smart-lights','parent_id'=>3,'description'=>'Smart lighting systems controlled via network','sort_order'=>50],
				['name'=>'Smart Plugs','slug'=>'smart-plugs','parent_id'=>3,'description'=>'Network-connected smart plugs and power outlets','sort_order'=>51],
				['name'=>'Smart Speakers','slug'=>'smart-speakers','parent_id'=>3,'description'=>'Voice-controlled networked speakers','sort_order'=>52],

				// 4. Smartphones
				['name'=>'Android Phones','slug'=>'android-phones','parent_id'=>4,'description'=>'Smartphones running Android OS','sort_order'=>1],
				['name'=>'iPhones','slug'=>'iphones','parent_id'=>4,'description'=>'Apple iPhone models','sort_order'=>2],
				['name'=>'Refurbished Phones','slug'=>'refurbished-phones','parent_id'=>4,'description'=>'Certified refurbished smartphones','sort_order'=>3],
				['name'=>'Phone Accessories','slug'=>'phone-accessories','parent_id'=>4,'description'=>'Chargers, cables, cases, and screen protectors','sort_order'=>4],
				['name'=>'Smartphone Batteries','slug'=>'smartphone-batteries','parent_id'=>4,'description'=>'Replacement batteries and power banks','sort_order'=>5],
				['name'=>'Screen Protectors','slug'=>'screen-protectors','parent_id'=>4,'description'=>'Tempered glass and film protectors','sort_order'=>6],
				['name'=>'Phone Cases','slug'=>'phone-cases','parent_id'=>4,'description'=>'Protective and decorative phone cases','sort_order'=>7],
				['name'=>'Smartwatches','slug'=>'smartwatches','parent_id'=>4,'description'=>'Smartwatches compatible with smartphones','sort_order'=>8],
				['name'=>'Activity Trackers','slug'=>'activity-trackers','parent_id'=>4,'description'=>'Fitness and activity tracking bands','sort_order'=>9],
				['name'=>'Smart Rings','slug'=>'smart-rings','parent_id'=>4,'description'=>'Wearable rings with smart features','sort_order'=>10],
				['name'=>'AR Glasses','slug'=>'ar-glasses','parent_id'=>4,'description'=>'Augmented Reality wearable glasses','sort_order'=>11],
				['name'=>'Apple Watch','slug'=>'apple-watch','parent_id'=>4,'description'=>'Apple branded smartwatches','sort_order'=>12],
				['name'=>'Garmin','slug'=>'garmin','parent_id'=>4,'description'=>'Garmin wearable devices','sort_order'=>13],
				['name'=>'Huawei','slug'=>'huawei','parent_id'=>4,'description'=>'Huawei wearable devices','sort_order'=>14],
				['name'=>'Samsung','slug'=>'samsung','parent_id'=>4,'description'=>'Samsung wearable devices','sort_order'=>15],
				['name'=>'Xiaomi','slug'=>'xiaomi','parent_id'=>4,'description'=>'Xiaomi wearable devices','sort_order'=>16],
				['name'=>'HiFuture','slug'=>'hifuture','parent_id'=>4,'description'=>'HiFuture brand wearables','sort_order'=>17],
				['name'=>'Wearable Cases','slug'=>'wearable-cases','parent_id'=>4,'description'=>'Cases and protective covers for wearables','sort_order'=>18],
				['name'=>'Wearable Straps','slug'=>'wearable-straps','parent_id'=>4,'description'=>'Replacement straps and bands for wearables','sort_order'=>19],
				['name'=>'Wearable Screen Protectors','slug'=>'wearable-screen-protectors','parent_id'=>4,'description'=>'Screen protection for smartwatches and trackers','sort_order'=>20],
				['name'=>'Wearable Chargers','slug'=>'wearable-chargers','parent_id'=>4,'description'=>'Chargers and charging docks for wearables','sort_order'=>21],
				['name'=>'Wearable Accessories','slug'=>'wearable-accessories','parent_id'=>4,'description'=>'Miscellaneous accessories for wearable devices','sort_order'=>22],

				// 5. Gaming
				['name'=>'PlayStation 5','slug'=>'playstation-5','parent_id'=>5,'description'=>'Sony PlayStation 5 console and accessories','sort_order'=>1],
				['name'=>'PlayStation 4','slug'=>'playstation-4','parent_id'=>5,'description'=>'Sony PlayStation 4 console and accessories','sort_order'=>2],
				['name'=>'Xbox Series X','slug'=>'xbox-series-x','parent_id'=>5,'description'=>'Microsoft Xbox Series X console and accessories','sort_order'=>3],
				['name'=>'Xbox Series S','slug'=>'xbox-series-s','parent_id'=>5,'description'=>'Microsoft Xbox Series S console and accessories','sort_order'=>4],
				['name'=>'Xbox One','slug'=>'xbox-one','parent_id'=>5,'description'=>'Microsoft Xbox One console and accessories','sort_order'=>5],
				['name'=>'Nintendo Switch','slug'=>'nintendo-switch','parent_id'=>5,'description'=>'Nintendo Switch console, Joy-Con, and accessories','sort_order'=>6],
				['name'=>'Nintendo Switch Lite','slug'=>'nintendo-switch-lite','parent_id'=>5,'description'=>'Portable Nintendo Switch Lite console','sort_order'=>7],
				['name'=>'Nintendo Switch OLED','slug'=>'nintendo-switch-oled','parent_id'=>5,'description'=>'Nintendo Switch OLED model','sort_order'=>8],
				['name'=>'Retro Consoles','slug'=>'retro-consoles','parent_id'=>5,'description'=>'Classic gaming consoles like NES, SNES, Sega Genesis','sort_order'=>9],
				['name'=>'Handheld Gaming Consoles','slug'=>'handheld-gaming-consoles','parent_id'=>5,'description'=>'Portable consoles including Game Boy, PSP, Nintendo 3DS','sort_order'=>10],
				['name'=>'PC Games','slug'=>'pc-games','parent_id'=>5,'description'=>'Video game titles and licenses for Windows, macOS, and Linux platforms','sort_order'=>11],
				['name'=>'Console Games','slug'=>'console-games','parent_id'=>5,'description'=>'Physical and digital games for PlayStation, Xbox, and Nintendo consoles','sort_order'=>12],
				['name'=>'Gaming Accessories','slug'=>'gaming-accessories','parent_id'=>5,'description'=>'General controllers, headsets, keyboards, mice, and other peripherals for all platforms','sort_order'=>13],
				['name'=>'VR & AR','slug'=>'vr-ar','parent_id'=>5,'description'=>'Virtual reality and augmented reality gaming devices and peripherals','sort_order'=>14],
				['name'=>'Game Streaming & Capture','slug'=>'game-streaming-capture','parent_id'=>5,'description'=>'Streaming devices, capture cards, and software for content creation','sort_order'=>15],
				['name'=>'Gaming Chairs & Furniture','slug'=>'gaming-chairs-furniture','parent_id'=>5,'description'=>'Ergonomic chairs and desks for gamers','sort_order'=>16],
				['name'=>'Controllers & Gamepads','slug'=>'controllers-gamepads','parent_id'=>5,'description'=>'Wired and wireless controllers for PC and consoles, including Elite models.','sort_order'=>17],
				['name'=>'Gaming Storage & Memory','slug'=>'gaming-storage-memory','parent_id'=>5,'description'=>'External SSDs, expansion cards, and memory for consoles and PC games.','sort_order'=>18],
				['name'=>'Gaming Apparel & Merchandise','slug'=>'gaming-apparel-merchandise','parent_id'=>5,'description'=>'Official apparel, figures, posters, and collectibles from game franchises.','sort_order'=>19],
				['name'=>'Subscription & Digital Services','slug'=>'subscription-digital-services','parent_id'=>5,'description'=>'Codes and subscriptions for Xbox Game Pass, PlayStation Plus, and other online services.','sort_order'=>20],
				['name'=>'Sim & Specialty Controllers','slug'=>'sim-specialty-controllers','parent_id'=>5,'description'=>'Racing wheels, flight sticks, and specialized simulation input devices.','sort_order'=>21],

				// 6. Furniture
				['name'=>'Living Room Furniture','slug'=>'living-room-furniture','parent_id'=>6,'description'=>'Sofas, coffee tables, TV stands, and more','sort_order'=>1],
				['name'=>'Bedroom Furniture','slug'=>'bedroom-furniture','parent_id'=>6,'description'=>'Beds, wardrobes, nightstands, and dressers','sort_order'=>2],
				['name'=>'Office Furniture','slug'=>'office-furniture','parent_id'=>6,'description'=>'Desks, chairs, filing cabinets, and shelves','sort_order'=>3],
				['name'=>'Dining Room Furniture','slug'=>'dining-room-furniture','parent_id'=>6,'description'=>'Dining tables, chairs, and storage units','sort_order'=>4],
				['name'=>'Outdoor Furniture','slug'=>'outdoor-furniture','parent_id'=>6,'description'=>'Patio sets, garden chairs, and tables','sort_order'=>5],
				['name'=>'Storage & Shelving','slug'=>'storage-shelving','parent_id'=>6,'description'=>'Bookshelves, cabinets, and storage units','sort_order'=>6],
				['name'=>'Kids Furniture','slug'=>'kids-furniture','parent_id'=>6,'description'=>'Beds, desks, and storage solutions for children','sort_order'=>7],
				['name'=>'Accent Furniture','slug'=>'accent-furniture','parent_id'=>6,'description'=>'Side tables, benches, and decorative pieces','sort_order'=>8],

				// 7. Office Supplies
				['name'=>'Writing Instruments','slug'=>'writing-instruments','parent_id'=>7,'description'=>'Pens, pencils, markers, and highlighters','sort_order'=>1],
				['name'=>'Paper & Notebooks','slug'=>'paper-notebooks','parent_id'=>7,'description'=>'Printing paper, notebooks, and pads','sort_order'=>2],
				['name'=>'Filing & Organization','slug'=>'filing-organization','parent_id'=>7,'description'=>'Folders, binders, filing cabinets, and organizers','sort_order'=>3],
				['name'=>'Desk Accessories','slug'=>'desk-accessories','parent_id'=>7,'description'=>'Staplers, scissors, tape, and small desk items','sort_order'=>4],
				['name'=>'Office Calendars & Desk Pads','slug'=>'office-calendars-desk-pads','parent_id'=>7,'description'=>'Wall calendars, daily desk pads, and functional appointment books','sort_order'=>5],
				['name'=>'Labels & Stickers','slug'=>'labels-stickers','parent_id'=>7,'description'=>'Label makers, sheets, and stickers','sort_order'=>6],
				['name'=>'Ink & Toner','slug'=>'ink-toner','parent_id'=>7,'description'=>'Printer cartridges, ink bottles, and toner','sort_order'=>7],
				['name'=>'Mailing & Shipping','slug'=>'mailing-shipping','parent_id'=>7,'description'=>'Envelopes, packaging, and mailing supplies','sort_order'=>8],

				// 8. Kitchenware
				['name'=>'Cookware','slug'=>'cookware','parent_id'=>8,'description'=>'Pots, pans, skillets, and cooking utensils','sort_order'=>1],
				['name'=>'Bakeware','slug'=>'bakeware','parent_id'=>8,'description'=>'Baking trays, molds, and accessories','sort_order'=>2],
				['name'=>'Cutlery & Knives','slug'=>'cutlery-knives','parent_id'=>8,'description'=>'Kitchen knives, knife sets, and cutlery','sort_order'=>3],
				['name'=>'Kitchen Gadgets','slug'=>'kitchen-gadgets','parent_id'=>8,'description'=>'Peelers, graters, slicers, and small tools','sort_order'=>4],
				['name'=>'Storage & Organization','slug'=>'kitchen-storage-organization','parent_id'=>8,'description'=>'Containers, jars, and organizers','sort_order'=>5],
				['name'=>'Dinnerware','slug'=>'dinnerware','parent_id'=>8,'description'=>'Plates, bowls, and serving dishes','sort_order'=>6],
				['name'=>'Glassware & Drinkware','slug'=>'glassware-drinkware','parent_id'=>8,'description'=>'Glasses, mugs, and cups','sort_order'=>7],
				['name'=>'Small Appliances','slug'=>'small-appliances','parent_id'=>8,'description'=>'Blenders, toasters, coffee makers, and kettles','sort_order'=>8],

				// 9. Lighting
				['name'=>'Ceiling Lights','slug'=>'ceiling-lights','parent_id'=>9,'description'=>'Chandeliers, pendant lights, flush mounts, and general overhead lighting','sort_order'=>1],
				['name'=>'Wall Lights','slug'=>'wall-lights','parent_id'=>9,'description'=>'Sconces, wall lamps, and decorative fixtures for ambient or accent lighting','sort_order'=>2],
				['name'=>'Table Lamps','slug'=>'table-lamps','parent_id'=>9,'description'=>'Desk lamps, bedside lamps, and accent table lighting','sort_order'=>3],
				['name'=>'Floor Lamps','slug'=>'floor-lamps','parent_id'=>9,'description'=>'Standing lamps for living rooms, offices, and reading areas','sort_order'=>4],
				['name'=>'Outdoor Lighting','slug'=>'outdoor-lighting','parent_id'=>9,'description'=>'Garden lights, floodlights, pathway lighting, and solar lamps','sort_order'=>5],
				['name'=>'LED & Smart Lighting','slug'=>'led-smart-lighting','parent_id'=>9,'description'=>'Energy-efficient LED bulbs, smart bulbs, LED strips, and programmable lighting','sort_order'=>6],

				// 10. Sports & Outdoors
				['name'=>'Fitness Equipment','slug'=>'fitness-equipment-outdoors','parent_id'=>10,'description'=>'Gym machines, weights, yoga mats','sort_order'=>1],
				['name'=>'Outdoor Gear','slug'=>'outdoor-gear-outdoors','parent_id'=>10,'description'=>'Tents, backpacks, camping equipment','sort_order'=>2],
				['name'=>'Team Sports','slug'=>'team-sports-outdoors','parent_id'=>10,'description'=>'Soccer, basketball, and volleyball gear','sort_order'=>3],
				['name'=>'Outdoor Sports','slug'=>'outdoor-sports','parent_id'=>10,'description'=>'Biking, hiking, camping, and climbing gear','sort_order'=>7],
				['name'=>'Water Sports','slug'=>'water-sports','parent_id'=>10,'description'=>'Kayaking, paddleboarding, and swimming gear','sort_order'=>4],
				['name'=>'Sportswear','slug'=>'sportswear','parent_id'=>10,'description'=>'Activewear, shoes, and sports accessories','sort_order'=>5],
				['name'=>'Protective Gear','slug'=>'protective-gear','parent_id'=>10,'description'=>'Helmets, pads, and safety equipment','sort_order'=>6],

				// 11. Fashion & Clothing
				['name'=>'Men\'s Clothing','slug'=>'mens-clothing-fashion','parent_id'=>11,'description'=>'Shirts, pants, jackets, suits, and outerwear','sort_order'=>1],
				['name'=>'Women\'s Clothing','slug'=>'womens-clothing-fashion','parent_id'=>11,'description'=>'Dresses, tops, skirts, pants, and outerwear','sort_order'=>2],
				['name'=>'Kids Clothing','slug'=>'kids-clothing','parent_id'=>11,'description'=>'Clothing for boys and girls of all ages','sort_order'=>3],
				['name'=>'Footwear','slug'=>'footwear-fashion','parent_id'=>11,'description'=>'Shoes, boots, sandals, sneakers, and slippers','sort_order'=>4],
				['name'=>'Activewear & Sportswear','slug'=>'activewear-sportswear','parent_id'=>11,'description'=>'Gym clothing, leggings, sports tops, and tracksuits','sort_order'=>5],
				['name'=>'Undergarments & Lingerie','slug'=>'undergarments-lingerie','parent_id'=>11,'description'=>'Underwear, bras, socks, and sleepwear','sort_order'=>6],
				['name'=>'Swimwear','slug'=>'swimwear','parent_id'=>11,'description'=>'Bikinis, swimsuits, trunks, and cover-ups','sort_order'=>7],
				['name'=>'Accessories','slug'=>'fashion-accessories','parent_id'=>11,'description'=>'Belts, scarves, wallets, gloves, and sunglasses','sort_order'=>8],
				['name'=>'Bags & Handbags','slug'=>'bags-handbags','parent_id'=>11,'description'=>'Totes, handbags, backpacks, and luggage','sort_order'=>9],
				['name'=>'Jewelry','slug'=>'jewelry-fashion','parent_id'=>11,'description'=>'Necklaces, rings, bracelets, earrings, and watches','sort_order'=>10],

				// 12. Beauty & Personal Care
				['name'=>'Skincare','slug'=>'skincare','parent_id'=>12,'description'=>'Lotions, creams, and cleansers','sort_order'=>1],
				['name'=>'Hair Care','slug'=>'hair-care','parent_id'=>12,'description'=>'Shampoos, conditioners, and styling products','sort_order'=>2],
				['name'=>'Makeup','slug'=>'makeup','parent_id'=>12,'description'=>'Lipsticks, foundations, and eye makeup','sort_order'=>3],
				['name'=>'Fragrances','slug'=>'fragrances','parent_id'=>12,'description'=>'Perfumes and colognes','sort_order'=>4],
				['name'=>'Personal Hygiene','slug'=>'personal-hygiene','parent_id'=>12,'description'=>'Soaps, deodorants, oral care products','sort_order'=>5],
				['name'=>'Bath & Body','slug'=>'bath-body','parent_id'=>12,'description'=>'Soaps, body washes, scrubs, lotions, and shower gels','sort_order'=>6],
				['name'=>'Men\'s Grooming','slug'=>'mens-grooming','parent_id'=>12,'description'=>'Shaving products, beard care, skincare, and hair styling for men','sort_order'=>10],
				['name'=>'Oral Care','slug'=>'oral-care','parent_id'=>12,'description'=>'Toothpaste, toothbrushes, mouthwash, and teeth whitening products','sort_order'=>7],
				['name'=>'Nail Care','slug'=>'nail-care','parent_id'=>12,'description'=>'Nail polish, removers, nail tools, and treatments','sort_order'=>8],
				['name'=>'Beauty Tools & Accessories','slug'=>'beauty-tools-accessories','parent_id'=>12,'description'=>'Makeup brushes, sponges, hairdryers, and beauty gadgets','sort_order'=>9],

				// 13. Books & Stationery
				['name' => 'Fiction', 'slug' => 'fiction-books', 'parent_id' => 13, 'description' => 'Novels, short stories, and classic literature from around the world.', 'sort_order' => 1],
				['name' => 'Non-Fiction', 'slug' => 'non-fiction-books', 'parent_id' => 13, 'description' => 'General non-fiction, self-help, and general knowledge books.', 'sort_order' => 2],
				['name' => 'Children’s Books', 'slug' => 'children-books', 'parent_id' => 13, 'description' => 'Picture books, early readers, and stories for young minds.', 'sort_order' => 3],
				['name' => 'Educational & Academic', 'slug' => 'educational-academic', 'parent_id' => 13, 'description' => 'Textbooks, study guides, and educational resources.', 'sort_order' => 4],
				['name' => 'Stationery', 'slug' => 'stationery-supplies', 'parent_id' => 13, 'description' => 'General notebooks, pens, and basic supplies.', 'sort_order' => 5],
				['name' => 'Art & Design', 'slug' => 'art-design-books', 'parent_id' => 13, 'description' => 'Books and materials on art, architecture, and creative design.', 'sort_order' => 6],
				['name' => 'Comics & Graphic Novels', 'slug' => 'comics-graphic-novels', 'parent_id' => 13, 'description' => 'Manga, graphic novels, and comic book collections.', 'sort_order' => 7],
				['name' => 'Journals & Diaries', 'slug' => 'journals-diaries', 'parent_id' => 13, 'description' => 'Personal journals, bullet journals, and diaries for daily writing.', 'sort_order' => 8],
				['name' => 'Office Supplies', 'slug' => 'writing-supplies', 'parent_id' => 13, 'description' => 'Folders, staplers, clips, and all your office essentials.', 'sort_order' => 9],
				['name' => 'Premium Planners & Dated Journals', 'slug' => 'premium-planners-dated-journals', 'parent_id' => 13, 'description' => 'High-quality, dated planners, organizers, and collectible journals.', 'sort_order' => 10],
				['name' => 'Language Learning', 'slug' => 'language-learning', 'parent_id' => 13, 'description' => 'Books and guides for learning new languages.', 'sort_order' => 11],
				['name' => 'Religious & Spiritual', 'slug' => 'religious-spiritual-books', 'parent_id' => 13, 'description' => 'Sacred texts, spiritual guidance, and religious studies.', 'sort_order' => 12],
				['name' => 'Cookbooks', 'slug' => 'cookbooks', 'parent_id' => 13, 'description' => 'Recipes, culinary guides, and food culture books.', 'sort_order' => 13],
				['name' => 'Travel Guides', 'slug' => 'travel-guides', 'parent_id' => 13, 'description' => 'Travel books, maps, and cultural exploration guides.', 'sort_order' => 14],
				['name' => 'Science & Technology', 'slug' => 'science-technology-books', 'parent_id' => 13, 'description' => 'Scientific discoveries, engineering, and modern tech books.', 'sort_order' => 15],
				['name' => 'Law & Business', 'slug' => 'law-business-books', 'parent_id' => 13, 'description' => 'Resources on law, finance, entrepreneurship, and business management.', 'sort_order' => 16],
				['name' => 'Health & Fitness', 'slug' => 'health-fitness-books', 'parent_id' => 13, 'description' => 'Books on wellness, exercise, and mental health.', 'sort_order' => 17],
				['name' => 'Poetry', 'slug' => 'poetry-books', 'parent_id' => 13, 'description' => 'Collections of poems and anthologies from modern and classic poets.', 'sort_order' => 18],
				['name' => 'Photography', 'slug' => 'photography-books', 'parent_id' => 13, 'description' => 'Guides and inspiration for photography lovers and professionals.', 'sort_order' => 19],
				['name' => 'Crafts & DIY', 'slug' => 'crafts-diy', 'parent_id' => 13, 'description' => 'Books and materials for handmade art, home projects, and creativity.', 'sort_order'=>20],
				['name' => 'Book Reading Accessories', 'slug' => 'book-reading-accessories', 'parent_id' => 13, 'description' => 'Book lights, bookmarks, book stands, and protective sleeves.', 'sort_order' => 21],
				['name' => 'Special Editions & Signed Copies', 'slug' => 'special-edition-books', 'parent_id' => 13, 'description' => 'Signed books, collector sets, and limited run editions.', 'sort_order' => 22],
				['name' => 'Biography & Memoir', 'slug' => 'biography-memoir', 'parent_id' => 13, 'description' => 'Life stories, autobiographies, and historical figure biographies.', 'sort_order' => 23],
				['name' => 'History & Politics', 'slug' => 'history-politics', 'parent_id' => 13, 'description' => 'World history, political science, and current affairs analysis.', 'sort_order' => 24],
				['name' => 'Luxury Writing Supplies', 'slug' => 'luxury-writing-supplies', 'parent_id' => 13, 'description' => 'Fountain pens, fine leather journals, and specialized paper stock.', 'sort_order' => 25],

				// 14. Gardening
				['name' => 'Plants & Seeds', 'slug' => 'plants-seeds', 'parent_id' => 14, 'description' => 'Flowering plants, herbs, vegetables, and seed packets for your garden.', 'sort_order' => 1],
				['name' => 'Pots & Planters', 'slug' => 'pots-planters', 'parent_id' => 14, 'description' => 'Ceramic, plastic, and metal pots in various shapes and sizes.', 'sort_order' => 2],
				['name' => 'Gardening Tools', 'slug' => 'gardening-tools', 'parent_id' => 14, 'description' => 'Essential tools like spades, trowels, pruners, and rakes.', 'sort_order' => 3],
				['name' => 'Soil & Fertilizers', 'slug' => 'soil-fertilizers', 'parent_id' => 14, 'description' => 'Potting soil, compost, and organic or chemical fertilizers.', 'sort_order' => 4],
				['name' => 'Watering Equipment', 'slug' => 'watering-equipment', 'parent_id' => 14, 'description' => 'Hoses, sprinklers, watering cans, and irrigation systems.', 'sort_order' => 5],
				['name' => 'Outdoor Decor', 'slug' => 'outdoor-decor', 'parent_id' => 14, 'description' => 'Garden ornaments, statues, and decorative lighting.', 'sort_order' => 6],
				['name' => 'Garden Furniture', 'slug' => 'garden-furniture', 'parent_id' => 14, 'description' => 'Outdoor chairs, tables, benches, and patio sets.', 'sort_order' => 7],
				['name' => 'Greenhouses', 'slug' => 'greenhouses', 'parent_id' => 14, 'description' => 'Mini and full-size greenhouses for plant propagation and protection.', 'sort_order' => 8],
				['name' => 'Plant Protection', 'slug' => 'plant-protection', 'parent_id' => 14, 'description' => 'Nets, covers, pesticides, and natural repellents.', 'sort_order' => 9],
				['name' => 'Garden Lighting', 'slug' => 'garden-lighting', 'parent_id' => 14, 'description' => 'Solar lights, lanterns, and string lights for outdoor ambiance.', 'sort_order' => 10],
				['name' => 'Composting', 'slug' => 'composting', 'parent_id' => 14, 'description' => 'Compost bins, tumblers, and accessories for waste recycling.', 'sort_order' => 11],
				['name' => 'Lawn Care', 'slug' => 'lawn-care', 'parent_id' => 14, 'description' => 'Grass seeds, mowers, trimmers, and maintenance equipment.', 'sort_order' => 12],
				['name' => 'Vertical Gardening', 'slug' => 'vertical-gardening', 'parent_id' => 14, 'description' => 'Wall planters and systems for small-space gardening.', 'sort_order' => 13],
				['name' => 'Hydroponics', 'slug' => 'hydroponics', 'parent_id' => 14, 'description' => 'Soilless gardening systems and nutrient solutions.', 'sort_order' => 14],
				['name' => 'Irrigation Systems', 'slug' => 'irrigation-systems', 'parent_id' => 14, 'description' => 'Automatic watering setups for home and commercial gardens.', 'sort_order' => 15],
				['name' => 'Plant Supports', 'slug' => 'plant-supports', 'parent_id' => 14, 'description' => 'Trellises, stakes, and cages to support growing plants.', 'sort_order' => 16],
				['name' => 'Garden Storage', 'slug' => 'garden-storage', 'parent_id' => 14, 'description' => 'Outdoor storage boxes and sheds for garden tools and supplies.', 'sort_order' => 17],
				['name' => 'Bird & Wildlife', 'slug' => 'bird-wildlife', 'parent_id' => 14, 'description' => 'Bird feeders, baths, and accessories for attracting wildlife.', 'sort_order' => 18],
				['name' => 'Herb Gardening', 'slug' => 'herb-gardening', 'parent_id' => 14, 'description' => 'Kits and tools for growing herbs like basil, mint, and rosemary.', 'sort_order' => 19],
				['name' => 'Garden Accessories', 'slug' => 'garden-accessories', 'parent_id' => 14, 'description' => 'Gloves, aprons, knee pads, and gardening accessories.', 'sort_order' => 20],

				// 15. Automotive
				['name' => 'Car Accessories', 'slug' => 'car-accessories', 'parent_id' => 15, 'description' => 'Interior and exterior accessories to personalize and upgrade your car.', 'sort_order' => 1],
				['name' => 'Car Care', 'slug' => 'car-care', 'parent_id' => 15, 'description' => 'Cleaning supplies, wax, and detailing products for maintaining your car’s look.', 'sort_order' => 2],
				['name' => 'Lubricants & Fluids', 'slug' => 'lubricants-fluids', 'parent_id' => 15, 'description' => 'Engine oil, brake fluid, coolant, and transmission oil for smooth performance.', 'sort_order' => 3],
				['name' => 'Tires & Wheels', 'slug' => 'tires-wheels', 'parent_id' => 15, 'description' => 'Car tires, rims, and wheel accessories for all types of vehicles.', 'sort_order' => 4],
				['name' => 'Car Electronics', 'slug' => 'car-electronics', 'parent_id' => 15, 'description' => 'Audio systems, navigation, dash cams, and electronic gadgets for vehicles.', 'sort_order' => 5],
				['name' => 'Replacement Parts', 'slug' => 'replacement-parts', 'parent_id' => 15, 'description' => 'High-quality spare parts including filters, belts, and hoses.', 'sort_order' => 6],
				['name' => 'Battery & Charging', 'slug' => 'battery-charging', 'parent_id' => 15, 'description' => 'Car batteries, chargers, jump starters, and related accessories.', 'sort_order' => 7],
				['name' => 'Tools & Equipment', 'slug' => 'tools-equipment', 'parent_id' => 15, 'description' => 'Wrenches, jacks, lifts, and diagnostic tools for vehicle maintenance.', 'sort_order' => 8],
				['name' => 'Performance Parts', 'slug' => 'performance-parts', 'parent_id' => 15, 'description' => 'Performance exhausts, air filters, and tuning components for enthusiasts.', 'sort_order' => 9],
				['name' => 'Motor Oils & Additives', 'slug' => 'motor-oils-additives', 'parent_id' => 15, 'description' => 'High-performance motor oils and fuel additives to protect engines.', 'sort_order' => 10],
				['name' => 'Car Lighting', 'slug' => 'car-lighting', 'parent_id' => 15, 'description' => 'Headlights, fog lights, LED bulbs, and interior lighting options.', 'sort_order' => 11],
				['name' => 'Safety & Security', 'slug' => 'safety-security', 'parent_id' => 15, 'description' => 'Car alarms, sensors, cameras, and safety kits for secure driving.', 'sort_order' => 12],
				['name' => 'Garage & Workshop', 'slug' => 'garage-workshop', 'parent_id' => 15, 'description' => 'Garage equipment, tool storage, and maintenance solutions.', 'sort_order' => 13],
				['name' => 'Car Covers & Protection', 'slug' => 'car-covers-protection', 'parent_id' => 15, 'description' => 'Covers, mats, and protectors for cars, seats, and dashboards.', 'sort_order' => 14],
				['name' => 'Air Fresheners', 'slug' => 'air-fresheners', 'parent_id' => 15, 'description' => 'Car perfumes and air fresheners for a pleasant driving experience.', 'sort_order' => 15],
				['name' => 'Motorcycle Accessories', 'slug' => 'motorcycle-accessories', 'parent_id' => 15, 'description' => 'Helmets, gloves, jackets, and other motorbike essentials.', 'sort_order' => 16],
				['name' => 'Car Audio & Video', 'slug' => 'car-audio-video', 'parent_id' => 15, 'description' => 'Speakers, amplifiers, and entertainment systems for cars.', 'sort_order' => 17],
				['name' => 'Navigation & GPS', 'slug' => 'navigation-gps', 'parent_id' => 15, 'description' => 'GPS devices and maps for efficient and safe driving.', 'sort_order' => 18],
				['name' => 'Truck Accessories', 'slug' => 'truck-accessories', 'parent_id' => 15, 'description' => 'Accessories and gear specifically for trucks and heavy vehicles.', 'sort_order' => 19],
				['name' => 'Car Cleaning Kits', 'slug' => 'car-cleaning-kits', 'parent_id' => 15, 'description' => 'Complete car cleaning and detailing kits for exterior and interior.', 'sort_order' => 20],

				// 16. Pets
				['name' => 'Dog Supplies', 'slug' => 'dog-supplies', 'parent_id' => 16, 'description' => 'Everything for dogs including food, toys, and accessories.', 'sort_order' => 1],
				['name' => 'Cat Supplies', 'slug' => 'cat-supplies', 'parent_id' => 16, 'description' => 'Food, litter, and toys to keep your cat happy and healthy.', 'sort_order' => 2],
				['name' => 'Bird Supplies', 'slug' => 'bird-supplies', 'parent_id' => 16, 'description' => 'Cages, food, and toys for parrots, canaries, and other birds.', 'sort_order' => 3],
				['name' => 'Fish & Aquarium', 'slug' => 'fish-aquarium', 'parent_id' => 16, 'description' => 'Aquariums, filters, and accessories for freshwater and saltwater fish.', 'sort_order' => 4],
				['name' => 'Small Pet Supplies', 'slug' => 'small-pet-supplies', 'parent_id' => 16, 'description' => 'Products for hamsters, guinea pigs, and rabbits.', 'sort_order' => 5],
				['name' => 'Reptile Supplies', 'slug' => 'reptile-supplies', 'parent_id' => 16, 'description' => 'Terrariums, heating, and food for lizards, snakes, and turtles.', 'sort_order' => 6],
				['name' => 'Pet Food', 'slug' => 'pet-food', 'parent_id' => 16, 'description' => 'Healthy and nutritious food for all types of pets.', 'sort_order' => 7],
				['name' => 'Pet Toys', 'slug' => 'pet-toys', 'parent_id' => 16, 'description' => 'Interactive and durable toys for dogs, cats, and more.', 'sort_order' => 8],
				['name' => 'Pet Grooming', 'slug' => 'pet-grooming', 'parent_id' => 16, 'description' => 'Shampoos, brushes, and grooming tools for pets.', 'sort_order' => 9],
				['name' => 'Pet Health & Wellness', 'slug' => 'pet-health-wellness', 'parent_id' => 16, 'description' => 'Vitamins, supplements, and healthcare products for pets.', 'sort_order' => 10],
				['name' => 'Pet Training', 'slug' => 'pet-training', 'parent_id' => 16, 'description' => 'Training tools, clickers, and accessories for behavior management.', 'sort_order' => 11],
				['name' => 'Pet Carriers & Travel', 'slug' => 'pet-carriers-travel', 'parent_id' => 16, 'description' => 'Carriers, crates, and travel gear for safe pet transportation.', 'sort_order' => 12],
				['name' => 'Pet Beds & Furniture', 'slug' => 'pet-beds-furniture', 'parent_id' => 16, 'description' => 'Comfortable beds, mats, and furniture for pets to rest.', 'sort_order' => 13],
				['name' => 'Pet Clothing & Accessories', 'slug' => 'pet-clothing-accessories', 'parent_id' => 16, 'description' => 'Clothes, collars, and stylish accessories for pets.', 'sort_order' => 14],
				['name' => 'Pet Cleaning & Waste Management', 'slug' => 'pet-cleaning-waste', 'parent_id' => 16, 'description' => 'Litter boxes, waste bags, and cleaning supplies.', 'sort_order' => 15],
				['name' => 'Pet Doors & Gates', 'slug' => 'pet-doors-gates', 'parent_id' => 16, 'description' => 'Safety gates, doors, and barriers for managing pet movement.', 'sort_order' => 16],
				['name' => 'Pet Identification', 'slug' => 'pet-identification', 'parent_id' => 16, 'description' => 'Tags, microchips, and collars for identifying pets.', 'sort_order' => 17],
				['name' => 'Pet Bowls & Feeders', 'slug' => 'pet-bowls-feeders', 'parent_id' => 16, 'description' => 'Automatic feeders, bowls, and water dispensers.', 'sort_order' => 18],
				['name' => 'Pet Cleaning Supplies', 'slug' => 'pet-cleaning-supplies', 'parent_id' => 16, 'description' => 'Odor removers, brushes, and hygiene accessories.', 'sort_order' => 19],
				['name' => 'Pet Adoption & Welfare', 'slug' => 'pet-adoption-welfare', 'parent_id' => 16, 'description' => 'Resources and services for adopting and helping animals.', 'sort_order' => 20],

				// 17. Food & Beverages
				['name' => 'Fruits & Vegetables', 'slug' => 'fruits-vegetables', 'parent_id' => 17, 'description' => 'Fresh fruits, vegetables, and organic produce.', 'sort_order' => 1],
				['name' => 'Dairy & Eggs', 'slug' => 'dairy-eggs', 'parent_id' => 17, 'description' => 'Milk, cheese, butter, yogurt, and fresh eggs.', 'sort_order' => 2],
				['name' => 'Meat & Seafood', 'slug' => 'meat-seafood', 'parent_id' => 17, 'description' => 'Fresh and frozen meat, poultry, and seafood.', 'sort_order' => 3],
				['name' => 'Bakery', 'slug' => 'bakery', 'parent_id' => 17, 'description' => 'Freshly baked bread, pastries, cakes, and desserts.', 'sort_order' => 4],
				['name' => 'Pantry Staples', 'slug' => 'pantry-staples', 'parent_id' => 17, 'description' => 'Rice, pasta, flour, sugar, and essential dry goods.', 'sort_order' => 5],
				['name' => 'Snacks & Confectionery', 'slug' => 'snacks-confectionery', 'parent_id' => 17, 'description' => 'Chips, biscuits, chocolates, candies, and sweets.', 'sort_order' => 6],
				['name' => 'Beverages', 'slug' => 'beverages', 'parent_id' => 17, 'description' => 'Soft drinks, juices, teas, coffees, and bottled water.', 'sort_order' => 7],
				['name' => 'Coffee & Tea', 'slug' => 'coffee-tea', 'parent_id' => 17, 'description' => 'Ground coffee, tea bags, herbal blends, and accessories.', 'sort_order' => 8],
				['name' => 'Canned & Packaged Foods', 'slug' => 'canned-packaged-foods', 'parent_id' => 17, 'description' => 'Canned vegetables, soups, sauces, and ready-to-eat meals.', 'sort_order' => 9],
				['name' => 'Breakfast Foods', 'slug' => 'breakfast-foods', 'parent_id' => 17, 'description' => 'Cereals, oats, spreads, jams, and honey.', 'sort_order' => 10],
				['name' => 'Condiments & Sauces', 'slug' => 'condiments-sauces', 'parent_id' => 17, 'description' => 'Ketchup, mustard, mayonnaise, and specialty sauces.', 'sort_order' => 11],
				['name' => 'Frozen Foods', 'slug' => 'frozen-foods', 'parent_id' => 17, 'description' => 'Frozen vegetables, pizzas, ice creams, and desserts.', 'sort_order' => 12],
				['name' => 'Organic & Health Foods', 'slug' => 'organic-health-foods', 'parent_id' => 17, 'description' => 'Organic groceries, gluten-free, vegan, and superfoods.', 'sort_order' => 13],
				['name' => 'Spices & Seasonings', 'slug' => 'spices-seasonings', 'parent_id' => 17, 'description' => 'Herbs, spices, and seasoning mixes from around the world.', 'sort_order' => 14],
				['name' => 'Cooking Oils & Vinegars', 'slug' => 'cooking-oils-vinegars', 'parent_id' => 17, 'description' => 'Olive oil, sunflower oil, balsamic and apple cider vinegars.', 'sort_order' => 15],
				['name' => 'Gourmet & International Foods', 'slug' => 'gourmet-international-foods', 'parent_id' => 17, 'description' => 'Imported specialties, sauces, and global ingredients.', 'sort_order' => 16],
				['name' => 'Baby Food', 'slug' => 'baby-food', 'parent_id' => 17, 'description' => 'Healthy and nutritious meals for infants and toddlers.', 'sort_order' => 17],
				['name' => 'Pet Treats', 'slug' => 'pet-treats', 'parent_id' => 17, 'description' => 'Specialized food for dogs, cats, birds, and small animals.', 'sort_order' => 18],
				['name' => 'Alcoholic Beverages', 'slug' => 'alcoholic-beverages', 'parent_id' => 17, 'description' => 'Beer, wine, spirits, and liquors for adults.', 'sort_order' => 19],
				['name' => 'Non-Alcoholic Drinks', 'slug' => 'non-alcoholic-drinks', 'parent_id' => 17, 'description' => 'Sodas, mocktails, flavored waters, and energy drinks.', 'sort_order' => 20],

				// 18. Toys & Games
				['name' => 'Action Figures', 'slug' => 'action-figures', 'parent_id' => 18, 'description' => 'Collectible and playable action figures from popular movies, comics, and games.', 'sort_order' => 1],
				['name' => 'Building Sets & Blocks', 'slug' => 'building-sets-blocks', 'parent_id' => 18, 'description' => 'LEGO sets, magnetic tiles, and other creative construction toys.', 'sort_order' => 2],
				['name' => 'Dolls & Accessories', 'slug' => 'dolls-accessories', 'parent_id' => 18, 'description' => 'Fashion dolls, baby dolls, dollhouses, and related accessories.', 'sort_order' => 3],
				['name' => 'Board Games', 'slug' => 'board-games', 'parent_id' => 18, 'description' => 'Classic and modern board games for kids, families, and adults.', 'sort_order' => 4],
				['name' => 'Puzzles', 'slug' => 'puzzles', 'parent_id' => 18, 'description' => 'Jigsaw puzzles, logic puzzles, and 3D brain teasers for all ages.', 'sort_order' => 5],
				['name' => 'Outdoor Play', 'slug' => 'outdoor-play', 'parent_id' => 18, 'description' => 'Swing sets, trampolines, water toys, and other outdoor fun activities.', 'sort_order' => 6],
				['name' => 'Educational Toys', 'slug' => 'educational-toys', 'parent_id' => 18, 'description' => 'STEM, STEAM, and Montessori-based learning toys for young minds.', 'sort_order' => 7],
				['name' => 'Arts & Crafts', 'slug' => 'arts-crafts', 'parent_id' => 18, 'description' => 'Creative materials like paints, clay, DIY kits, and craft supplies.', 'sort_order' => 8],
				['name' => 'Electronic Toys', 'slug' => 'electronic-toys', 'parent_id' => 18, 'description' => 'Interactive electronic toys, talking robots, and coding gadgets.', 'sort_order' => 9],
				['name' => 'Stuffed Animals & Plush Toys', 'slug' => 'stuffed-animals-plush-toys', 'parent_id' => 18, 'description' => 'Soft toys, teddy bears, and plush characters for children.', 'sort_order' => 10],
				['name' => 'Vehicles & Remote Control', 'slug' => 'vehicles-remote-control', 'parent_id' => 18, 'description' => 'Remote-controlled cars, drones, helicopters, and model vehicles.', 'sort_order' => 11],
				['name' => 'Baby & Toddler Toys', 'slug' => 'baby-toddler-toys', 'parent_id' => 18, 'description' => 'Safe and colorful toys for infants and toddlers to develop early skills.', 'sort_order' => 12],
				['name' => 'Pretend Play', 'slug' => 'pretend-play', 'parent_id' => 18, 'description' => 'Kitchen sets, tool kits, costumes, and role-playing toys.', 'sort_order' => 13],
				['name' => 'Games & Card Games', 'slug' => 'games-card-games', 'parent_id' => 18, 'description' => 'Family-friendly games, strategy card decks, and classic fun titles.', 'sort_order' => 14],
				['name' => 'Sports & Activity Toys', 'slug' => 'sports-activity-toys', 'parent_id' => 18, 'description' => 'Balls, rackets, mini goals, and fun sports gear for children.', 'sort_order' => 15],
				['name' => 'Collectibles', 'slug' => 'collectibles', 'parent_id' => 18, 'description' => 'Trading cards, figurines, and collectibles for enthusiasts.', 'sort_order' => 16],
				['name' => 'Musical Toys', 'slug' => 'musical-toys', 'parent_id' => 18, 'description' => 'Toy instruments like mini keyboards, drums, and xylophones.', 'sort_order' => 17],
				['name' => 'Party Games', 'slug' => 'party-games', 'parent_id' => 18, 'description' => 'Fun and engaging games perfect for parties and gatherings.', 'sort_order' => 18],
				['name' => 'Science Kits', 'slug' => 'science-kits', 'parent_id' => 18, 'description' => 'Experiment kits for chemistry, physics, and biology exploration.', 'sort_order' => 19],
				['name' => 'Seasonal Toys', 'slug' => 'seasonal-toys', 'parent_id' => 18, 'description' => 'Holiday-themed and limited-edition toys for special occasions.', 'sort_order' => 20],

				// 19. Health & Wellness
				['name' => 'Vitamins & Supplements', 'slug' => 'vitamins-supplements', 'parent_id' => 19, 'description' => 'Daily vitamins, minerals, and dietary supplements to support overall health.', 'sort_order' => 1],
				['name' => 'Fitness & Exercise', 'slug' => 'fitness-exercise', 'parent_id' => 19, 'description' => 'Exercise equipment, resistance bands, yoga mats, and home gym essentials.', 'sort_order' => 2],
				['name' => 'Personal Care', 'slug' => 'personal-care', 'parent_id' => 19, 'description' => 'Hygiene products, skincare, haircare, and oral care essentials.', 'sort_order' => 3],
				['name' => 'First Aid & Medical Supplies', 'slug' => 'first-aid-medical-supplies', 'parent_id' => 19, 'description' => 'Bandages, antiseptics, medical instruments, and home first aid kits.', 'sort_order' => 4],
				['name' => 'Wellness Devices', 'slug' => 'wellness-devices', 'parent_id' => 19, 'description' => 'Thermometers, blood pressure monitors, massagers, and health gadgets.', 'sort_order' => 5],
				['name' => 'Nutrition & Diet', 'slug' => 'nutrition-diet', 'parent_id' => 19, 'description' => 'Protein powders, meal replacements, and dietary planning products.', 'sort_order' => 6],
				['name' => 'Sleep & Relaxation', 'slug' => 'sleep-relaxation', 'parent_id' => 19, 'description' => 'Sleep aids, aromatherapy, cushions, and relaxation tools.', 'sort_order' => 7],
				['name' => 'Mental Wellness', 'slug' => 'mental-wellness', 'parent_id' => 19, 'description' => 'Meditation guides, stress relief tools, and mental wellness aids.', 'sort_order' => 8],
				['name' => 'Weight Management', 'slug' => 'weight-management', 'parent_id' => 19, 'description' => 'Weight scales, trackers, and supplements for weight control.', 'sort_order' => 9],
				['name' => 'Healthy Snacks & Foods', 'slug' => 'healthy-snacks-foods', 'parent_id' => 19, 'description' => 'Low-calorie, organic, and health-focused snacks and beverages.', 'sort_order' => 10],
				['name' => 'Foot & Hand Care', 'slug' => 'foot-hand-care', 'parent_id' => 19, 'description' => 'Creams, orthotics, and tools for healthy hands and feet.', 'sort_order' => 11],
				['name' => 'Eye & Ear Care', 'slug' => 'eye-ear-care', 'parent_id' => 19, 'description' => 'Eyewash, ear care solutions, and vision accessories.', 'sort_order' => 12],
				['name' => 'Alternative Medicine', 'slug' => 'alternative-medicine', 'parent_id' => 19, 'description' => 'Herbal remedies, homeopathy, and natural health products.', 'sort_order' => 13],
				['name' => 'Personal Protection', 'slug' => 'personal-protection', 'parent_id' => 19, 'description' => 'Masks, sanitizers, gloves, and other protective equipment.', 'sort_order' => 14],
				['name' => 'Spa & Self-Care', 'slug' => 'spa-self-care', 'parent_id' => 19, 'description' => 'Bath salts, essential oils, massage tools, and wellness kits.', 'sort_order' => 15],
				['name' => 'Fitness Apparel', 'slug' => 'fitness-apparel', 'parent_id' => 19, 'description' => 'Clothing, footwear, and accessories for workouts and sports.', 'sort_order' => 16],
				['name' => 'Hydration & Water Bottles', 'slug' => 'hydration-water-bottles', 'parent_id' => 19, 'description' => 'Reusable bottles, hydration packs, and water filters.', 'sort_order' => 17],
				['name' => 'Heart & Circulation', 'slug' => 'heart-circulation', 'parent_id' => 19, 'description' => 'Devices and supplements for cardiovascular health.', 'sort_order' => 18],
				['name' => 'Respiratory Health', 'slug' => 'respiratory-health', 'parent_id' => 19, 'description' => 'Inhalers, humidifiers, and other respiratory support tools.', 'sort_order' => 19],
				['name' => 'Pain Relief', 'slug' => 'pain-relief', 'parent_id' => 19, 'description' => 'Topical creams, heat packs, and supplements for pain management.', 'sort_order' => 20],

				// 20. Food & Beverages
				['name' => 'Suitcases & Luggage', 'slug' => 'suitcases-luggage', 'parent_id' => 20, 'description' => 'Hard-shell and soft-shell suitcases, carry-ons, and travel sets.', 'sort_order' => 1],
				['name' => 'Backpacks & Daypacks', 'slug' => 'backpacks-daypacks', 'parent_id' => 20, 'description' => 'Travel backpacks, hiking packs, and daily-use bags.', 'sort_order' => 2],
				['name' => 'Travel Accessories', 'slug' => 'travel-accessories', 'parent_id' => 20, 'description' => 'Neck pillows, luggage tags, passport holders, and travel organizers.', 'sort_order' => 3],
				['name' => 'Travel Bags & Duffels', 'slug' => 'travel-bags-duffels', 'parent_id' => 20, 'description' => 'Duffel bags, weekenders, and gym bags suitable for travel.', 'sort_order' => 4],
				['name' => 'Luggage Protection & Covers', 'slug' => 'luggage-protection-covers', 'parent_id' => 20, 'description' => 'Protective covers, locks, and luggage belts to safeguard bags.', 'sort_order' => 5],
				['name' => 'Packing Cubes & Organizers', 'slug' => 'packing-cubes-organizers', 'parent_id' => 20, 'description' => 'Efficient packing cubes and organizers for better luggage management.', 'sort_order' => 6],
				['name' => 'Travel Toiletries & Kits', 'slug' => 'travel-toiletries-kits', 'parent_id' => 20, 'description' => 'Portable toiletry bags, travel-sized hygiene kits, and containers.', 'sort_order' => 7],
				['name' => 'Travel Shoes & Apparel', 'slug' => 'travel-shoes-apparel', 'parent_id' => 20, 'description' => 'Comfortable clothing, footwear, and travel-friendly attire.', 'sort_order' => 8],
				['name' => 'Electronics & Travel Gadgets', 'slug' => 'electronics-travel-gadgets', 'parent_id' => 20, 'description' => 'Portable chargers, adapters, headphones, and travel gadgets.', 'sort_order' => 9],
				['name' => 'Travel Security', 'slug' => 'travel-security', 'parent_id' => 20, 'description' => 'Anti-theft bags, locks, RFID wallets, and personal security items.', 'sort_order' => 10],
				['name' => 'Camping & Adventure Travel', 'slug' => 'camping-adventure-travel', 'parent_id' => 20, 'description' => 'Tents, sleeping bags, and gear for outdoor and adventure trips.', 'sort_order' => 11],
				['name' => 'Travel Guides & Maps', 'slug' => 'travel-guides-maps', 'parent_id' => 20, 'description' => 'Books, maps, and guides to help plan your trips.', 'sort_order' => 12],
				['name' => 'Luggage Wheels & Handles', 'slug' => 'luggage-wheels-handles', 'parent_id' => 20, 'description' => 'Replacement wheels, handles, and repair accessories for luggage.', 'sort_order' => 13],
				['name' => 'Travel Clothing Storage', 'slug' => 'travel-clothing-storage', 'parent_id' => 20, 'description' => 'Garment bags, foldable hangers, and travel clothing organizers.', 'sort_order' => 14],
				['name' => 'Travel Snacks & Drinks', 'slug' => 'travel-snacks-drinks', 'parent_id' => 20, 'description' => 'Convenient snacks, water bottles, and travel-friendly drinks.', 'sort_order' => 15],
				['name' => 'Travel Health & Safety', 'slug' => 'travel-health-safety', 'parent_id' => 20, 'description' => 'Travel first aid kits, sanitizers, and health essentials.', 'sort_order' => 16],
				['name' => 'Kids Travel Gear', 'slug' => 'kids-travel-gear', 'parent_id' => 20, 'description' => 'Strollers, travel seats, and luggage for children.', 'sort_order' => 17],
				['name' => 'Luxury Travel', 'slug' => 'luxury-travel', 'parent_id' => 20, 'description' => 'High-end luggage, accessories, and premium travel items.', 'sort_order' => 18],
				['name' => 'Eco-Friendly Travel', 'slug' => 'eco-friendly-travel', 'parent_id' => 20, 'description' => 'Sustainable luggage, reusable bottles, and environmentally conscious gear.', 'sort_order' => 19],
				['name' => 'Travel Electronics Cases', 'slug' => 'travel-electronics-cases', 'parent_id' => 20, 'description' => 'Protective cases for laptops, cameras, tablets, and electronics.', 'sort_order' => 20],
			];

			foreach ($childCategories as $child) {
				DB::table('categories')->insert($child + [
						'is_active'  => true,
						'created_at' => Carbon::now()->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
						'updated_at' => Carbon::now()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59))
					]);
			}
		}
	}
