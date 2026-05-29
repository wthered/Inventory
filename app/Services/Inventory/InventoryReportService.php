<?php

	namespace App\Services\Inventory;

	use App\Models\InventoryTransaction;
	use App\Models\Product;
	use Illuminate\Support\Collection;

	class InventoryReportService {
		/**
		 * Get dashboard inventory report
		 */
		public function getDashboardReport(): array {
			return [
				'lowStock'        => $this->getLowStockProducts(),
				'outOfStock'      => $this->getOutOfStockProducts(),
				'totalStockValue' => $this->calculateTotalStockValue(),
				'byCategory'      => $this->getStockByCategory(),
				'recentMovements' => $this->getRecentInventoryMovements(),
			];
		}

		/**
		 * Get products with low stock (less than 10)
		 */
		public function getLowStockProducts(int $threshold = 10): Collection {
			return Product::with([
				'category',
				'inventories'
			])->whereHas('inventories', function ($query) use ($threshold) {
				$query->where('quantity', '<', $threshold);
			})->orderBy('name')->get()->map(function ($product) {
				return [
					'id'            => $product->id,
					'name'          => $product->name,
					'sku'           => $product->sku,
					'current_stock' => $product->inventories->sum('quantity'),
					'min_stock'     => $product->minimum_stock_level,
					'category'      => $product->category->name ?? 'Uncategorized',
				];
			});
		}

		/**
		 * Get out of stock products
		 */
		public function getOutOfStockProducts(): Collection {
			return Product::with([
				'category',
				'inventories'
			])
				->whereDoesntHave('inventories', function ($query) {
					$query->where('quantity', '>', 0);
				})
				->orWhereHas('inventories', function ($query) {
					$query->havingRaw('SUM(quantity) = 0');
				})
				->orderBy('name')
				->get()
				->map(function ($product) {
					return [
						'id'              => $product->id,
						'name'            => $product->name,
						'sku'             => $product->sku,
						'last_stock_date' => $product->inventories->max('updated_at'),
						'category'        => $product->category->name ?? 'Uncategorized',
					];
				});
		}

		/**
		 * Calculate total stock value
		 */
		public function calculateTotalStockValue(): float {
			return Product::with('inventories')
				->get()
				->sum(function ($product) {
					return $product->inventories->sum('quantity') * $product->cost_price;
				});
		}

		/**
		 * Get stock grouped by category
		 */
		public function getStockByCategory(): Collection {
			return Product::with([
				'category',
				'inventories'
			])
				->get()
				->groupBy('category.name')
				->map(function ($products, $categoryName) {
					return [
						'category'       => $categoryName ?: 'Uncategorized',
						'product_count'  => $products->count(),
						'total_quantity' => $products->sum(function ($product) {
							return $product->inventories->sum('quantity');
						}),
						'total_value'    => $products->sum(function ($product) {
							return $product->inventories->sum('quantity') * $product->cost_price;
						}),
					];
				})
				->sortByDesc('total_value')
				->values();
		}

		/**
		 * Get recent inventory movements
		 */
		public function getRecentInventoryMovements(int $limit = 10): Collection {
			return InventoryTransaction::with([
				'product',
				'warehouse',
				'user'
			])
				->latest()
				->limit($limit)
				->get()
				->map(function ($transaction) {
					return [
						'id'         => $transaction->id,
						'product'    => $transaction->product->name,
						'type'       => $transaction->type,
						'quantity'   => $transaction->quantity,
						'warehouse'  => $transaction->warehouse->name,
						'user'       => $transaction->user->name,
						'created_at' => $transaction->created_at->format('Y-m-d H:i'),
					];
				});
		}

		/**
		 * Generate inventory aging report
		 */
		public function getInventoryAgingReport(): Collection {
			return Product::with([
				'inventories' => function ($query) {
					$query->orderBy('updated_at', 'asc');
				}
			])->get()->map(function ($product) {
					$oldestInventory = $product->inventories->first();

					return [
						'product'           => $product->name,
						'sku'               => $product->sku,
						'total_quantity'    => $product->inventories->sum('quantity'),
						'oldest_stock_date' => $oldestInventory?->updated_at->format('Y-m-d'),
						'days_in_stock'     => $oldestInventory?->updated_at->diffInDays(now()),
					];
				})
				->sortByDesc('days_in_stock');
		}
	}
