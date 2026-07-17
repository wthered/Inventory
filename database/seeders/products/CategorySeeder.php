<?php

	namespace Database\Seeders\products;

	use Carbon\Carbon;
	use Database\Seeders\ParentSeeder;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Str;

	class CategorySeeder extends ParentSeeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Clear existing database rows cleanly to reset Auto-Increments
			DB::statement('SET FOREIGN_KEY_CHECKS=0;');
			DB::table('categories')->truncate();
			DB::statement('SET FOREIGN_KEY_CHECKS=1;');

			// 1. Define standard master Parent Categories (parent_id = null)
			$parentCategories = require __DIR__ . '/data/categories/parent.php';

			$this->command->info('=======================================');
			$this->command->info('   🚀 STARTING CATEGORY SEEDER');
			$this->command->info('=======================================');
			$this->command->info('Seeding Parent Categories...');

			// Insert Parent categories and construct a lookup array (slug => database_id)
			$parentMap = [];
			foreach ($parentCategories as $parent) {
				$parentMap[$parent['slug']] = DB::table('categories')->insertGetId($parent + [
					'parent_id'  => null,
					'image'      => asset('images/parent.svg'),
					'is_active'  => true,
					'created_at' => Carbon::now(),
					'updated_at' => Carbon::now(),
				]);
			}

			$this->command->info('✅ Master Parent Categories seeded successfully.');

			// 2. Scan and load Child Categories recursively from the children directory
			$childrenFolder = __DIR__ . '/data/categories/children/';
			$childCategoriesRaw = [];

			if (is_dir($childrenFolder)) {
				$files = File::allFiles($childrenFolder);

				$this->command->info('📂 Scanning nested subdirectories for child category configs...');

				foreach ($files as $file) {
					if ($file->getExtension() === 'php') {
						$fileData = require $file->getPathname();
						if (is_array($fileData)) {
							$childCategoriesRaw = array_merge($childCategoriesRaw, $fileData);
						}
					}
				}
			}

			// 💡 ΔΙΟΡΘΩΣΗ: Μετατροπή σε Collection και ανακάτεμα με ασφάλεια μέσω Laravel
			$categoriesCollection = Collection::make($childCategoriesRaw)->shuffle();
			$this->command->info("📂 Loaded and shuffled ".$categoriesCollection->count()." total child categories safely.");

			// 3. Transform and prepare child data with relational mappings
			$insertData = [];
			$sortOrderCounters = [];

			// 💡 Διαβάζουμε την ανακατεμένη συλλογή της Laravel
			foreach ($categoriesCollection as $index => $child) {
				$parentSlug = $child['parent_slug'] ?? null;

				// Safety unsets so file data columns don't override database logic
				unset($child['parent_slug']);
				// 💡 ΔΙΟΡΘΩΣΗ: Αφαιρούμε το sort_order του αρχείου για να περάσει το δυναμικό!
				unset($child['sort_order']);

				$parentId = $parentMap[$parentSlug] ?? null;

				if ($parentId === null) {
					$this->command->warn("  ⚠️ Parent slug '".$parentSlug."' for child '" . $child['name'] ."' not found in the database. Skipping...");
					continue;
				}

				// Δυναμικός υπολογισμός του sort_order ανά parent_id
				if (!isset($sortOrderCounters[$parentId])) {
					$sortOrderCounters[$parentId] = 1;
				} else {
					$sortOrderCounters[$parentId]++;
				}

				// Create staggered timestamps mimicking real historical entries
				$current_time = Carbon::now(config('app.timezone'))->subDays($categoriesCollection->count() * $index % 365);

				$insertData[] = $child + [
					'parent_id'  => $parentId,
					'sort_order' => $sortOrderCounters[$parentId],
					'image'      => asset('images/child.svg'),
					'is_active'  => true,
					'created_at' => $current_time->subHours(mt_rand(0, 23))->subMinutes(mt_rand(0, 59))->subSeconds(mt_rand(0, 59)),
					'updated_at' => $current_time->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
				];
			}

			// 4. Mass chunk insert using Laravel Collections
			$this->command->info('⏳ Bulk inserting child categories into the database...');

			$processed = 0;
			$collection = Collection::make($insertData);
			$chunkCount = ceil($collection->count() / self::BATCH_SIZE);

			$collection->chunk(self::BATCH_SIZE)->each(function ($chunk, $index) use ($chunkCount, &$processed) {
				DB::table('categories')->insert($chunk->toArray());
				$processed += $chunk->count();
				$this->command->info("  ✅ Chunk ".($index + 1)."/".$chunkCount.": Inserted " . $chunk->count() . " categories (Total Processed: ".$processed.")");
			});

			$this->command->info('🎉 Category database synchronization complete.');
		}
	}