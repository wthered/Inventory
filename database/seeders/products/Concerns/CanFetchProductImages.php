<?php

	namespace Database\Seeders\products\Concerns;

	use Exception;
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\GuzzleException;
	use Illuminate\Support\Collection;

	trait CanFetchProductImages {
		protected function fetchImagePool(): Collection {
			$this->command->info('Fetching movie posters from TMDB API...');
			$imagePool = collect();
			$client    = new Client();

			// Φέρνουμε μερικές σελίδες για ποικιλία (π.χ. 5 σελίδες = ~100 εικόνες)
			for ($page = 1; $page <= 5; $page++) {
				try {
					$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/now_playing?page=' . $page, [
						'headers' => [
							'Authorization' => 'Bearer ' . config('app.BEARER_TOKEN'),
							'Accept'        => 'application/json',
						],
					]);

					$body = json_decode($response->getBody(), true);

					if (isset($body['results'])) {
						foreach ($body['results'] as $film) {
							if (!empty($film['poster_path'])) {
								$imagePool->push('https://image.tmdb.org/t/p/original' . $film['poster_path'])->unique();
							}
						}
					}
				} catch (GuzzleException|Exception $e) {
					$this->command->error("API Error on page ".$page.": " . $e->getMessage());
					break; // Σταματάμε αν φάμε Rate Limit
				}
			}

			return $imagePool;
		}
	}