<?php

	namespace Database\Seeders;

	use App\Models\Product;
	use App\Models\User;
	use Carbon\Carbon;
	use Database\Seeders\products\Concerns\CanFetchProductImages;
	use Database\Seeders\products\Concerns\CanSeedInitialProducts;
	use Database\Seeders\products\Concerns\CanSeedProductHistory;
	use Symfony\Component\Console\Terminal;

	class ProductSeeder extends ParentSeeder {
		use CanSeedProductHistory, CanFetchProductImages, CanSeedInitialProducts;

		public function run(): void {
			$users = User::query()->pluck('id')->toArray();

			// 1. Μαζικό Insert Προϊόντων (Όπως το είχες)
			$initialTime = $this->insertInitialProducts();

			// 2. Fetch Images μια φορά
			$imagePool = $this->fetchImagePool();

			// 3. Επεξεργασία ανά 512 προϊόντα (Memory Safe)
			Product::query()->chunkById(self::BATCH_SIZE, function ($products) use ($users, $imagePool) {
				$bar = $this->command->getOutput()->createProgressBar($products->count());
				$terminal = new Terminal();
				$bar->setBarWidth($terminal->getWidth() - 32);
				$bar->setFormat('very_verbose');

				foreach ($products as $product) {
					if ($imagePool->isNotEmpty()) {
						$images = $imagePool->random(mt_rand(6, 16))->map(fn($url) => [
							'image_location' => $url,
							'is_default'     => false,
							'created_at'     => now(),
							'updated_at'     => now()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						]);

						$product->images()->createMany($images->toArray());
						// Απευθείας update στη βάση για αποφυγή του "Unknown column id"
						$product->images()->limit(1)->update(['is_default' => true]);
					}

					// Προσθήκη Ιστορικού
					$this->seedHistoryForProduct($product, $users);

					$bar->advance();
				}

				$bar->finish();
				$this->command->line(""); // Αλλαγή γραμμής μετά το τέλος του chunk
			});

			$this->command->info(Product::query()->count()." products have been seeded in ".$initialTime->diffInSeconds(Carbon::now(config('app.timezone')))." seconds.");
		}
	}
