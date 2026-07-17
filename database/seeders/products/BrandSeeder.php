<?php

	namespace Database\Seeders\products;

	use App\Models\Brand;
	use App\Models\Category;
	use Exception;
	use Illuminate\Database\Seeder;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;

	class BrandSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// 1. Clear tables
			DB::statement('SET FOREIGN_KEY_CHECKS=0;');
			DB::table('brands')->truncate();
			DB::table('brand_category')->truncate();
			DB::statement('SET FOREIGN_KEY_CHECKS=1;');

			// 2. Load all raw brands from files into a flat list
			$rawBrands = $this->loadBrandsFromFiles();

			// 3. Fetch categories mapping (slug => id)
			$categoryMap = Category::pluck('id', 'slug')->toArray();

			if (empty($categoryMap)) {
				$this->command->warn('❌ No categories found. Run CategorySeeder first!');
				return;
			}

			$this->command->info('=======================================');
			$this->command->info('   🚀 STARTING BRAND SEEDER');
			$this->command->info('=======================================');
			$this->command->info('Total categories loaded: ' . count($categoryMap));
			$this->command->info('Total brands to process: ' . count($rawBrands));
			$this->command->info('');

			$createdCount = 0;
			$relationshipCount = 0;

			// 4. Iterate over each brand from your files
			foreach ($rawBrands as $brandData) {
				// Extract category slugs from the brand item, then remove it so it doesn't break mass assignment
				$categorySlugs = $brandData['category_slugs'] ?? [];
				unset($brandData['category_slugs']);

				// Create or update the Brand record
				$brand = Brand::firstOrCreate(
					['slug' => $brandData['slug']],
					[
						'name'        => $brandData['name'],
						'description' => $brandData['description'] ?? null,
						'logo'        => $brandData['logo'] ?? null,
						'website'     => $brandData['website'] ?? null,
						'is_active'   => $brandData['is_active'] ?? true,
					]
				);

				if ($brand->wasRecentlyCreated) {
					$createdCount++;
				}

				// Map the categories linked to this specific brand
				$categoryIds = [];
				foreach ($categorySlugs as $slug) {
					if (isset($categoryMap[$slug])) {
						$categoryIds[] = $categoryMap[$slug];
					} else {
						// Gracefully log missing category match without crashing the seeder
						$this->command->warn("  ⚠️ Category slug '".$slug."' attached to brand '" . $brand->name . "' does not exist in the database.");
					}
				}

				// Attach categories to pivot table (assuming a BelongsToMany relationship named 'categories')
				if (!empty($categoryIds)) {
					$brand->categories()->syncWithoutDetaching($categoryIds);
					$relationshipCount += count($categoryIds);
				}
			}

			$this->command->info('=======================================');
			$this->command->info("  ✅ Done! Created ".$createdCount." unique brands.");
			$this->command->info("  🔗 Synced ".$relationshipCount." brand-category relationships.");
			$this->command->info('=======================================');
		}

		/**
		 * Φορτώνει όλα τα brands από τα αρχεία στον φάκελο data/brands/
		 */
		private function loadBrandsFromFiles(): array {
			$allBrands = [];
			$brandsPath = __DIR__ . '/data/brands/';

			$this->command->info('Importing brands from ' . $brandsPath);

			// Αν ο φάκελος δεν υπάρχει, τον δημιουργούμε
			if (!is_dir($brandsPath)) {
				mkdir($brandsPath, 0755, true);
				$this->command->warn("📁 Brands folder created at: " . $brandsPath);
				$this->command->warn("📝 Please add brand files (e.g., electronics.php) in this folder.");
				return [];
			}

			// Βρίσκουμε όλα τα .php αρχεία στον φάκελο
			$files = File::files($brandsPath);

			if (empty($files)) {
				$this->command->warn("📁 No brand files found in: " . $brandsPath);
				$this->command->warn("📝 Please create at least one brand file (e.g., electronics.php)");
				return [];
			}

			$this->command->info('📂 Loading brands from files...');

			foreach ($files as $file) {
				$filename = $file->getFilename();

				// Αγνοούμε αρχεία που δεν είναι .php
				if ($file->getExtension() !== 'php') {
					continue;
				}

				try {
					$brands = require $file->getPathname();

					if (is_array($brands) && !empty($brands)) {
						$allBrands = array_merge($allBrands, $brands);
						$this->command->info("  📄 ".$filename." → " . count($brands) . " brands");
					} else {
						$this->command->warn("  ⚠️ ".$filename." → No brands found (empty file)");
					}
				} catch (Exception $e) {
					$this->command->error("  ❌ ".$filename." → Error: " . $e->getMessage());
				}
			}

			$this->command->info("📊 Total brands loaded: " . count($allBrands));

			return $allBrands;
		}
	}