<?php

	return [
		// ==========================================
		// MEN'S SPORTS CLOTHING
		// ==========================================
		[
			'name'           => 'Nike',
			'slug'           => 'nike-men-sports',
			'description'    => 'World-renowned athletic footwear, functional activewear, and performance gear.',
			'logo'           => 'nike.png',
			'website'        => 'https://www.nike.com',
			'is_active'      => true,
			'category_slugs' => [
				'men-sports',
				'mens-sports-tanks',
				'mens-sports-shorts',
				'mens-sports-jackets',
				'mens-compression',
				'mens-sports-socks',
				'mens-swimwear-sports',
			],
		],
		[
			'name'           => 'Adidas',
			'slug'           => 'adidas-men-sports',
			'description'    => 'Global sportswear icon producing premium casual and athletic components.',
			'logo'           => 'adidas.png',
			'website'        => 'https://www.adidas.com',
			'is_active'      => true,
			'category_slugs' => [
				'men-sports',
				'mens-sports-tanks',
				'mens-compression',
				'mens-sports-shorts',
				'mens-sports-jackets',
				'mens-sports-socks',
			],
		],
		[
			'name'           => 'Under Armour',
			'slug'           => 'under-armour-men',
			'description'    => 'American sports equipment company that manufactures high-performance footwear, sports, and casual apparel.',
			'logo'           => 'under-armour.png',
			'website'        => 'https://www.underarmour.com',
			'is_active'      => true,
			'category_slugs' => [
				'men-sports',
				'mens-compression',
				'mens-sports-jackets',
				'mens-sports-tanks',
				'mens-sports-shorts',
			],
		],
		[
			'name'           => 'Puma',
			'slug'           => 'puma-men',
			'description'    => 'German multinational corporation that designs and manufactures athletic and casual footwear, apparel, and accessories.',
			'logo'           => 'puma.png',
			'website'        => 'https://about.puma.com',
			'is_active'      => true,
			'category_slugs' => [
				'men-sports',
				'mens-sports-shorts',
				'mens-swimwear-sports',
			],
		],
		[
			'name'           => 'New Balance',
			'slug'           => 'new-balance-men',
			'description'    => 'Premium athletic footwear and sports apparel for performance and lifestyle.',
			'logo'           => 'new-balance.png',
			'website'        => 'https://www.newbalance.com',
			'is_active'      => true,
			'category_slugs' => [
				'men-sports',
				'mens-sports-tanks',
				'athletic-footwear',
				'mens-athletic-shoes',
			],
		],

		// ==========================================
		// WOMEN'S SPORTS CLOTHING
		// ==========================================
		[
			'name'           => 'Lululemon',
			'slug'           => 'lululemon-women',
			'description'    => 'Premium athletic apparel brand specialized in yoga pants, leggings, and sportswear.',
			'logo'           => 'lululemon.png',
			'website'        => 'https://www.lululemon.com',
			'is_active'      => true,
			'category_slugs' => [
				'women-sports',
				'yoga-pants',
				'womens-sports-tanks',
				'sports-bras',
				'womens-sports-shorts',
				'womens-sports-jackets',
			],
		],
		[
			'name'           => 'Gymshark',
			'slug'           => 'gymshark',
			'description'    => 'Modern athletic apparel brand focused on fitness and gym wear.',
			'logo'           => 'gymshark.png',
			'website'        => 'https://gymshark.com',
			'is_active'      => true,
			'category_slugs' => [
				'women-sports',
				'yoga-pants',
				'womens-sports-shorts',
				'womens-sports-tanks',
			],
		],

		// ==========================================
		// ATHLETIC FOOTWEAR
		// ==========================================
		[
			'name'           => 'Brooks',
			'slug'           => 'brooks',
			'description'    => 'Specialist in premium running shoes and athletic footwear.',
			'logo'           => 'brooks.png',
			'website'        => 'https://www.brooksrunning.com',
			'is_active'      => true,
			'category_slugs' => [
				'athletic-footwear',
				'running-athletics',
				'mens-athletic-shoes',
				'womens-athletic-shoes',
			],
		],
		[
			'name'           => 'Hoka One One',
			'slug'           => 'hoka',
			'description'    => 'Innovative running shoes known for maximum cushioning and support.',
			'logo'           => 'hoka.png',
			'website'        => 'https://www.hoka.com',
			'is_active'      => true,
			'category_slugs' => [
				'athletic-footwear',
				'running-athletics',
				'mens-athletic-shoes',
				'womens-athletic-shoes',
			],
		],

		// ==========================================
		// SPORTS BAGS
		// ==========================================
		[
			'name'           => 'Osprey',
			'slug'           => 'osprey-bags',
			'description'    => 'Industry leader in high-performance travel packs, rugged backpacks, and wheeled adventure gear built for global exploration.',
			'logo'           => 'osprey.png',
			'website'        => 'https://www.osprey.com',
			'is_active'      => true,
			'category_slugs' => [
				'sports-bags',
				'hiking-backpacks',
				'sports-backpacks',
			],
		],
		[
			'name'           => 'The North Face',
			'slug'           => 'tnf-bags',
			'description'    => 'American outdoor recreation product company specializing in premium outerwear, fleece, coats, and specialized equipment.',
			'logo'           => 'north-face.png',
			'website'        => 'https://www.thenorthface.com',
			'is_active'      => true,
			'category_slugs' => [
				'sports-bags',
				'tactical-bags',
				'hiking-backpacks',
				'sports-backpacks',
			],
		],

		// ==========================================
		// FITNESS EQUIPMENT
		// ==========================================
		[
			'name'           => 'Bowflex',
			'slug'           => 'bowflex',
			'description'    => 'Premium home fitness equipment including dumbbells and home gyms.',
			'logo'           => 'bowflex.png',
			'website'        => 'https://www.bowflex.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-equipment',
				'free-weights',
				'home-gym-systems',
			],
		],
		[
			'name'           => 'Peloton',
			'slug'           => 'peloton',
			'description'    => 'Interactive fitness platform with bikes, treadmills, and classes.',
			'logo'           => 'peloton.png',
			'website'        => 'https://www.onepeloton.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-equipment',
				'cardio-equipment',
			],
		],
		[
			'name'           => 'NordicTrack',
			'slug'           => 'nordictrack',
			'description'    => 'Premium treadmills, ellipticals, and home gym systems.',
			'logo'           => 'nordictrack.png',
			'website'        => 'https://www.nordictrack.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-equipment',
				'cardio-equipment',
			],
		],
		[
			'name'           => 'Rogue Fitness',
			'slug'           => 'rogue-fitness',
			'description'    => 'Premium strength and conditioning equipment for athletes.',
			'logo'           => 'rogue-fitness.png',
			'website'        => 'https://www.roguefitness.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-equipment',
				'barbells-benches',
				'free-weights',
				'home-gym-systems',
			],
		],
		[
			'name'           => 'Technogym',
			'slug'           => 'technogym',
			'description'    => 'Premium fitness equipment and home gym systems.',
			'logo'           => 'technogym.png',
			'website'        => 'https://www.technogym.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-equipment',
				'cardio-equipment',
				'home-gym-systems',
			],
		],

		// ==========================================
		// YOGA & EXERCISE MATS
		// ==========================================
		[
			'name'           => 'Manduka',
			'slug'           => 'manduka',
			'description'    => 'Premium yoga mats and yoga accessories.',
			'logo'           => 'manduka.png',
			'website'        => 'https://www.manduka.com',
			'is_active'      => true,
			'category_slugs' => [
				'exercise-mats',
				'yoga-pants',
			],
		],
		[
			'name'           => 'Gaiam',
			'slug'           => 'gaiam',
			'description'    => 'Trusted brand for yoga mats and wellness products.',
			'logo'           => 'gaiam.png',
			'website'        => 'https://www.gaiam.com',
			'is_active'      => true,
			'category_slugs' => [
				'exercise-mats',
				'stability-balls',
			],
		],

		// ==========================================
		// SPORTS EQUIPMENT - Basketball
		// ==========================================
		[
			'name'           => 'Spalding',
			'slug'           => 'spalding',
			'description'    => 'Official basketball brand for the NBA and quality sports equipment.',
			'logo'           => 'spalding.png',
			'website'        => 'https://www.spalding.com',
			'is_active'      => true,
			'category_slugs' => [
				'basketball-gear',
			],
		],
		[
			'name'           => 'Wilson',
			'slug'           => 'wilson',
			'description'    => 'Premium sports equipment for basketball, tennis, and golf.',
			'logo'           => 'wilson.png',
			'website'        => 'https://www.wilson.com',
			'is_active'      => true,
			'category_slugs' => [
				'basketball-gear',
				'tennis-racquet-sports',
				'golf-equipment',
				'baseball-softball',
			],
		],
		[
			'name'           => 'Lifetime',
			'slug'           => 'lifetime-sports',
			'description'    => 'Durable sports equipment and outdoor gear.',
			'logo'           => 'lifetime-sports.png',
			'website'        => 'https://www.lifetime.com',
			'is_active'      => true,
			'category_slugs' => [
				'basketball-gear',
			],
		],

		// ==========================================
		// SPORTS EQUIPMENT - Soccer & Football
		// ==========================================
		[
			'name'           => 'Adidas',
			'slug'           => 'adidas-soccer',
			'description'    => 'Global sportswear icon producing premium casual and athletic components.',
			'logo'           => 'adidas.png',
			'website'        => 'https://www.adidas.com',
			'is_active'      => true,
			'category_slugs' => [
				'soccer-gear',
				'cleats-sports-shoes',
			],
		],
		[
			'name'           => 'Nike',
			'slug'           => 'nike-soccer',
			'description'    => 'World-renowned athletic footwear, functional activewear, and performance gear.',
			'logo'           => 'nike.png',
			'website'        => 'https://www.nike.com',
			'is_active'      => true,
			'category_slugs' => [
				'soccer-gear',
				'cleats-sports-shoes',
				'football-gear',
			],
		],

		// ==========================================
		// GOLF EQUIPMENT
		// ==========================================
		[
			'name'           => 'Titleist',
			'slug'           => 'titleist',
			'description'    => 'Premium golf balls, clubs, and equipment.',
			'logo'           => 'titleist.png',
			'website'        => 'https://www.titleist.com',
			'is_active'      => true,
			'category_slugs' => [
				'golf-equipment',
			],
		],
		[
			'name'           => 'Callaway',
			'slug'           => 'callaway',
			'description'    => 'Innovative golf clubs, balls, and accessories.',
			'logo'           => 'callaway.png',
			'website'        => 'https://www.callawaygolf.com',
			'is_active'      => true,
			'category_slugs' => [
				'golf-equipment',
			],
		],
		[
			'name'           => 'TaylorMade',
			'slug'           => 'taylormade',
			'description'    => 'Premium golf equipment and accessories.',
			'logo'           => 'taylormade.png',
			'website'        => 'https://www.taylormadegolf.com',
			'is_active'      => true,
			'category_slugs' => [
				'golf-equipment',
			],
		],

		// ==========================================
		// CAMPING & HIKING - Tents & Shelters
		// ==========================================
		[
			'name'           => 'REI Co-op',
			'slug'           => 'rei',
			'description'    => 'Outdoor retailer offering premium camping and hiking gear.',
			'logo'           => 'rei.png',
			'website'        => 'https://www.rei.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-gear',
				'tents-shelters',
				'hiking-backpacks',
				'sleeping-bags-pads',
			],
		],
		[
			'name'           => 'Coleman',
			'slug'           => 'coleman',
			'description'    => 'Trusted camping brand for tents, sleeping bags, and outdoor gear.',
			'logo'           => 'coleman.png',
			'website'        => 'https://www.coleman.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-gear',
				'tents-shelters',
				'camping-cooking',
				'camping-furniture',
			],
		],
		[
			'name'           => 'MSR',
			'slug'           => 'msr',
			'description'    => 'Premium outdoor gear and camping equipment.',
			'logo'           => 'msr.png',
			'website'        => 'https://www.msrgear.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-gear',
				'tents-shelters',
				'camping-cooking',
			],
		],
		[
			'name'           => 'Marmot',
			'slug'           => 'marmot',
			'description'    => 'Premium outdoor and camping gear.',
			'logo'           => 'marmot.png',
			'website'        => 'https://www.marmot.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-gear',
				'sleeping-bags-pads',
			],
		],

		// ==========================================
		// HIKING BACKPACKS
		// ==========================================
		[
			'name'           => 'Deuter',
			'slug'           => 'deuter',
			'description'    => 'Premium hiking backpacks and outdoor gear.',
			'logo'           => 'deuter.png',
			'website'        => 'https://www.deuter.com',
			'is_active'      => true,
			'category_slugs' => [
				'hiking-backpacks',
				'sports-backpacks',
			],
		],
		[
			'name'           => 'Gregory',
			'slug'           => 'gregory',
			'description'    => 'High-performance backpacks for hiking and travel.',
			'logo'           => 'gregory.png',
			'website'        => 'https://www.gregorypacks.com',
			'is_active'      => true,
			'category_slugs' => [
				'hiking-backpacks',
				'sports-backpacks',
			],
		],

		// ==========================================
		// CAMPING FURNITURE & COOKING
		// ==========================================
		[
			'name'           => 'Helinox',
			'slug'           => 'helinox',
			'description'    => 'Lightweight camping furniture and chairs.',
			'logo'           => 'helinox.png',
			'website'        => 'https://www.helinox.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-furniture',
			],
		],
		[
			'name'           => 'Jetboil',
			'slug'           => 'jetboil',
			'description'    => 'Premium camping cooking systems and stoves.',
			'logo'           => 'jetboil.png',
			'website'        => 'https://www.jetboil.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-cooking',
			],
		],
		[
			'name'           => 'GSI Outdoors',
			'slug'           => 'gsi-outdoors',
			'description'    => 'Camping cookware and outdoor kitchen equipment.',
			'logo'           => 'gsi-outdoors.png',
			'website'        => 'https://www.gsioutdoors.com',
			'is_active'      => true,
			'category_slugs' => [
				'camping-cooking',
			],
		],

		// ==========================================
		// CLIMBING GEAR
		// ==========================================
		[
			'name'           => 'Black Diamond',
			'slug'           => 'black-diamond',
			'description'    => 'Premium climbing equipment, outdoor gear, and headlamps.',
			'logo'           => 'black-diamond.png',
			'website'        => 'https://www.blackdiamondequipment.com',
			'is_active'      => true,
			'category_slugs' => [
				'climbing-gear',
				'safety-rope-gear',
				'flashlights-headlamps',
				'crampons-ice-gear',
			],
		],
		[
			'name'           => 'Petzl',
			'slug'           => 'petzl',
			'description'    => 'Premium climbing equipment, safety gear, and headlamps.',
			'logo'           => 'petzl.png',
			'website'        => 'https://www.petzl.com',
			'is_active'      => true,
			'category_slugs' => [
				'climbing-gear',
				'safety-rope-gear',
				'flashlights-headlamps',
			],
		],
		[
			'name'           => 'Mammut',
			'slug'           => 'mammut',
			'description'    => 'Premium climbing gear, ropes, and safety equipment.',
			'logo'           => 'mammut.png',
			'website'        => 'https://www.mammut.com',
			'is_active'      => true,
			'category_slugs' => [
				'climbing-gear',
				'safety-rope-gear',
			],
		],

		// ==========================================
		// WATER SPORTS
		// ==========================================
		[
			'name'           => 'Speedo',
			'slug'           => 'speedo',
			'description'    => 'World leader in swimwear and swim accessories for men, women, and children.',
			'logo'           => 'speedo.png',
			'website'        => 'https://www.speedo.com',
			'is_active'      => true,
			'category_slugs' => [
				'swimming-water-sports',
				'mens-swimwear-sports',
				'womens-swimwear-sports',
			],
		],
		[
			'name'           => 'Cressi',
			'slug'           => 'cressi',
			'description'    => 'Premium scuba diving and snorkeling equipment.',
			'logo'           => 'cressi.png',
			'website'        => 'https://www.cressi.com',
			'is_active'      => true,
			'category_slugs' => [
				'snorkeling-scuba',
				'swimming-water-sports',
			],
		],
		[
			'name'           => 'Aqua Lung',
			'slug'           => 'aqua-lung',
			'description'    => 'Professional scuba diving and snorkeling equipment.',
			'logo'           => 'aqua-lung.png',
			'website'        => 'https://www.aqualung.com',
			'is_active'      => true,
			'category_slugs' => [
				'snorkeling-scuba',
			],
		],

		// ==========================================
		// WINTER SPORTS
		// ==========================================
		[
			'name'           => 'Atomic',
			'slug'           => 'atomic',
			'description'    => 'Premium skis and winter sports equipment.',
			'logo'           => 'atomic.png',
			'website'        => 'https://www.atomicski.com',
			'is_active'      => true,
			'category_slugs' => [
				'skiing-snowboarding',
			],
		],
		[
			'name'           => 'Salomon',
			'slug'           => 'salomon',
			'description'    => 'Premium winter sports equipment, skis, and snowboards.',
			'logo'           => 'salomon.png',
			'website'        => 'https://www.salomon.com',
			'is_active'      => true,
			'category_slugs' => [
				'skiing-snowboarding',
				'winter-sports-clothing',
				'winter-sports-accessories',
			],
		],
		[
			'name'           => 'Burton',
			'slug'           => 'burton',
			'description'    => 'Premium snowboarding equipment and gear.',
			'logo'           => 'burton.png',
			'website'        => 'https://www.burton.com',
			'is_active'      => true,
			'category_slugs' => [
				'skiing-snowboarding',
				'winter-sports-accessories',
				'winter-sports-clothing',
			],
		],

		// ==========================================
		// BICYCLING & CYCLING
		// ==========================================
		[
			'name'           => 'Trek',
			'slug'           => 'trek',
			'description'    => 'Premium bicycles and cycling accessories.',
			'logo'           => 'trek.png',
			'website'        => 'https://www.trekbikes.com',
			'is_active'      => true,
			'category_slugs' => [
				'bicycling-cycling',
			],
		],
		[
			'name'           => 'Specialized',
			'slug'           => 'specialized',
			'description'    => 'Premium bikes and cycling equipment.',
			'logo'           => 'specialized.png',
			'website'        => 'https://www.specialized.com',
			'is_active'      => true,
			'category_slugs' => [
				'bicycling-cycling',
			],
		],
		[
			'name'           => 'Giro',
			'slug'           => 'giro',
			'description'    => 'Premium cycling helmets and accessories.',
			'logo'           => 'giro.png',
			'website'        => 'https://www.giro.com',
			'is_active'      => true,
			'category_slugs' => [
				'bicycling-cycling',
				'outdoor-hats',
			],
		],

		// ==========================================
		// SKATEBOARDING & SCOOTERS
		// ==========================================
		[
			'name'           => 'Santa Cruz',
			'slug'           => 'santa-cruz',
			'description'    => 'Iconic skateboard brand and apparel.',
			'logo'           => 'santa-cruz.png',
			'website'        => 'https://www.santacruzskateboards.com',
			'is_active'      => true,
			'category_slugs' => [
				'skateboarding-scooters',
			],
		],
		[
			'name'           => 'Razor',
			'slug'           => 'razor',
			'description'    => 'Popular scooters and ride-on toys.',
			'logo'           => 'razor.png',
			'website'        => 'https://www.razor.com',
			'is_active'      => true,
			'category_slugs' => [
				'skateboarding-scooters',
			],
		],
		[
			'name'           => 'Micro Mobility',
			'slug'           => 'micro',
			'description'    => 'Premium scooters and kickboards.',
			'logo'           => 'micro.png',
			'website'        => 'https://www.micro-mobility.com',
			'is_active'      => true,
			'category_slugs' => [
				'skateboarding-scooters',
			],
		],

		// ==========================================
		// OUTDOOR ACCESSORIES - Flashlights & Headlamps
		// ==========================================
		[
			'name'           => 'Fenix',
			'slug'           => 'fenix',
			'description'    => 'Premium flashlights and headlamps.',
			'logo'           => 'fenix.png',
			'website'        => 'https://www.fenixlighting.com',
			'is_active'      => true,
			'category_slugs' => [
				'flashlights-headlamps',
			],
		],

		// ==========================================
		// OUTDOOR KNIVES & TOOLS
		// ==========================================
		[
			'name'           => 'Victorinox',
			'slug'           => 'victorinox-outdoor',
			'description'    => 'Swiss brand known for quality knives and Swiss Army knives.',
			'logo'           => 'victorinox.png',
			'website'        => 'https://www.victorinox.com',
			'is_active'      => true,
			'category_slugs' => [
				'outdoor-knives',
				'survival-kits',
			],
		],
		[
			'name'           => 'Benchmade',
			'slug'           => 'benchmade',
			'description'    => 'Premium outdoor and tactical knives.',
			'logo'           => 'benchmade.png',
			'website'        => 'https://www.benchmade.com',
			'is_active'      => true,
			'category_slugs' => [
				'outdoor-knives',
			],
		],
		[
			'name'           => 'Leatherman',
			'slug'           => 'leatherman',
			'description'    => 'Premium multi-tools and outdoor tools.',
			'logo'           => 'leatherman.png',
			'website'        => 'https://www.leatherman.com',
			'is_active'      => true,
			'category_slugs' => [
				'outdoor-knives',
				'survival-kits',
			],
		],

		// ==========================================
		// FITNESS TRACKERS & WEARABLES
		// ==========================================
		[
			'name'           => 'Fitbit',
			'slug'           => 'fitbit-sports',
			'description'    => 'Pioneer in fitness trackers, health monitors, and wearable wellness technology.',
			'logo'           => 'fitbit.png',
			'website'        => 'https://www.fitbit.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-trackers-sports',
				'wearable-sports-tech',
			],
		],
		[
			'name'           => 'Garmin',
			'slug'           => 'garmin-fitness',
			'description'    => 'Specialist in GPS navigation, wearable fitness technology, and smartwatches.',
			'logo'           => 'garmin.png',
			'website'        => 'https://www.garmin.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-trackers-sports',
				'wearable-sports-tech',
				'running-athletics',
				'compasses-gps',
			],
		],
		[
			'name'           => 'Whoop',
			'slug'           => 'whoop-sports',
			'description'    => 'Advanced fitness and health tracking wearable for athletes.',
			'logo'           => 'whoop.png',
			'website'        => 'https://www.whoop.com',
			'is_active'      => true,
			'category_slugs' => [
				'fitness-trackers-sports',
				'wearable-sports-tech',
			],
		],

		// ==========================================
		// ADDITIONAL BRANDS
		// ==========================================

		// Running Belts
		[
			'name'           => 'Nathan',
			'slug'           => 'nathan',
			'description'    => 'Premium running belts, hydration packs, and race gear.',
			'logo'           => 'nathan.png',
			'website'        => 'https://www.nathansports.com',
			'is_active'      => true,
			'category_slugs' => [
				'running-belts',
				'hydration-bottles',
				'running-athletics',
			],
		],
		[
			'name'           => 'Amphipod',
			'slug'           => 'amphipod',
			'description'    => 'Running belts, hydration packs, and performance gear.',
			'logo'           => 'amphipod.png',
			'website'        => 'https://www.amphipod.com',
			'is_active'      => true,
			'category_slugs' => [
				'running-belts',
				'hydration-bottles',
			],
		],

		// Outdoor Sunglasses
		[
			'name'           => 'Oakley',
			'slug'           => 'oakley',
			'description'    => 'Premium sports sunglasses and performance eyewear.',
			'logo'           => 'oakley.png',
			'website'        => 'https://www.oakley.com',
			'is_active'      => true,
			'category_slugs' => [
				'outdoor-sunglasses',
				'outdoor-hats',
			],
		],
		[
			'name'           => 'Smith Optics',
			'slug'           => 'smith-optics',
			'description'    => 'Premium sports sunglasses, goggles, and winter sports eyewear.',
			'logo'           => 'smith-optics.png',
			'website'        => 'https://www.smithoptics.com',
			'is_active'      => true,
			'category_slugs' => [
				'outdoor-sunglasses',
				'winter-sports-accessories',
			],
		],
	];