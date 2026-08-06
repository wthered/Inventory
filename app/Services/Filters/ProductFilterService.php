<?php

	namespace App\Services\Filters;

	use App\Enums\Stock\ProductStockStatus;
	use App\Models\Product;
	use Illuminate\Pagination\LengthAwarePaginator;

	class ProductFilterService {
		public function filter(array $filters): LengthAwarePaginator {
//			dd($filters);

			$query = Product::query()->with([
				'images',
				'category',
				'brand',
				'inventories'
			]);

			// Φίλτρο Κατηγορίας
			if (!empty($filters['category'])) {
				$query->where('category_id', $filters['child_category']);
			}

			// Φίλτρο Προμηθευτή
			if (!empty($filters['supplier'])) {
				$query->whereHas('suppliers', function ($q) use ($filters) {
					$q->where('suppliers.id', $filters['supplier']);
				});
			}

			// Φίλτρο Brands
			if (!empty($filters['brand'])) {
				$query->whereHas('brand', function ($q) use ($filters) {
					$q->where('brands.id', $filters['brand']);
				});
			}

			// Φίλτρο Stock Status
			if (!empty($filters['status'])) {
				// Attempt to convert the filter value to a valid Enum instance
				$statusEnum = ProductStockStatus::tryFrom($filters['status']);

				if ($statusEnum) {
					match ($statusEnum) {
						ProductStockStatus::OUT_OF_STOCK => $query->where('current_stock', '<=', 0),

						ProductStockStatus::CRITICAL     => $query->where('current_stock', '>', 0)
						                                          ->whereColumn('current_stock', '<=', 'min_stock_level'),

						ProductStockStatus::LOW          => $query->whereColumn('current_stock', '>', 'min_stock_level')
						                                          ->whereColumn('current_stock', '<=', 'reorder_point'),

						ProductStockStatus::NORMAL       => $query->whereColumn('current_stock', '>', 'reorder_point')
						                                          ->where(function ($q) {
							                                          $q->whereNull('max_stock_level')
							                                            ->orWhereColumn('current_stock', '<', 'max_stock_level');
						                                          }),

						ProductStockStatus::OVERSTOCK    => $query->whereNotNull('max_stock_level')
						                                          ->whereColumn('current_stock', '>=', 'max_stock_level'),
					};
				}
			}

//			dd($query->toRawSql());

			return $query->latest()->paginate(25);
		}
	}