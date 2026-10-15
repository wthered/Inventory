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

			// 1. Μαζικό Insert Προϊόντων
			$initialTime = $this->insertInitialProducts();

			// 2. Fetch Images μια φορά
			$imagePool = $this->fetchImagePool();

			// 3. Υπολογισμός συνολικών chunks για το Progress Bar
			$totalProducts = Product::query()->count();
			$totalChunks   = (int) ceil($totalProducts / self::BATCH_SIZE);

			$terminal = new Terminal();
			$bar      = $this->command->getOutput()->createProgressBar($totalChunks);
			$bar->setBarWidth($terminal->getWidth() - 64);

			// Αλλαγή format ώστε να εμφανίζει ETA αντί για Avg Chunk
			$bar->setFormat(" %current% / %max% [%bar%] %percent:3s%% | ETA: %eta% | Last Chunk: %last_time%s");

			// Αρχικοποίηση Placeholders
			$bar->setMessage('00:00', 'eta');
			$bar->setMessage('0.00', 'last_time');

			$chunkDurations = [];

			// 4. Επεξεργασία ανά 512 προϊόντα (Memory Safe)
			Product::query()->chunkById(self::BATCH_SIZE, function ($products) use ($users, $imagePool, $bar, &$chunkDurations, $totalChunks) {
				$chunkStartTime = microtime(true);

				foreach ($products as $product) {
					if ($imagePool->isNotEmpty()) {
						$images = $imagePool->random(mt_rand(6, 16))->map(fn ($url) => [
							'image_location' => $url,
							'is_default'     => false,
							'created_at'     => Carbon::now(),
							'updated_at'     => Carbon::now()->addHours(mt_rand(0, 23))->addMinutes(mt_rand(0, 59))->addSeconds(mt_rand(0, 59)),
						]);

						$product->images()->createMany($images->toArray());
						// Απευθείας update στη βάση για αποφυγή του "Unknown column id"
						$product->images()->limit(1)->update(['is_default' => true]);
					}

					// Προσθήκη Ιστορικού
					$this->seedHistoryForProduct($product, $users);
				}

				// Υπολογισμός χρόνου εκτέλεσης του Chunk
				$duration         = round(microtime(true) - $chunkStartTime, 2);
				$chunkDurations[] = $duration;
				$avgDuration      = array_sum($chunkDurations) / count($chunkDurations);

				// Υπολογισμός υπολειπόμενου χρόνου (ETA)
				$remainingChunks = max(0, $totalChunks - count($chunkDurations));
				$etaSeconds      = round($avgDuration * $remainingChunks);
				$etaFormatted    = sprintf('%02d:%02d', floor($etaSeconds / 60), $etaSeconds % 60);

				// Ενημέρωση Progress Bar
				$bar->setMessage(number_format($duration, 2), 'last_time');
				$bar->setMessage($etaFormatted, 'eta');
				$bar->advance();
			});

			$bar->finish();
			$this->command->line(""); // Αλλαγή γραμμής μετά το τέλος

			$this->command->info(Product::query()->count() . " products have been seeded in " . $initialTime->diffInSeconds(Carbon::now(config('app.timezone'))) . " seconds.");
		}
	}
