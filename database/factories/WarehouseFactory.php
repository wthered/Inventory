<?php

	namespace Database\Factories; // Ή Database\Factories\Warehouses ανάλογα με το setup σου

	use App\Enums\WarehouseType;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Factories\Factory;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;

	class WarehouseFactory extends Factory {
		protected $model = Warehouse::class;

		/**
		 * Η master static λίστα με όλες τις διαθέσιμες Lore τοποθεσίες.
		 * Γίνεται initialize μία φορά και μετά αφαιρούμε στοιχεία (pull).
		 */
		protected static ?Collection $availableLocations = null;

		/**
		 * Μέθοδος για το αρχικό γέμισμα της static λίστας
		 */
		protected static function initializeLocations(): void {
			if (self::$availableLocations !== null) {
				return;
			}

			$lord = Collection::make([
				// ==========================================
				// THE SHIRE (Χώρα των Χόμπιτ)
				// ==========================================
				['name' => 'Hobbiton', 'description' => 'The peaceful village in the Shire where Bilbo and Frodo Baggins lived', 'prefix' => 'LOTR-HOB'],
				['name' => 'Bag End', 'description' => 'The famous underground home of Bilbo and later Frodo Baggins in Hobbiton', 'prefix' => 'LOTR-BAG'],
				['name' => 'Michel Delving', 'description' => 'The chief town and administrative center of the Shire', 'prefix' => 'LOTR-MIC'],
				['name' => 'Bywater', 'description' => 'A village in the Shire, home to the Green Dragon Inn and the Battle of Bywater', 'prefix' => 'LOTR-BY W'],
				['name' => 'Tuckborough', 'description' => 'The ancestral home of the Took family in the Shire', 'prefix' => 'LOTR-TUC'],
				['name' => 'Brandy Hall', 'description' => 'The ancestral home of the Brandybuck family in Buckland', 'prefix' => 'LOTR-BRA'],
				['name' => 'Buckland', 'description' => 'The area east of the Shire, home of the Brandybucks', 'prefix' => 'LOTR-BUC'],
				['name' => 'Stock', 'description' => 'A village in the Eastfarthing of the Shire', 'prefix' => 'LOTR-STO'],
				['name' => 'Frogmorton', 'description' => 'A village in the Northfarthing of the Shire', 'prefix' => 'LOTR-FRO'],

				// ==========================================
				// BREE-LAND (Χώρα των Ανθρώπων & Χόμπιτ)
				// ==========================================
				['name' => 'Bree', 'description' => 'The main settlement at the crossroads in Bree-land, home to both Men and Hobbits', 'prefix' => 'LOTR-BRE'],
				['name' => 'Combe', 'description' => 'A village in Bree-land, located near Bree', 'prefix' => 'LOTR-COM'],
				['name' => 'Staddle', 'description' => 'A village in Bree-land, known for its stone houses', 'prefix' => 'LOTR-STA'],
				['name' => 'Archet', 'description' => 'A village in Bree-land, located in the Chetwood forest', 'prefix' => 'LOTR-ARC'],

				// ==========================================
				// RIVENDELL & ELVEN REALMS (Χώρες των Ξωτικών)
				// ==========================================
				['name' => 'Rivendell', 'description' => 'The hidden Elven valley refuge of Lord Elrond (also called Imladris)', 'prefix' => 'LOTR-RIV'],
				['name' => 'Imladris', 'description' => 'The Elvish name for Rivendell, meaning "deep valley of the cleft"', 'prefix' => 'LOTR-IML'],
				['name' => 'Lothlorien', 'description' => 'The beautiful golden forest realm of the Elves ruled by Galadriel and Celeborn', 'prefix' => 'LOTR-LOT'],
				['name' => 'Caras Galadhon', 'description' => 'The city of the Galadhrim, the capital of Lothlorien', 'prefix' => 'LOTR-CAR'],
				['name' => 'The Grey Havens', 'description' => 'The port city of the Elves, from which they sail to the Undying Lands', 'prefix' => 'LOTR-GRE'],
				['name' => 'Mithlond', 'description' => 'The Elvish name for the Grey Havens', 'prefix' => 'LOTR-MIT'],
				['name' => 'Woodland Realm', 'description' => 'The kingdom of the Elves in Mirkwood, ruled by Thranduil', 'prefix' => 'LOTR-WOO'],

				// ==========================================
				// GONDOR (Βασίλειο των Ανθρώπων - Νότια)
				// ==========================================
				['name' => 'Minas Tirith', 'description' => 'The White City and capital of Gondor, built on seven levels against the mountain', 'prefix' => 'LOTR-MIN'],
				['name' => 'Minas Anor', 'description' => 'The original name of Minas Tirith, meaning "Tower of the Sun"', 'prefix' => 'LOTR-ANO'],
				['name' => 'Minas Ithil', 'description' => 'The city of the moon, later captured and renamed Minas Morgul', 'prefix' => 'LOTR-ITH'],
				['name' => 'Minas Morgul', 'description' => 'The city of black sorcery, once the Gondorian city of Minas Ithil', 'prefix' => 'LOTR-MOR'],
				['name' => 'Osgiliath', 'description' => 'The ruined former capital of Gondor, straddling the River Anduin', 'prefix' => 'LOTR-OSG'],
				['name' => 'Dol Amroth', 'description' => 'The coastal city of Gondor, ruled by Prince Imrahil', 'prefix' => 'LOTR-DOL'],
				['name' => 'Pelargir', 'description' => 'The ancient harbor city of Gondor, located on the River Anduin', 'prefix' => 'LOTR-PEL'],
				['name' => 'Cair Andros', 'description' => 'An island fortress in the River Anduin, north of Osgiliath', 'prefix' => 'LOTR-CAI'],
				['name' => 'Henneth Annûn', 'description' => 'The hidden waterfall refuge of the Rangers of Ithilien', 'prefix' => 'LOTR-HEN'],

				// ==========================================
				// ROHAN (Βασίλειο των Ανθρώπων - Βόρεια)
				// ==========================================
				['name' => 'Edoras', 'description' => 'The golden hall and capital city of Rohan', 'prefix' => 'LOTR-EDO'],
				['name' => 'Helm\'s Deep', 'description' => 'The great valley fortress of Rohan where the battle against Saruman\'s army took place', 'prefix' => 'LOTR-HLM'],
				['name' => 'Dunharrow', 'description' => 'The fortress and refuge of the Rohirrim in the White Mountains', 'prefix' => 'LOTR-DUN'],
				['name' => 'The Hornburg', 'description' => 'The great fortress at Helm\'s Deep', 'prefix' => 'LOTR-HOR'],
				['name' => 'Aldburg', 'description' => 'The ancient capital of Rohan, located in the Eastfold', 'prefix' => 'LOTR-ALD'],
				['name' => 'Snowbourn', 'description' => 'The river that flows through Rohan, near Edoras', 'prefix' => 'LOTR-SNO'],

				// ==========================================
				// ISENGARD & ORTHANC (Σαρούμαν)
				// ==========================================
				['name' => 'Isengard', 'description' => 'The fortress valley of Saruman containing the black tower of Orthanc', 'prefix' => 'LOTR-ISN'],
				['name' => 'Orthanc', 'description' => 'The black tower at the center of Isengard, built by the Numenoreans', 'prefix' => 'LOTR-ORT'],
				['name' => 'Nan Curunir', 'description' => 'The valley of Isengard, meaning "Valley of Saruman"', 'prefix' => 'LOTR-NAN'],

				// ==========================================
				// MORDOR (Χώρα του Σαούρον)
				// ==========================================
				['name' => 'Moria', 'description' => 'The vast ancient underground kingdom of the Dwarves, also known as Khazad-dum', 'prefix' => 'LOTR-MOR'],
				['name' => 'Khazad-dûm', 'description' => 'The Dwarvish name for Moria, meaning "Dwarf-mansion"', 'prefix' => 'LOTR-KHA'],
				['name' => 'Barad-dûr', 'description' => 'The Dark Tower of Sauron in Mordor', 'prefix' => 'LOTR-BAR'],
				['name' => 'Mount Doom', 'description' => 'The volcanic mountain in Mordor where the One Ring was forged', 'prefix' => 'LOTR-MTD'],
				['name' => 'Orodruin', 'description' => 'The Elvish name for Mount Doom', 'prefix' => 'LOTR-ORO'],
				['name' => 'The Black Gate', 'description' => 'The north gate of Mordor, guarding the entrance to the Dark Land', 'prefix' => 'LOTR-BLA'],
				['name' => 'Morannon', 'description' => 'The Elvish name for the Black Gate', 'prefix' => 'LOTR-MRN'],
				['name' => 'Cirith Ungol', 'description' => 'The pass of the spider Shelob, guarding the way into Mordor', 'prefix' => 'LOTR-CIR'],
				['name' => 'The Tower of Cirith Ungol', 'description' => 'The fortress guarding the pass into Mordor', 'prefix' => 'LOTR-TOW'],

				// ==========================================
				// OTHER NOTABLE LOCATIONS (Άλλες Τοποθεσίες)
				// ==========================================
				['name' => 'Weathertop', 'description' => 'The hilltop where Frodo was stabbed by the Witch-king with a Morgul blade', 'prefix' => 'LOTR-WEA'],
				['name' => 'Amon Sûl', 'description' => 'The Elvish name for Weathertop', 'prefix' => 'LOTR-AMO'],
				['name' => 'The Prancing Pony', 'description' => 'The famous inn in Bree where the hobbits met Strider', 'prefix' => 'LOTR-PRA'],
				['name' => 'The Green Dragon', 'description' => 'The village inn in Bywater, a favorite meeting place of the hobbits', 'prefix' => 'LOTR-GRE'],
				['name' => 'The Golden Perch', 'description' => 'An inn in Stock, in the Eastfarthing of the Shire', 'prefix' => 'LOTR-GOL'],
				['name' => 'The Ivy Bush', 'description' => 'An inn on the Bywater road, near Hobbiton', 'prefix' => 'LOTR-IVY'],
				['name' => 'Barrow-downs', 'description' => 'The ancient burial mounds of the Kings of Men, haunted by wights', 'prefix' => 'LOTR-BAR'],
				['name' => 'The Old Forest', 'description' => 'The ancient forest east of the Shire, home to Old Man Willow', 'prefix' => 'LOTR-OLD'],
				['name' => 'Bombadil\'s House', 'description' => 'The home of Tom Bombadil and Goldberry in the Old Forest', 'prefix' => 'LOTR-BOM'],
				['name' => 'The Withywindle', 'description' => 'The river that flows through the Old Forest', 'prefix' => 'LOTR-WIT'],
				['name' => 'Fangorn Forest', 'description' => 'The ancient forest where the Ents live, led by Treebeard', 'prefix' => 'LOTR-FAN'],
				['name' => 'The Entwash', 'description' => 'The river that flows through Fangorn Forest', 'prefix' => 'LOTR-ENT'],
				['name' => 'The Anduin', 'description' => 'The great river that flows through Middle-earth', 'prefix' => 'LOTR-AND'],
				['name' => 'The Gladden Fields', 'description' => 'The area where Isildur lost the One Ring and was killed', 'prefix' => 'LOTR-GLA'],
				['name' => 'Dale', 'description' => 'The city of Men near the Lonely Mountain', 'prefix' => 'LOTR-DAL'],
				['name' => 'Erebor', 'description' => 'The Lonely Mountain, home of the Dwarves and the treasure of Thror', 'prefix' => 'LOTR-ERE'],
				['name' => 'Lake-town', 'description' => 'The town of Men on the Long Lake, built on wooden pilings', 'prefix' => 'LOTR-LAK'],
				['name' => 'Esgaroth', 'description' => 'The Elvish name for Lake-town', 'prefix' => 'LOTR-ESG'],
				['name' => 'The Misty Mountains', 'description' => 'The great mountain range that runs through the middle of Middle-earth', 'prefix' => 'LOTR-MIS'],
				['name' => 'The High Pass', 'description' => 'The pass through the Misty Mountains near Rivendell', 'prefix' => 'LOTR-HIG'],
				['name' => 'The Redhorn Pass', 'description' => 'The mountain pass near Moria, also known as Caradhras', 'prefix' => 'LOTR-RED'],
				['name' => 'Caradhras', 'description' => 'The Redhorn mountain, one of the peaks of the Misty Mountains', 'prefix' => 'LOTR-CAR'],
				['name' => 'The Dead Marshes', 'description' => 'The marshes where the dead of the Battle of Dagorlad lie', 'prefix' => 'LOTR-DEA'],
				['name' => 'The Emyn Muil', 'description' => 'The rocky hills near the eastern end of the Emyn Muil, where Frodo and Sam started their journey to Mordor', 'prefix' => 'LOTR-EMY'],
				['name' => 'The Paths of the Dead', 'description' => 'The cursed underground path that Aragorn took to recruit the Army of the Dead', 'prefix' => 'LOTR-PAT'],
				['name' => 'The Stone of Erech', 'description' => 'The black stone where Isildur cursed the Dead Men of Dunharrow', 'prefix' => 'LOTR-STO'],
				['name' => 'The Pelennor Fields', 'description' => 'The great battlefield outside Minas Tirith', 'prefix' => 'LOTR-PEL'],
				['name' => 'The River Anduin', 'description' => 'The great river that flows through Middle-earth', 'prefix' => 'LOTR-RIV'],
			]);

			$wow = Collection::make([
				// ==========================================
				// CAPITAL CITIES (Πρωτεύουσες)
				// ==========================================
				['name' => 'Orgrimmar', 'description' => 'The fortified capital city of the Horde, located in the harsh land of Durotar', 'prefix' => 'WOW-ORG'],
				['name' => 'Stormwind', 'description' => 'The majestic capital city of the Alliance, located in the Elwynn Forest', 'prefix' => 'WOW-STO'],
				['name' => 'Undercity', 'description' => 'The subterranean capital of the Forsaken undead, built beneath the ruins of Lordaeron', 'prefix' => 'WOW-UND'],
				['name' => 'Darnassus', 'description' => 'The mystical capital of the Night Elves, located on the giant tree of Teldrassil', 'prefix' => 'WOW-DAR'],
				['name' => 'Thunder Bluff', 'description' => 'The peaceful capital city of the Tauren, built atop a cluster of high mesas in Mulgore', 'prefix' => 'WOW-THU'],
				['name' => 'Ironforge', 'description' => 'The great mountain fortress and capital city of the Dwarves, carved deep into Khazanos', 'prefix' => 'WOW-IRO'],
				['name' => 'Dalaran', 'description' => 'The magical floating magocracy city-state ruled by the Kirin Tor council', 'prefix' => 'WOW-DAL'],
				['name' => 'Silvermoon', 'description' => 'The ancient and beautiful capital city of the Blood Elves in the Eversong Woods', 'prefix' => 'WOW-SIL'],
				['name' => 'Shattrath', 'description' => 'The great shattered sanctuary city in Outland, co-ruled by the Naaru and Aldor/Scryers', 'prefix' => 'WOW-SHA'],
				['name' => 'Gilneas', 'description' => 'The cursed city of the Worgen, once a proud human kingdom now shrouded in fog', 'prefix' => 'WOW-GIL'],
				['name' => 'Exodar', 'description' => 'The crashed dimensional ship of the Draenei, now a city on Azuremyst Isle', 'prefix' => 'WOW-EXO'],
				['name' => 'Suramar', 'description' => 'The magnificent ancient Nightborne capital, hidden behind a magical barrier in the Broken Isles', 'prefix' => 'WOW-SUR'],
				['name' => 'Mechagon', 'description' => 'The mechanical city-state of the Mechagnomes, located on the island of Mechagon', 'prefix' => 'WOW-MEC'],
				['name' => 'Zandalar', 'description' => 'The ancient golden capital of the Zandalari Trolls on the island of Zandalar', 'prefix' => 'WOW-ZAN'],
				['name' => 'Boralus', 'description' => 'The proud maritime capital of Kul Tiras, home to the Proudmoore family', 'prefix' => 'WOW-BOR'],
				['name' => 'Dazar\'alor', 'description' => 'The great golden pyramid-city of the Zandalari Empire', 'prefix' => 'WOW-DAZ'],

				// ==========================================
				// EASTERN KINGDOMS (Ανατολικά Βασίλεια)
				// ==========================================
				['name' => 'Lordaeron', 'description' => 'The once-great human kingdom, now the plague-ridden capital of the Undercity', 'prefix' => 'WOW-LOR'],
				['name' => 'Stratholme', 'description' => 'The cursed city that fell to the Scourge, now a dangerous place of undead', 'prefix' => 'WOW-STR'],
				['name' => 'Caer Darrow', 'description' => 'The ruined island fortress in the Western Plaguelands, home to Scholomance', 'prefix' => 'WOW-CAE'],
				['name' => 'Hearthglen', 'description' => 'The town in the Western Plaguelands, once defended by Tirion Fordring', 'prefix' => 'WOW-HEA'],
				['name' => 'Tarren Mill', 'description' => 'The small Horde village in the Hillsbrad Foothills', 'prefix' => 'WOW-TAR'],
				['name' => 'Southshore', 'description' => 'The former Alliance town in the Hillsbrad Foothills, now abandoned', 'prefix' => 'WOW-SOU'],
				['name' => 'Booty Bay', 'description' => 'The bustling goblin port city in Stranglethorn Vale', 'prefix' => 'WOW-BOO'],
				['name' => 'Gadgetzan', 'description' => 'The goblin city in the middle of Tanaris desert, led by the goblin trade princes', 'prefix' => 'WOW-GAD'],
				['name' => 'Ratchet', 'description' => 'The goblin port city in the Barrens, neutral trading post', 'prefix' => 'WOW-RAT'],
				['name' => 'Everlook', 'description' => 'The goblin city in the frozen Winterspring', 'prefix' => 'WOW-EVE'],
				['name' => 'Mudsprocket', 'description' => 'The goblin settlement in Dustwallow Marsh', 'prefix' => 'WOW-MUD'],

				// ==========================================
				// KALIMDOR (Καλίμντορ)
				// ==========================================
				['name' => 'Durotar', 'description' => 'The harsh red land of Orgrimmar, home to the Orcs and Trolls', 'prefix' => 'WOW-DUR'],
				['name' => 'Mulgore', 'description' => 'The peaceful green plains of Thunder Bluff, home to the Tauren', 'prefix' => 'WOW-MUL'],
				['name' => 'Teldrassil', 'description' => 'The great world tree and home of the Night Elves, where Darnassus was built', 'prefix' => 'WOW-TEL'],
				['name' => 'Hyjal', 'description' => 'The sacred mountain home of the Night Elves and druids, site of the Battle of Mount Hyjal', 'prefix' => 'WOW-HYJ'],
				['name' => 'Mount Hyjal', 'description' => 'The great mountain and home of the World Tree Nordrassil', 'prefix' => 'WOW-MOU'],
				['name' => 'Ahn\'Qiraj', 'description' => 'The ancient Silithid city in the desert of Silithus', 'prefix' => 'WOW-AHN'],
				['name' => 'Un\'Goro Crater', 'description' => 'The lush jungle crater in the heart of Kalimdor, home to dinosaurs and strange creatures', 'prefix' => 'WOW-UNG'],
				['name' => 'Feralas', 'description' => 'The dense forest of the Night Elves and highborne', 'prefix' => 'WOW-FER'],
				['name' => 'The Barrens', 'description' => 'The vast savanna of the Horde, stretching between Orgrimmar and Mulgore', 'prefix' => 'WOW-BAR'],
				['name' => 'Stonetalon Mountains', 'description' => 'The mountain range in central Kalimdor, home to harpies and dwarves', 'prefix' => 'WOW-STO'],
				['name' => 'Ashenvale', 'description' => 'The ancient forest of the Night Elves, known for its fierce battles with the Horde', 'prefix' => 'WOW-ASH'],
				['name' => 'Desolace', 'description' => 'The barren wasteland in western Kalimdor, once a beautiful forest', 'prefix' => 'WOW-DES'],
				['name' => 'Dustwallow Marsh', 'description' => 'The murky swamp where Theramore Isle is located', 'prefix' => 'WOW-DUS'],
				['name' => 'Tanaris', 'description' => 'The desert wasteland home to Gadgetzan and the ruins of Uldum', 'prefix' => 'WOW-TAN'],
				['name' => 'Silithus', 'description' => 'The southernmost desert of Kalimdor, home to the Silithid and the sword of Sargeras', 'prefix' => 'WOW-SIL'],
				['name' => 'Moonglade', 'description' => 'The sacred druid sanctuary in the mountains of Kalimdor', 'prefix' => 'WOW-MOO'],
				['name' => 'Winterspring', 'description' => 'The frozen northern region of Kalimdor, home to frost giants and the goblin city Everlook', 'prefix' => 'WOW-WIN'],

				// ==========================================
				// OUTLAND (Εξωτερικό)
				// ==========================================
				['name' => 'Hellfire Peninsula', 'description' => 'The desolate red wasteland of Outland, scarred by the Dark Portal', 'prefix' => 'WOW-HEL'],
				['name' => 'Zangarmarsh', 'description' => 'The swampy marshland of Outland, home to the Sporeggar and many strange creatures', 'prefix' => 'WOW-ZAN'],
				['name' => 'Terokkar Forest', 'description' => 'The forest of Outland, home to the ancient city of Auchindoun and the refugee city of Shattrath', 'prefix' => 'WOW-TER'],
				['name' => 'Nagrand', 'description' => 'The beautiful green plains of Outland, home to the Mag\'har Orcs and elemental spirits', 'prefix' => 'WOW-NAG'],
				['name' => 'Blade\'s Edge Mountains', 'description' => 'The jagged mountain range of Outland, home to the Gronn and Ogres', 'prefix' => 'WOW-BLA'],
				['name' => 'Netherstorm', 'description' => 'The floating islands of Outland, home to the mana-starved Ethereals and the manaforge', 'prefix' => 'WOW-NET'],
				['name' => 'Shadowmoon Valley', 'description' => 'The dark valley of Outland, home to the Black Temple of Illidan Stormrage', 'prefix' => 'WOW-SHA'],
				['name' => 'Auchenai Crypts', 'description' => 'The ancient burial grounds of the Draenei in Terokkar Forest', 'prefix' => 'WOW-AUC'],
				['name' => 'The Netherstorm', 'description' => 'The magical floating isles of Outland, home to the Ethereum and the abandoned manaforges', 'prefix' => 'WOW-NET'],

				// ==========================================
				// NORTHREND (Βόρεια)
				// ==========================================
				['name' => 'Dalaran', 'description' => 'The floating city-state in Northrend, now the capital of the Kirin Tor', 'prefix' => 'WOW-DAL'],
				['name' => 'Icecrown', 'description' => 'The frozen citadel of the Lich King, Icecrown Citadel', 'prefix' => 'WOW-ICC'],
				['name' => 'Icecrown Citadel', 'description' => 'The fortress of the Lich King, located in the heart of Icecrown Glacier', 'prefix' => 'WOW-ICE'],
				['name' => 'Wintergarde Keep', 'description' => 'The Alliance fortress in Dragonblight', 'prefix' => 'WOW-WIN'],
				['name' => 'Grizzly Hills', 'description' => 'The forested hills of Northrend, known for its lush forests and the Vrykul', 'prefix' => 'WOW-GRI'],
				['name' => 'Sholazar Basin', 'description' => 'The jungle basin in Northrend, a tropical paradise surrounded by ice', 'prefix' => 'WOW-SHO'],
				['name' => 'Borean Tundra', 'description' => 'The frozen tundra of Northrend, home to the Tuskarr and the dragons', 'prefix' => 'WOW-BOR'],
				['name' => 'Howling Fjord', 'description' => 'The fjords of Northrend, home to the Vrykul and the ancient wyrms', 'prefix' => 'WOW-HOW'],
				['name' => 'Storm Peaks', 'description' => 'The mountain peaks of Northrend, home to the Titan city of Ulduar', 'prefix' => 'WOW-STO'],
				['name' => 'Ulduar', 'description' => 'The ancient Titan prison-city in the Storm Peaks, home to the Old God Yogg-Saron', 'prefix' => 'WOW-ULD'],
				['name' => 'Dragonblight', 'description' => 'The frozen valley where the dragons of Northrend go to die', 'prefix' => 'WOW-DRA'],
				['name' => 'Wyrmrest Temple', 'description' => 'The great temple of the dragons in Dragonblight', 'prefix' => 'WOW-WYR'],

				// ==========================================
				// PANDARIA (Πανδαρία)
				// ==========================================
				['name' => 'The Jade Forest', 'description' => 'The lush forest of Pandaria, home to the Pandaren and the ancient serpent statues', 'prefix' => 'WOW-JAD'],
				['name' => 'Valley of the Four Winds', 'description' => 'The fertile agricultural valley of Pandaria, where the Pandaren grow their crops', 'prefix' => 'WOW-VAL'],
				['name' => 'Krasarang Wilds', 'description' => 'The southern coastal region of Pandaria, home to the ancient temple of the Red Crane', 'prefix' => 'WOW-KRA'],
				['name' => 'Townlong Steppes', 'description' => 'The vast plains of western Pandaria, home to the Mantid and the Klaxxi', 'prefix' => 'WOW-TOW'],
				['name' => 'Dread Wastes', 'description' => 'The desolate land of the Mantid, located in western Pandaria', 'prefix' => 'WOW-DRE'],
				['name' => 'Kun-Lai Summit', 'description' => 'The mountain peak of Pandaria, home to the Shado-Pan and the Yak', 'prefix' => 'WOW-KUN'],
				['name' => 'Vale of Eternal Blossoms', 'description' => 'The golden valley of Pandaria, protected by the ancient Mogu and the Naga', 'prefix' => 'WOW-VALE'],
				['name' => 'Isle of Thunder', 'description' => 'The lightning-filled isle of Pandaria, the home of the Thunder King Lei Shen', 'prefix' => 'WOW-ISL'],
				['name' => 'The Wandering Isle', 'description' => 'The giant turtle-isle of the Pandaren, traveling through the seas of Azeroth', 'prefix' => 'WOW-WAN'],

				// ==========================================
				// BROKEN ISLES (Σπασμένα Νησιά)
				// ==========================================
				['name' => 'Stormheim', 'description' => 'The stormy isle of the Vrykul, home to the Valarjar and the Storm Drakes', 'prefix' => 'WOW-STO'],
				['name' => 'Azsuna', 'description' => 'The ancient Nightborne isle of Azsuna, home to the Night Elves and the ruins of Nar\'thalas', 'prefix' => 'WOW-AZS'],
				['name' => 'Val\'sharah', 'description' => 'The blessed valley of the Night Elves, home to the Druids of the Claw', 'prefix' => 'WOW-VAL'],
				['name' => 'Highmountain', 'description' => 'The mountain peak of the Tauren, home to the Highmountain Tauren and the elemental spirits', 'prefix' => 'WOW-HIG'],
				['name' => 'The Broken Shore', 'description' => 'The desolate island of the Broken Shore, home to the Tomb of Sargeras', 'prefix' => 'WOW-BRO'],
				['name' => 'Tomb of Sargeras', 'description' => 'The ancient temple of the Pantheon, corrupted by the Burning Legion', 'prefix' => 'WOW-TOM'],

				// ==========================================
				// DRAENOR (Δρένορ - Warlords of Draenor)
				// ==========================================
				['name' => 'Frostfire Ridge', 'description' => 'The frozen ridge of Draenor, home to the Frostwolf Clan', 'prefix' => 'WOW-FRO'],
				['name' => 'Shadowmoon Valley', 'description' => 'The dark valley of Draenor, home to the Shadowmoon Clan and the Black Temple', 'prefix' => 'WOW-SHA'],
				['name' => 'Nagrand', 'description' => 'The beautiful plains of Draenor, home to the Warsong Clan and the elemental spirits', 'prefix' => 'WOW-NAG'],
				['name' => 'Gorgrond', 'description' => 'The rocky wasteland of Draenor, home to the Iron Horde and the Laughing Skull Orcs', 'prefix' => 'WOW-GOR'],
				['name' => 'Talador', 'description' => 'The forested region of Draenor, home to the Draenei and the Auchindoun', 'prefix' => 'WOW-TAL'],
				['name' => 'Spires of Arak', 'description' => 'The bird-like isle of the Arakkoa, home to the Ravenous and the Spires of Arak', 'prefix' => 'WOW-SPI'],
				['name' => 'Tanaan Jungle', 'description' => 'The dense jungle of Draenor, home to the Iron Horde and the Blood Elves', 'prefix' => 'WOW-TAN'],
				['name' => 'Ashran', 'description' => 'The island of Ashran, a PvP zone in the middle of Draenor', 'prefix' => 'WOW-ASH'],

				// ==========================================
				// DUNGEONS & RAIDS (Μπουντρούμια & Raids)
				// ==========================================
				['name' => 'The Stockades', 'description' => 'The prison in Stormwind, where criminals and political prisoners are held', 'prefix' => 'WOW-STO'],
				['name' => 'Deadmines', 'description' => 'The defias mines of Westfall, home to the Defias Brotherhood', 'prefix' => 'WOW-DEA'],
				['name' => 'Scarlet Monastery', 'description' => 'The cathedral of the Scarlet Crusade, now occupied by the undead', 'prefix' => 'WOW-SCA'],
				['name' => 'Scholomance', 'description' => 'The necromantic school in the Western Plaguelands, now in ruins', 'prefix' => 'WOW-SCH'],
				['name' => 'Stratholme', 'description' => 'The cursed city of Stratholme, now a Scourge stronghold', 'prefix' => 'WOW-STR'],
				['name' => 'Molten Core', 'description' => 'The volcanic heart of the Blackrock Mountain, home to Ragnaros the Firelord', 'prefix' => 'WOW-MOL'],
				['name' => 'Blackwing Lair', 'description' => 'The lair of Nefarian in the Blackrock Spire', 'prefix' => 'WOW-BLA'],
				['name' => 'Ruins of Ahn\'Qiraj', 'description' => 'The ancient Silithid city in the desert of Silithus', 'prefix' => 'WOW-RUI'],
				['name' => 'Naxxramas', 'description' => 'The floating necropolis of the Lich King in the Eastern Plaguelands', 'prefix' => 'WOW-NAX'],
				['name' => 'Karazhan', 'description' => 'The tower of Medivh in the Deadwind Pass, filled with magic and secrets', 'prefix' => 'WOW-KAR'],
				['name' => 'The Eye', 'description' => 'The floating prison of Illidan Stormrage in Outland', 'prefix' => 'WOW-EYE'],
				['name' => 'Serpentshrine Cavern', 'description' => 'The cavern of Lady Vashj in the Coilfang Reservoir', 'prefix' => 'WOW-SER'],
				['name' => 'Sunwell Plateau', 'description' => 'The ancient well of the Blood Elves in the Isle of Quel\'Danas', 'prefix' => 'WOW-SUN'],

				// ==========================================
				// OTHER NOTABLE LOCATIONS (Άλλες Τοποθεσίες)
				// ==========================================
				['name' => 'The Dark Portal', 'description' => 'The portal connecting Azeroth to Outland and Draenor, located in the Blasted Lands', 'prefix' => 'WOW-DAR'],
				['name' => 'Theramore Isle', 'description' => 'The island city of Theramore, home of Jaina Proudmoore, destroyed by the Horde', 'prefix' => 'WOW-THE'],
				['name' => 'Kul Tiras', 'description' => 'The island nation of Kul Tiras, home of the Proudmoore family', 'prefix' => 'WOW-KUL'],
				['name' => 'The Maelstrom', 'description' => 'The great whirlpool in the center of Azeroth, leading to Deepholm', 'prefix' => 'WOW-MAE'],
				['name' => 'Deepholm', 'description' => 'The elemental plane of Earth, home to the Earth Elementals and the Stone Core', 'prefix' => 'WOW-DEE'],
				['name' => 'The Firelands', 'description' => 'The elemental plane of Fire, home to Ragnaros the Firelord', 'prefix' => 'WOW-FIR'],
				['name' => 'The Eye of Eternity', 'description' => 'The floating isle of the Blue Dragonflight in the Nexus', 'prefix' => 'WOW-EYE'],
				['name' => 'The Oculus', 'description' => 'The magical tower of the Blue Dragonflight in the Nexus', 'prefix' => 'WOW-OCU'],
				['name' => 'The Lich King\'s Throne', 'description' => 'The throne of the Lich King in Icecrown Citadel', 'prefix' => 'WOW-LIC'],
			]);

			// Ενώνουμε όλες τις τοποθεσίες σε μία ενιαία λίστα και τις ανακατεύουμε (shuffle)
			self::$availableLocations = $lord->concat($wow)->shuffle();
		}

		/**
		 * Φιλτράρει τις τοποθεσίες που έχουν ήδη χρησιμοποιηθεί στη βάση
		 */
		protected static function filterUsedLocations(): void {
			if (self::$availableLocations === null || self::$availableLocations->isEmpty()) {
				return;
			}

			// Παίρνουμε όλα τα codes που υπάρχουν ήδη στη βάση
			$usedCodes = DB::table('warehouses')->pluck('code')->toArray();

			// Φιλτράρουμε τις availableLocations αφαιρώντας όσα prefix υπάρχουν ήδη
			self::$availableLocations = self::$availableLocations->filter(function ($location) use ($usedCodes) {
				// Το prefix είναι το βασικό αναγνωριστικό (π.χ. WOW-SHA)
				$prefix = $location['prefix'];

				// Ελέγχουμε αν υπάρχει ήδη κάποιο warehouse με αυτό το prefix
				foreach ($usedCodes as $usedCode) {
					if (Str::startsWith($usedCode, $prefix)) {
						return false; // Το αφαιρούμε από τη λίστα
					}
				}
				return true;
			})->values(); // επαναφορά των keys
		}

		public function definition(): array {
			// Διασφαλίζουμε ότι η static λίστα έχει γεμίσει
			self::initializeLocations();

			// Φιλτράρουμε όσες τοποθεσίες έχουν ήδη χρησιμοποιηθεί
			self::filterUsedLocations();

			// 1. Κάνουμε pull (παίρνουμε και ταυτόχρονα αφαιρούμε) ένα τυχαίο στοιχείο
			$selected = self::$availableLocations->pop();

			// Fallback σε περίπτωση που ζητήθηκαν περισσότερες αποθήκες
			if ($selected === null) {
				$fallbackName = fake()->unique()->city();
				$selected = [
					'name'        => $fallbackName . ' Depot',
					'description' => 'An automated logistics hub extension.',
					'prefix'      => 'GEN-' . Str::upper(Str::substr($fallbackName, 0, 3))
				];
			}

			// Αν το $selected είναι null, δημιουργούμε generic
			if (!is_array($selected)) {
				$selected = [
					'name'        => fake()->unique()->city() . ' Depot',
					'description' => 'An automated logistics hub extension.',
					'prefix'      => 'GEN-' . Str::upper(Str::random(3))
				];
			}

			$name = $selected['name'];
			$isLotr = Str::startsWith($selected['prefix'], 'LOTR');

			// Προσθέτουμε Central Warehouse στο όνομα αν είναι WoW
			if (!$isLotr && !Str::contains($name, 'Depot') && !Str::contains($name, 'Warehouse')) {
				$name = $name . ' Central Warehouse';
			}

			// Δημιουργούμε μοναδικό code με τυχαίο suffix
			$code = $selected['prefix'] . '-' . Str::padLeft(mt_rand(1, 99), 2, '0');

			// Αν το code υπάρχει ήδη, προσθέτουμε ακόμα ένα τυχαίο νούμερο
			$existing = DB::table('warehouses')->where('code', $code)->exists();
			if ($existing) {
				$code = $selected['prefix'] . '-' . Str::padLeft(mt_rand(1, 999), 3, '0');
			}

			return [
				'code'         => $code,
				'name'         => $name,
				'type'         => fake()->randomElement(WarehouseType::cases())->value,
				'description'  => $selected['description'],
				'address'      => fake()->streetAddress(),
				'city'         => $selected['name'],
				'state'        => $isLotr ? 'Middle-earth' : 'Azeroth',
				'country'      => 'Fantasy Realm',
				'postal_code'  => fake()->postcode(),
				'phone'        => fake()->phoneNumber(),
				'email'        => fake()->companyEmail(),
				'manager_id'   => null,

				// Διαστάσεις αποθήκης
				'zones'        => mt_rand(2, 4),
				'aisles'       => mt_rand(2, 4),
				'racks'        => mt_rand(2, 4),
				'shelves'      => mt_rand(2, 4),
				'bins'         => mt_rand(2, 4),
				'is_primary'   => false,
				'is_active'    => true,
			];
		}
	}