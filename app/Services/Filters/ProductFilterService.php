<?php

	namespace App\Services\Filters;

	use App\Models\Product;
	use Illuminate\Pagination\LengthAwarePaginator;

	class ProductFilterService {
		public function filter(array $filters): LengthAwarePaginator {
			$query = Product::query()->with([
				'images',
				'category',
				'brand',
				'inventories'
			]);

			// Φίλτρο Κατηγορίας
			if (!empty($filters['category'])) {
				$query->where('category_id', $filters['category']);
			}

			// Φίλτρο Προμηθευτή
			if (!empty($filters['supplier'])) {
				$query->whereHas('suppliers', function ($q) use ($filters) {
					$q->where('suppliers.id', $filters['supplier']);
				});
			}

			// Φίλτρο Stock Status
			if (!empty($filters['status'])) {
				$status = $filters['status'];
				if ($status === 'Out') {
					$query->where('current_stock', '<=', 0);
				} elseif ($status === 'Low') {
					$query->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock_level');
				} elseif ($status === 'Stock') {
					$query->whereColumn('current_stock', '>', 'min_stock_level');
				}
			}

			return $query->latest()->paginate(25);
		}
	}