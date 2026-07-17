<?php

	return [
		// === BOOK PUBLISHERS ===
		[
			'name' => 'Penguin Random House',
			'slug' => 'penguin-random-house',
			'description' => 'The world\'s largest trade book publisher, offering a vast range of fiction and non-fiction.',
			'logo' => 'penguin-random-house.png',
			'website' => 'https://www.penguinrandomhouse.com',
			'is_active' => true,
			'category_slugs' => [
				'instructional-books',
				'box-sets',
				'collector-editions',
			],
		],
		[
			'name' => 'HarperCollins',
			'slug' => 'harpercollins',
			'description' => 'One of the world\'s largest publishers with a rich catalog of books across all genres.',
			'logo' => 'harpercollins.png',
			'website' => 'https://www.harpercollins.com',
			'is_active' => true,
			'category_slugs' => [
				'instructional-books',
				'pop-rock-music-books',
			],
		],
		[
			'name' => 'Hachette Livre',
			'slug' => 'hachette-livre',
			'description' => 'French international publishing house with a diverse collection of books.',
			'logo' => 'hachette.png',
			'website' => 'https://www.hachette.com',
			'is_active' => true,
			'category_slugs' => [
				'instructional-books',
				'box-sets',
			],
		],
		[
			'name' => 'Simon & Schuster',
			'slug' => 'simon-schuster',
			'description' => 'Major publishing house known for bestsellers in fiction, non-fiction, and children\'s books.',
			'logo' => 'simon-schuster.png',
			'website' => 'https://www.simonandschuster.com',
			'is_active' => true,
			'category_slugs' => [
				'instructional-books',
				'audiobooks',
			],
		],
		[
			'name' => 'Scholastic',
			'slug' => 'scholastic',
			'description' => 'Global children\'s publishing, education, and media company.',
			'logo' => 'scholastic.png',
			'website' => 'https://www.scholastic.com',
			'is_active' => true,
			'category_slugs' => [
				'instructional-books',
				'educational-pcs',
			],
		],

		// === E-BOOKS & E-READERS ===
		[
			'name' => 'Amazon Kindle',
			'slug' => 'amazon-kindle',
			'description' => 'World-leading e-readers and e-books platform by Amazon.',
			'logo' => 'amazon-kindle.png',
			'website' => 'https://www.amazon.com/kindle',
			'is_active' => true,
			'category_slugs' => [
				'e-readers',
				'e-books',
			],
		],
		[
			'name' => 'Kobo',
			'slug' => 'kobo',
			'description' => 'Premium e-readers and digital reading platform by Rakuten.',
			'logo' => 'kobo.png',
			'website' => 'https://www.kobo.com',
			'is_active' => true,
			'category_slugs' => [
				'e-readers',
				'e-books',
			],
		],
		[
			'name' => 'Apple Books',
			'slug' => 'apple-books',
			'description' => 'Apple\'s digital bookstore and e-book reading platform.',
			'logo' => 'apple-books.png',
			'website' => 'https://www.apple.com/apple-books',
			'is_active' => true,
			'category_slugs' => [
				'e-books',
			],
		],
		[
			'name' => 'Barnes & Noble Nook',
			'slug' => 'barnes-noble-nook',
			'description' => 'E-readers and digital books from the leading US bookstore chain.',
			'logo' => 'nook.png',
			'website' => 'https://www.barnesandnoble.com/nook',
			'is_active' => true,
			'category_slugs' => [
				'e-readers',
				'e-books',
			],
		],

		// === AUDIOBOOKS ===
		[
			'name' => 'Audible',
			'slug' => 'audible',
			'description' => 'The world\'s largest audiobook platform, owned by Amazon.',
			'logo' => 'audible.png',
			'website' => 'https://www.audible.com',
			'is_active' => true,
			'category_slugs' => [
				'audiobooks',
				'book-subscriptions',
			],
		],
		[
			'name' => 'Libro.fm',
			'slug' => 'libro-fm',
			'description' => 'Audiobook platform supporting independent bookstores.',
			'logo' => 'libro-fm.png',
			'website' => 'https://www.libro.fm',
			'is_active' => true,
			'category_slugs' => [
				'audiobooks',
			],
		],

		// === SPECIALTY & COLLECTOR BOOKS ===
		[
			'name' => 'Taschen',
			'slug' => 'taschen',
			'description' => 'Premium art, photography, and collector\'s edition books.',
			'logo' => 'taschen.png',
			'website' => 'https://www.taschen.com',
			'is_active' => true,
			'category_slugs' => [
				'collector-editions',
				'first-editions',
				'signed-books',
			],
		],
		[
			'name' => 'The Folio Society',
			'slug' => 'folio-society',
			'description' => 'Luxury illustrated editions of classic and contemporary books.',
			'logo' => 'folio-society.png',
			'website' => 'https://www.foliosociety.com',
			'is_active' => true,
			'category_slugs' => [
				'collector-editions',
				'box-sets',
			],
		],
		[
			'name' => 'Easton Press',
			'slug' => 'easton-press',
			'description' => 'Fine leather-bound collector\'s editions of classic literature.',
			'logo' => 'easton-press.png',
			'website' => 'https://www.eastonpress.com',
			'is_active' => true,
			'category_slugs' => [
				'collector-editions',
				'first-editions',
				'signed-books',
			],
		],

		// === BOOK SUBSCRIPTIONS ===
		[
			'name' => 'Book of the Month',
			'slug' => 'book-of-the-month',
			'description' => 'Monthly book subscription service delivering curated picks.',
			'logo' => 'book-of-the-month.png',
			'website' => 'https://www.bookofthemonth.com',
			'is_active' => true,
			'category_slugs' => [
				'book-subscriptions',
			],
		],
		[
			'name' => 'OwlCrate',
			'slug' => 'owlcrate',
			'description' => 'Young adult book subscription box with exclusive editions.',
			'logo' => 'owlcrate.png',
			'website' => 'https://www.owlcrate.com',
			'is_active' => true,
			'category_slugs' => [
				'book-subscriptions',
				'collector-editions',
			],
		],
		[
			'name' => 'Illumicrate',
			'slug' => 'illumicrate',
			'description' => 'Fantasy and sci-fi book subscription with exclusive content.',
			'logo' => 'illumicrate.png',
			'website' => 'https://www.illumicrate.com',
			'is_active' => true,
			'category_slugs' => [
				'book-subscriptions',
				'collector-editions',
			],
		],

		// === COMICS & GRAPHIC NOVELS ===
		[
			'name' => 'DC Comics',
			'slug' => 'dc-comics',
			'description' => 'One of the world\'s largest comic book publishers, home to Batman, Superman, and Wonder Woman.',
			'logo' => 'dc-comics.png',
			'website' => 'https://www.dccomics.com',
			'is_active' => true,
			'category_slugs' => [
				'comic-subscriptions',
				'collector-editions',
				'superhero-figures',
			],
		],
		[
			'name' => 'Marvel Comics',
			'slug' => 'marvel-comics',
			'description' => 'Iconic comic book publisher featuring Spider-Man, Avengers, and X-Men.',
			'logo' => 'marvel-comics.png',
			'website' => 'https://www.marvel.com/comics',
			'is_active' => true,
			'category_slugs' => [
				'comic-subscriptions',
				'collector-editions',
				'superhero-figures',
				'action-figures',
				'collectible-figures',
			],
		],
		[
			'name' => 'Image Comics',
			'slug' => 'image-comics',
			'description' => 'Leading independent comic publisher known for The Walking Dead and Saga.',
			'logo' => 'image-comics.png',
			'website' => 'https://imagecomics.com',
			'is_active' => true,
			'category_slugs' => [
				'comic-subscriptions',
				'collector-editions',
			],
		],
		[
			'name' => 'Dark Horse Comics',
			'slug' => 'dark-horse-comics',
			'description' => 'Independent comic publisher known for Hellboy, The Umbrella Academy, and Star Wars comics.',
			'logo' => 'dark-horse.png',
			'website' => 'https://www.darkhorse.com',
			'is_active' => true,
			'category_slugs' => [
				'comic-subscriptions',
				'collector-editions',
				'movie-characters',
			],
		],

		// === MAGAZINES & JOURNALS ===
		[
			'name' => 'The New Yorker',
			'slug' => 'new-yorker',
			'description' => 'Iconic American magazine covering culture, politics, and the arts.',
			'logo' => 'new-yorker.png',
			'website' => 'https://www.newyorker.com',
			'is_active' => true,
			'category_slugs' => [
				'magazines',
				'newspapers-journals',
			],
		],
		[
			'name' => 'National Geographic',
			'slug' => 'national-geographic',
			'description' => 'World-renowned magazine covering science, nature, and exploration.',
			'logo' => 'national-geographic.png',
			'website' => 'https://www.nationalgeographic.com',
			'is_active' => true,
			'category_slugs' => [
				'magazines',
			],
		],
		[
			'name' => 'Time',
			'slug' => 'time-magazine',
			'description' => 'American news magazine with global readership.',
			'logo' => 'time.png',
			'website' => 'https://time.com',
			'is_active' => true,
			'category_slugs' => [
				'magazines',
				'newspapers-journals',
			],
		],
		[
			'name' => 'The Economist',
			'slug' => 'economist',
			'description' => 'Global weekly newspaper covering international news, economics, and politics.',
			'logo' => 'economist.png',
			'website' => 'https://www.economist.com',
			'is_active' => true,
			'category_slugs' => [
				'magazines',
				'newspapers-journals',
			],
		],

		// === MUSIC BOOKS & SHEET MUSIC ===
		[
			'name' => 'Hal Leonard',
			'slug' => 'hal-leonard',
			'description' => 'World\'s largest music print publisher, offering sheet music and instructional books.',
			'logo' => 'hal-leonard.png',
			'website' => 'https://www.halleonard.com',
			'is_active' => true,
			'category_slugs' => [
				'classical-sheet-music',
				'digital-sheet-music',
				'jazz-blues-sheet',
				'pop-rock-music-books',
				'instructional-books',
			],
		],
		[
			'name' => 'Alfred Music',
			'slug' => 'alfred-music',
			'description' => 'Leading music education publisher with sheet music and instructional materials.',
			'logo' => 'alfred-music.png',
			'website' => 'https://www.alfred.com',
			'is_active' => true,
			'category_slugs' => [
				'classical-sheet-music',
				'digital-sheet-music',
				'instructional-books',
			],
		],
		[
			'name' => 'Schott Music',
			'slug' => 'schott-music',
			'description' => 'German classical music publisher and sheet music specialist.',
			'logo' => 'schott-music.png',
			'website' => 'https://www.schott-music.com',
			'is_active' => true,
			'category_slugs' => [
				'classical-sheet-music',
				'jazz-blues-sheet',
			],
		],

		// === MOVIES, TV & DOCUMENTARIES ===
		[
			'name' => 'Criterion Collection',
			'slug' => 'criterion-collection',
			'description' => 'Premium collector\'s editions of classic and contemporary films.',
			'logo' => 'criterion-collection.png',
			'website' => 'https://www.criterion.com',
			'is_active' => true,
			'category_slugs' => [
				'movies-tv',
				'collector-editions',
				'box-sets',
			],
		],
		[
			'name' => 'BBC Video',
			'slug' => 'bbc-video',
			'description' => 'Home video releases of BBC documentaries and series.',
			'logo' => 'bbc-video.png',
			'website' => 'https://www.bbc.com',
			'is_active' => true,
			'category_slugs' => [
				'movies-tv',
				'documentaries',
				'box-sets',
			],
		],
		[
			'name' => 'National Geographic Films',
			'slug' => 'national-geographic-films',
			'description' => 'Documentary films and series from National Geographic.',
			'logo' => 'national-geographic-films.png',
			'website' => 'https://www.nationalgeographic.com',
			'is_active' => true,
			'category_slugs' => [
				'documentaries',
				'movies-tv',
			],
		],

		// === BOOK ACCESSORIES ===
		[
			'name' => 'Book Beau',
			'slug' => 'book-beau',
			'description' => 'Stylish book sleeves and covers for book lovers.',
			'logo' => 'book-beau.png',
			'website' => 'https://www.bookbeau.com',
			'is_active' => true,
			'category_slugs' => [
				'book-covers',
				'bookmarks',
			],
		],
		[
			'name' => 'Kickstarter Books',
			'slug' => 'kickstarter-books',
			'description' => 'Crowdfunding platform for independent book projects.',
			'logo' => 'kickstarter.png',
			'website' => 'https://www.kickstarter.com',
			'is_active' => true,
			'category_slugs' => [
				'first-editions',
				'signed-books',
			],
		],

		// === TABLETOP GAMES ===
		[
			'name' => 'Wizards of the Coast',
			'slug' => 'wizards-coast',
			'description' => 'Publisher of tabletop RPGs including Dungeons & Dragons and Magic: The Gathering.',
			'logo' => 'wizards-coast.png',
			'website' => 'https://www.wizards.com',
			'is_active' => true,
			'category_slugs' => [
				'tabletop-rpgs',
				'strategy-board-games',
				'card-games',
				'collector-editions',
				'dice-spinners',
			],
		],
		[
			'name' => 'Paizo',
			'slug' => 'paizo',
			'description' => 'Publisher of Pathfinder and Starfinder tabletop RPGs.',
			'logo' => 'paizo.png',
			'website' => 'https://www.paizo.com',
			'is_active' => true,
			'category_slugs' => [
				'tabletop-rpgs',
				'strategy-board-games',
			],
		],

		// === STATIONERY & GREETING CARDS ===
		[
			'name' => 'Hallmark',
			'slug' => 'hallmark',
			'description' => 'Iconic greeting card and stationery brand.',
			'logo' => 'hallmark.png',
			'website' => 'https://www.hallmark.com',
			'is_active' => true,
			'category_slugs' => [
				'greeting-cards',
				'party-supplies',
			],
		],
		[
			'name' => 'Paper Source',
			'slug' => 'paper-source',
			'description' => 'Premium stationery and paper goods for creative expression.',
			'logo' => 'paper-source.png',
			'website' => 'https://www.papersource.com',
			'is_active' => true,
			'category_slugs' => [
				'greeting-cards',
				'journaling-supplies',
				'notebooks-journals',
			],
		],
		[
			'name' => 'Rifle Paper Co.',
			'slug' => 'rifle-paper-co',
			'description' => 'Beautiful stationery, greeting cards, and paper products.',
			'logo' => 'rifle-paper.png',
			'website' => 'https://www.riflepaperco.com',
			'is_active' => true,
			'category_slugs' => [
				'greeting-cards',
				'notebooks-journals',
				'bookmarks',
			],
		],

		// === VINYL & COLLECTIBLE FIGURES ===
		[
			'name' => 'Funko',
			'slug' => 'funko',
			'description' => 'Pop culture collectible figures including Pop! Vinyl series.',
			'logo' => 'funko.png',
			'website' => 'https://www.funko.com',
			'is_active' => true,
			'category_slugs' => [
				'vinyl-figures',
				'collectible-figures',
				'action-figures',
				'movie-characters',
				'superhero-figures',
			],
		],
		[
			'name' => 'Good Smile Company',
			'slug' => 'good-smile',
			'description' => 'Premium anime and gaming collectible figures.',
			'logo' => 'good-smile.png',
			'website' => 'https://www.goodsmile.com',
			'is_active' => true,
			'category_slugs' => [
				'vinyl-figures',
				'collectible-figures',
				'action-figures',
			],
		],
	];