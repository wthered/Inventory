<?php

	return [
		// ==========================================
		// LAPTOPS & DESKTOPS
		// ==========================================

		// Laptops
		[
			'name' => 'Apple',
			'slug' => 'apple',
			'description' => 'Premium laptops, desktops, and devices with macOS, known for design and performance.',
			'logo' => 'apple.png',
			'website' => 'https://www.apple.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'smartphones',
				'ios-smartphones',
				'smartwatches',
				'gaming-desktops',
				'gaming-laptops',
			],
		],
		[
			'name' => 'Dell',
			'slug' => 'dell',
			'description' => 'Global leader in laptops, desktops, and enterprise solutions.',
			'logo' => 'dell.png',
			'website' => 'https://www.dell.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'gaming-laptops',
				'gaming-desktops',
				'monitors',
				'workstations-servers',
			],
		],
		[
			'name' => 'HP',
			'slug' => 'hp',
			'description' => 'Leading manufacturer of laptops, desktops, printers, and enterprise hardware.',
			'logo' => 'hp.png',
			'website' => 'https://www.hp.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'gaming-laptops',
				'gaming-desktops',
				'monitors',
				'printers-scanners',
				'workstations-servers',
			],
		],
		[
			'name' => 'Lenovo',
			'slug' => 'lenovo',
			'description' => 'World\'s largest PC manufacturer, offering ThinkPad, Yoga, and Legion series.',
			'logo' => 'lenovo.png',
			'website' => 'https://www.lenovo.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'gaming-laptops',
				'gaming-desktops',
				'monitors',
				'workstations-servers',
			],
		],
		[
			'name' => 'Asus',
			'slug' => 'asus',
			'description' => 'Taiwanese tech giant known for gaming laptops, motherboards, and monitors.',
			'logo' => 'asus.png',
			'website' => 'https://www.asus.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'gaming-laptops',
				'gaming-desktops',
				'gaming-monitors',
				'motherboards',
				'graphics-cards',
				'monitors',
			],
		],
		[
			'name' => 'Acer',
			'slug' => 'acer',
			'description' => 'Affordable laptops, desktops, and gaming PCs with reliable performance.',
			'logo' => 'acer.png',
			'website' => 'https://www.acer.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'desktops',
				'gaming-laptops',
				'gaming-desktops',
				'monitors',
				'chromebooks-cloud',
			],
		],
		[
			'name' => 'Microsoft',
			'slug' => 'microsoft',
			'description' => 'Software giant and creator of Surface laptops, tablets, and operating systems.',
			'logo' => 'microsoft.png',
			'website' => 'https://www.microsoft.com',
			'is_active' => true,
			'category_slugs' => [
				'laptops-macbooks',
				'operating-systems',
				'gaming-desktops',
				'gaming-laptops',
			],
		],
		[
			'name' => 'Razer',
			'slug' => 'razer',
			'description' => 'Premium gaming laptops, peripherals, and gaming hardware.',
			'logo' => 'razer.png',
			'website' => 'https://www.razer.com',
			'is_active' => true,
			'category_slugs' => [
				'gaming-laptops',
				'gaming-desktops',
				'gaming-keyboards',
				'gaming-mice',
				'gaming-headsets',
				'gaming-monitors',
				'gaming-cases',
				'gaming-psu',
			],
		],

		// ==========================================
		// COMPUTER COMPONENTS
		// ==========================================

		// Processors & Motherboards
		[
			'name' => 'Intel',
			'slug' => 'intel',
			'description' => 'World leader in processors, CPUs, and semiconductor technology.',
			'logo' => 'intel.png',
			'website' => 'https://www.intel.com',
			'is_active' => true,
			'category_slugs' => [
				'processors-cpu',
				'gaming-processors',
			],
		],
		[
			'name' => 'AMD',
			'slug' => 'amd',
			'description' => 'Leading manufacturer of CPUs, GPUs, and gaming processors.',
			'logo' => 'amd.png',
			'website' => 'https://www.amd.com',
			'is_active' => true,
			'category_slugs' => [
				'processors-cpu',
				'graphics-cards',
				'gaming-processors',
				'gaming-gpus',
			],
		],
		[
			'name' => 'NVIDIA',
			'slug' => 'nvidia',
			'description' => 'Pioneer in graphics cards, GPUs, and AI computing.',
			'logo' => 'nvidia.png',
			'website' => 'https://www.nvidia.com',
			'is_active' => true,
			'category_slugs' => [
				'graphics-cards',
				'gaming-gpus',
			],
		],
		[
			'name' => 'MSI',
			'slug' => 'msi',
			'description' => 'Premium gaming motherboards, graphics cards, and gaming laptops.',
			'logo' => 'msi.png',
			'website' => 'https://www.msi.com',
			'is_active' => true,
			'category_slugs' => [
				'motherboards',
				'graphics-cards',
				'gaming-motherboards',
				'gaming-laptops',
				'gaming-monitors',
				'gaming-cases',
			],
		],
		[
			'name' => 'Gigabyte',
			'slug' => 'gigabyte',
			'description' => 'Leading manufacturer of motherboards, graphics cards, and PC components.',
			'logo' => 'gigabyte.png',
			'website' => 'https://www.gigabyte.com',
			'is_active' => true,
			'category_slugs' => [
				'motherboards',
				'graphics-cards',
				'gaming-motherboards',
				'gaming-gpus',
				'computer-cases',
				'gaming-cases',
			],
		],

		// Memory & Storage
		[
			'name' => 'Corsair',
			'slug' => 'corsair',
			'description' => 'Premium PC components including RAM, power supplies, and gaming peripherals.',
			'logo' => 'corsair.png',
			'website' => 'https://www.corsair.com',
			'is_active' => true,
			'category_slugs' => [
				'ram-memory',
				'gaming-ram',
				'power-supplies',
				'gaming-psu',
				'computer-cases',
				'gaming-cases',
				'computer-cooling-systems',
				'gaming-cooling',
				'external-storage',
			],
		],
		[
			'name' => 'Kingston',
			'slug' => 'kingston',
			'description' => 'World leader in memory products, SSDs, and USB drives.',
			'logo' => 'kingston.png',
			'website' => 'https://www.kingston.com',
			'is_active' => true,
			'category_slugs' => [
				'ram-memory',
				'external-storage',
				'internal-storage',
				'gaming-ram',
				'gaming-storage',
			],
		],
		[
			'name' => 'Samsung Storage',
			'slug' => 'samsung-storage',
			'description' => 'Premium SSDs, external drives, and memory products.',
			'logo' => 'samsung-storage.png',
			'website' => 'https://www.samsung.com',
			'is_active' => true,
			'category_slugs' => [
				'external-storage',
				'internal-storage',
				'gaming-storage',
				'ram-memory',
			],
		],
		[
			'name' => 'Western Digital',
			'slug' => 'western-digital',
			'description' => 'Leading manufacturer of hard drives, SSDs, and external storage.',
			'logo' => 'western-digital.png',
			'website' => 'https://www.westerndigital.com',
			'is_active' => true,
			'category_slugs' => [
				'external-storage',
				'internal-storage',
				'gaming-storage',
				'nas-devices',
			],
		],
		[
			'name' => 'Seagate',
			'slug' => 'seagate',
			'description' => 'Global leader in data storage solutions, HDDs, and SSDs.',
			'logo' => 'seagate.png',
			'website' => 'https://www.seagate.com',
			'is_active' => true,
			'category_slugs' => [
				'external-storage',
				'internal-storage',
				'gaming-storage',
				'nas-devices',
			],
		],

		// Power Supplies & Cooling
		[
			'name' => 'Cooler Master',
			'slug' => 'cooler-master',
			'description' => 'Premium PC cooling solutions, cases, and power supplies.',
			'logo' => 'cooler-master.png',
			'website' => 'https://www.coolermaster.com',
			'is_active' => true,
			'category_slugs' => [
				'computer-cooling-systems',
				'gaming-cooling',
				'computer-cases',
				'gaming-cases',
				'power-supplies',
				'gaming-psu',
			],
		],
		[
			'name' => 'Noctua',
			'slug' => 'noctua',
			'description' => 'Premium PC cooling fans, heatsinks, and silent cooling solutions.',
			'logo' => 'noctua.png',
			'website' => 'https://noctua.at',
			'is_active' => true,
			'category_slugs' => [
				'computer-cooling-systems',
				'gaming-cooling',
			],
		],

		// ==========================================
		// MONITORS & DISPLAYS
		// ==========================================
		[
			'name' => 'Dell Monitors',
			'slug' => 'dell-monitors',
			'description' => 'Premium monitors for business, gaming, and professional use.',
			'logo' => 'dell-monitors.png',
			'website' => 'https://www.dell.com',
			'is_active' => true,
			'category_slugs' => [
				'monitors',
				'gaming-monitors',
				'portable-monitors',
			],
		],
		[
			'name' => 'LG',
			'slug' => 'lg',
			'description' => 'Leading manufacturer of monitors, TVs, and display technology.',
			'logo' => 'lg.png',
			'website' => 'https://www.lg.com',
			'is_active' => true,
			'category_slugs' => [
				'monitors',
				'gaming-monitors',
				'portable-monitors',
				'curved-monitors',
			],
		],
		[
			'name' => 'Samsung Displays',
			'slug' => 'samsung-displays',
			'description' => 'Premium monitors, curved displays, and gaming monitors.',
			'logo' => 'samsung-displays.png',
			'website' => 'https://www.samsung.com',
			'is_active' => true,
			'category_slugs' => [
				'monitors',
				'gaming-monitors',
				'curved-monitors',
				'portable-monitors',
			],
		],
		[
			'name' => 'BenQ',
			'slug' => 'benq',
			'description' => 'Professional monitors for gaming, design, and photography.',
			'logo' => 'benq.png',
			'website' => 'https://www.benq.com',
			'is_active' => true,
			'category_slugs' => [
				'monitors',
				'gaming-monitors',
				'portable-monitors',
			],
		],

		// ==========================================
		// PERIPHERALS & ACCESSORIES
		// ==========================================

		// Keyboards
		[
			'name' => 'Logitech',
			'slug' => 'logitech',
			'description' => 'World leader in computer peripherals, keyboards, mice, and webcams.',
			'logo' => 'logitech.png',
			'website' => 'https://www.logitech.com',
			'is_active' => true,
			'category_slugs' => [
				'keyboard-accessories',
				'gaming-keyboards',
				'gaming-mice',
				'mouse-pads',
				'webcams',
				'pc-peripherals',
				'gaming-mouse-pads',
			],
		],
		[
			'name' => 'Corsair Peripherals',
			'slug' => 'corsair-peripherals',
			'description' => 'Premium gaming keyboards, mice, and headsets.',
			'logo' => 'corsair-peripherals.png',
			'website' => 'https://www.corsair.com',
			'is_active' => true,
			'category_slugs' => [
				'gaming-keyboards',
				'gaming-mice',
				'gaming-headsets',
				'mouse-pads',
				'gaming-mouse-pads',
				'keyboard-accessories',
			],
		],
		[
			'name' => 'SteelSeries',
			'slug' => 'steelseries',
			'description' => 'Premium gaming peripherals, keyboards, mice, and headsets.',
			'logo' => 'steelseries.png',
			'website' => 'https://steelseries.com',
			'is_active' => true,
			'category_slugs' => [
				'gaming-keyboards',
				'gaming-mice',
				'gaming-headsets',
				'gaming-mouse-pads',
				'mouse-pads',
			],
		],

		// Mice & Mouse Pads
		[
			'name' => 'Razer Peripherals',
			'slug' => 'razer-peripherals',
			'description' => 'Premium gaming mice, keyboards, and mouse pads.',
			'logo' => 'razer-peripherals.png',
			'website' => 'https://www.razer.com',
			'is_active' => true,
			'category_slugs' => [
				'gaming-mice',
				'gaming-keyboards',
				'gaming-headsets',
				'gaming-mouse-pads',
				'mouse-pads',
			],
		],

		// Webcams
		[
			'name' => 'Logitech Webcams',
			'slug' => 'logitech-webcams',
			'description' => 'Premium webcams for streaming, video calls, and content creation.',
			'logo' => 'logitech-webcams.png',
			'website' => 'https://www.logitech.com',
			'is_active' => true,
			'category_slugs' => [
				'webcams',
				'gaming-webcams',
				'pc-peripherals',
			],
		],

		// ==========================================
		// PRINTERS & SCANNERS
		// ==========================================
		[
			'name' => 'Brother',
			'slug' => 'brother',
			'description' => 'Leading manufacturer of printers, scanners, and multifunction devices.',
			'logo' => 'brother.png',
			'website' => 'https://www.brother.com',
			'is_active' => true,
			'category_slugs' => [
				'printers-scanners',
				'printer-supplies',
				'printer-paper',
			],
		],
		[
			'name' => 'Canon',
			'slug' => 'canon',
			'description' => 'Premium printers, scanners, and imaging solutions.',
			'logo' => 'canon.png',
			'website' => 'https://www.canon.com',
			'is_active' => true,
			'category_slugs' => [
				'printers-scanners',
				'printer-supplies',
				'printer-paper',
			],
		],
		[
			'name' => 'Epson',
			'slug' => 'epson',
			'description' => 'Leading inkjet printers, scanners, and projection technology.',
			'logo' => 'epson.png',
			'website' => 'https://www.epson.com',
			'is_active' => true,
			'category_slugs' => [
				'printers-scanners',
				'printer-supplies',
				'printer-paper',
			],
		],

		// ==========================================
		// NETWORKING & CONNECTIVITY
		// ==========================================
		[
			'name' => 'Netgear',
			'slug' => 'netgear',
			'description' => 'Leading manufacturer of routers, switches, and networking equipment.',
			'logo' => 'netgear.png',
			'website' => 'https://www.netgear.com',
			'is_active' => true,
			'category_slugs' => [
				'network-switches',
				'networking',
				'wifi-extenders',
				'smart-home-devices',
			],
		],
		[
			'name' => 'TP-Link',
			'slug' => 'tp-link',
			'description' => 'Global leader in networking products, routers, and smart home devices.',
			'logo' => 'tp-link.png',
			'website' => 'https://www.tp-link.com',
			'is_active' => true,
			'category_slugs' => [
				'network-switches',
				'networking',
				'wifi-extenders',
				'smart-home-devices',
				'signal-boosters',
			],
		],
		[
			'name' => 'Cisco',
			'slug' => 'cisco',
			'description' => 'Enterprise networking solutions, routers, and switches.',
			'logo' => 'cisco.png',
			'website' => 'https://www.cisco.com',
			'is_active' => true,
			'category_slugs' => [
				'network-switches',
				'networking',
				'workstations-servers',
			],
		],

		// ==========================================
		// STORAGE & BACKUP
		// ==========================================
		[
			'name' => 'Synology',
			'slug' => 'synology',
			'description' => 'Leading NAS devices and network-attached storage solutions.',
			'logo' => 'synology.png',
			'website' => 'https://www.synology.com',
			'is_active' => true,
			'category_slugs' => [
				'nas-devices',
				'external-storage',
				'backup-recovery',
			],
		],
		[
			'name' => 'QNAP',
			'slug' => 'qnap',
			'description' => 'Premium NAS and network-attached storage solutions.',
			'logo' => 'qnap.png',
			'website' => 'https://www.qnap.com',
			'is_active' => true,
			'category_slugs' => [
				'nas-devices',
				'external-storage',
				'backup-recovery',
			],
		],

		// ==========================================
		// CABLE & ADAPTERS
		// ==========================================
		[
			'name' => 'Anker',
			'slug' => 'anker',
			'description' => 'Premium charging accessories, cables, adapters, and power banks.',
			'logo' => 'anker.png',
			'website' => 'https://www.anker.com',
			'is_active' => true,
			'category_slugs' => [
				'cables-adapters',
				'usb-cables',
				'car-chargers',
				'fast-chargers',
				'charging-accessories',
				'wireless-chargers',
				'power-supplies',
			],
		],
		[
			'name' => 'Belkin',
			'slug' => 'belkin',
			'description' => 'Premium cables, adapters, and charging solutions.',
			'logo' => 'belkin.png',
			'website' => 'https://www.belkin.com',
			'is_active' => true,
			'category_slugs' => [
				'cables-adapters',
				'usb-cables',
				'charging-accessories',
				'fast-chargers',
				'charging-ports',
				'docking-stations',
			],
		],

		// ==========================================
		// LAPTOP ACCESSORIES
		// ==========================================
		[
			'name' => 'Samsung',
			'slug' => 'samsung',
			'description' => 'Premium smartphones, tablets, laptops, and consumer electronics.',
			'logo' => 'samsung.png',
			'website' => 'https://www.samsung.com',
			'is_active' => true,
			'category_slugs' => [
				'smartphones',
				'android-smartphones',
				'smartwatches',
				'gaming-smartphones',
				'refurbished-smartphones',
			],
		],
		[
			'name' => 'Google',
			'slug' => 'google',
			'description' => 'Pixel smartphones, Chromebooks, and Android operating system.',
			'logo' => 'google.png',
			'website' => 'https://www.google.com',
			'is_active' => true,
			'category_slugs' => [
				'smartphones',
				'android-smartphones',
				'chromebooks-cloud',
				'operating-systems',
				'refurbished-smartphones',
			],
		],
		[
			'name' => 'OnePlus',
			'slug' => 'oneplus',
			'description' => 'Premium Android smartphones with fast charging and smooth performance.',
			'logo' => 'oneplus.png',
			'website' => 'https://www.oneplus.com',
			'is_active' => true,
			'category_slugs' => [
				'smartphones',
				'android-smartphones',
				'gaming-smartphones',
				'fast-chargers',
			],
		],
		[
			'name' => 'Xiaomi',
			'slug' => 'xiaomi',
			'description' => 'Global tech company offering smartphones, laptops, and smart devices.',
			'logo' => 'xiaomi.png',
			'website' => 'https://www.mi.com',
			'is_active' => true,
			'category_slugs' => [
				'smartphones',
				'android-smartphones',
				'gaming-smartphones',
				'laptops-macbooks',
			],
		],

		// ==========================================
		// PHONE ACCESSORIES
		// ==========================================
		[
			'name' => 'OtterBox',
			'slug' => 'otterbox',
			'description' => 'Premium phone cases and screen protectors for ultimate protection.',
			'logo' => 'otterbox.png',
			'website' => 'https://www.otterbox.com',
			'is_active' => true,
			'category_slugs' => [
				'phone-protection',
				'screen-protectors',
				'phone-pouches',
				'replacement-screens',
				'replacement-lenses',
			],
		],
		[
			'name' => 'Spigen',
			'slug' => 'spigen',
			'description' => 'Premium phone cases, screen protectors, and accessories.',
			'logo' => 'spigen.png',
			'website' => 'https://www.spigen.com',
			'is_active' => true,
			'category_slugs' => [
				'phone-protection',
				'screen-protectors',
				'phone-stands',
				'phone-organizers',
			],
		],
		[
			'name' => 'PopSockets',
			'slug' => 'popsockets',
			'description' => 'Innovative phone grips, stands, and accessories.',
			'logo' => 'popsockets.png',
			'website' => 'https://www.popsockets.com',
			'is_active' => true,
			'category_slugs' => [
				'phone-stands',
				'phone-organizers',
				'phone-buttons',
				'phone-pouches',
			],
		],

		// ==========================================
		// ERGONOMIC & DESK ORGANIZATION
		// ==========================================
		[
			'name' => 'Ergotron',
			'slug' => 'ergotron',
			'description' => 'Premium monitor arms, ergonomic solutions, and workstation accessories.',
			'logo' => 'ergotron.png',
			'website' => 'https://www.ergotron.com',
			'is_active' => true,
			'category_slugs' => [
				'monitor-arms',
				'ergonomic-accessories',
				'gaming-monitor-arms',
			],
		],
		[
			'name' => 'Fellowes',
			'slug' => 'fellowes',
			'description' => 'Office furniture, ergonomic accessories, and desk organization.',
			'logo' => 'fellowes.png',
			'website' => 'https://www.fellowes.com',
			'is_active' => true,
			'category_slugs' => [
				'desk-organizers',
				'desk-organizers-office',
				'document-holders',
				'wrist-rests',
				'file-folders',
			],
		],
		[
			'name' => '3M',
			'slug' => '3m',
			'description' => 'Desk accessories, monitor stands, and ergonomic solutions.',
			'logo' => '3m.png',
			'website' => 'https://www.3m.com',
			'is_active' => true,
			'category_slugs' => [
				'desk-lamps',
				'desk-organizers',
				'monitor-arms',
				'cable-management-office',
				'screen-protectors',
			],
		],

		// ==========================================
		// AUDIO ACCESSORIES
		// ==========================================
		[
			'name' => 'Sony',
			'slug' => 'sony',
			'description' => 'Premium headphones, speakers, audio interfaces, and consumer electronics.',
			'logo' => 'sony.png',
			'website' => 'https://www.sony.com',
			'is_active' => true,
			'category_slugs' => [
				'headphones-earbuds',
				'bluetooth-speakers',
				'speakers-subwoofers',
				'audio-accessories',
				'audio-interfaces',
				'gaming-headsets',
			],
		],
		[
			'name' => 'Bose',
			'slug' => 'bose',
			'description' => 'Premium audio brand offering headphones, speakers, and sound systems.',
			'logo' => 'bose.png',
			'website' => 'https://www.bose.com',
			'is_active' => true,
			'category_slugs' => [
				'headphones-earbuds',
				'bluetooth-speakers',
				'speakers-subwoofers',
				'audio-accessories',
			],
		],
		[
			'name' => 'JBL',
			'slug' => 'jbl',
			'description' => 'Premium audio brand offering headphones, speakers, and audio accessories.',
			'logo' => 'jbl.png',
			'website' => 'https://www.jbl.com',
			'is_active' => true,
			'category_slugs' => [
				'headphones-earbuds',
				'bluetooth-speakers',
				'speakers-subwoofers',
				'audio-accessories',
			],
		],
		[
			'name' => 'Focusrite',
			'slug' => 'focusrite',
			'description' => 'Professional audio interfaces and recording equipment.',
			'logo' => 'focusrite.png',
			'website' => 'https://www.focusrite.com',
			'is_active' => true,
			'category_slugs' => [
				'audio-interfaces',
				'audio-accessories',
				'studio-monitors',
			],
		],
	];