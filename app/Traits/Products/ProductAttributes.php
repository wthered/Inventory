<?php

	namespace App\Traits\Products;

	use App\Enums\Stock\ProductStockStatus;
	use Illuminate\Database\Eloquent\Casts\Attribute;

	trait ProductAttributes {
		/**
		 * Calculate total available stock across all warehouses.
		 * Accessible via $product->total_stock
		 */
		protected function totalStock(): Attribute {
			return Attribute::make(get: fn() => $this->inventories->sum('available_quantity'));
		}

		/**
		 * Determine if the product needs to be reordered based on total stock.
		 * Accessible via $product->needs_reorder
		 */
		protected function needsReorder(): Attribute {
			return Attribute::make(get: fn() => $this->total_stock <= $this->reorder_point);
		}

		protected function displayImage(): Attribute {
			return Attribute::make(
				get: fn(
				) => $this->images->first()?->image_location ?? 'https://image.tmdb.org/t/p/original/hBvaanw3RfMEs1m1blY7xwRXzul.jpg'
			);
		}

		protected function thumbnails(): Attribute {
			return Attribute::make(
				get: fn() => $this->images
			);
		}

		/**
		 * Determine the 5-tier stock status of the product.
		 * Accessible via $product->stock_status
		 */
		protected function stockStatus(): Attribute {
			return Attribute::make(
				get: function () {
					// Use total_stock if inventory tracking across warehouses is active,
					// otherwise fall back to current_stock
					$stock = $this->relationLoaded('inventories') ? $this->total_stock : $this->current_stock;

					if ($stock <= 0) {
						return ProductStockStatus::OUT_OF_STOCK;
					}

					if ($this->min_stock_level > 0 && $stock <= $this->min_stock_level) {
						return ProductStockStatus::CRITICAL;
					}

					if ($this->reorder_point > 0 && $stock <= $this->reorder_point) {
						return ProductStockStatus::LOW;
					}

					if ($this->max_stock_level && $stock >= $this->max_stock_level) {
						return ProductStockStatus::OVERSTOCK;
					}

					return ProductStockStatus::NORMAL;
				}
			);
		}
	}