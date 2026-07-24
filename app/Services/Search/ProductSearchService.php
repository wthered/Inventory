<?php

	namespace App\Services\Search;

	use App\Models\Product;
	use Illuminate\Support\Collection;

	class ProductSearchService {

		/**
		 * Search products for dropdown adjustments.
		 */
		public function search(array $filters): Collection {
			$query = Product::query();

			// Filter by Category if selected
			if (!empty($filters['category_id'])) {
				$query->where('category_id', $filters['category_id']);
			}

			// Filter by Brand if selected
			if (!empty($filters['brand_id'])) {
				$query->where('brand_id', $filters['brand_id']);
			}

			// Search text term (Name, SKU, or Barcode)
			if (!empty($filters['q'])) {
				$query->where(function ($q) use ($filters) {
					$q->where('name', 'LIKE', "%".$filters['q']."%")
					  ->orWhere('sku', 'LIKE', "%".$filters['q']."%")
					  ->orWhere('barcode', 'LIKE', "%".$filters['q']."%");
				});
			}

			// Limit results to keep Ajax payload lightweight and fast
			return $query->limit(25)->get([
				'id',
				'name',
				'sku'
			]);
		}
	}
