<?php

	namespace App\Traits\Products;

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
				get: fn() => $this->images->first()?->image_location ?? 'https://image.tmdb.org/t/p/original/hBvaanw3RfMEs1m1blY7xwRXzul.jpg'
			);
		}

		protected function thumbnails(): Attribute {
			return Attribute::make(
				get: fn() => $this->images
			);
		}
	}