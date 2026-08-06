<?php

	namespace App\Enums\Stock;

	use App\Models\Product;

	enum ProductStockStatus: int {
		case CRITICAL     = 0;
		case LOW          = 1;
		case NORMAL       = 2;
		case GOOD         = 3;
		case OVERSTOCK    = 4;
		case OUT_OF_STOCK = 5;
		case UNTRACKED    = 6;
		case BACKORDER    = 7;
		case DISCONTINUED = 8;

		/**
		 * Get short, clean label for dropdowns and tables.
		 */
		public function label(): string {
			return match ($this) {
				self::CRITICAL     => 'Critical Stock',
				self::LOW          => 'Low Stock',
				self::NORMAL       => 'Normal Stock',
				self::GOOD         => 'Good Stock',
				self::OVERSTOCK    => 'Overstock',
				self::OUT_OF_STOCK => 'Out of Stock',
				self::UNTRACKED    => 'Untracked',
				self::BACKORDER    => 'Backorder',
				self::DISCONTINUED => 'Discontinued',
			};
		}

		/**
		 * Get detailed contextual description message for reports/alerts.
		 */
		public function message(float $quantity, Product $product): string {
			return match ($this) {
				self::CRITICAL     => "Only ".$quantity." units left (below 20% of minimum)",
				self::LOW          => $quantity." units (below minimum stock of ".$product->min_stock_level.")",
				self::NORMAL       => $quantity." units (between min and 80% of max)",
				self::GOOD         => $quantity." units (between 80% and max)",
				self::OVERSTOCK    => $quantity." units (exceeds max of ".$product->max_stock_level.")",
				self::OUT_OF_STOCK => "None Available",
				self::UNTRACKED    => "Inventory tracking is disabled for this product",
				self::BACKORDER    => "Currently out of stock but accepting orders",
				self::DISCONTINUED => "Item is no longer stocked or active",
			};
		}

		/**
		 * Get hex color code for UI badges, charts, or inline styles.
		 */
		public function color(): string {
			return match ($this) {
				self::CRITICAL, self::OUT_OF_STOCK  => '#DC3545', // Red
				self::LOW                           => '#FFC107', // Amber / Yellow
				self::NORMAL                        => '#0D6EFD', // Primary Blue
				self::GOOD                          => '#198754', // Green
				self::OVERSTOCK                     => '#6F42C1', // Purple
				self::BACKORDER                     => '#0DCAF0', // Cyan
				self::UNTRACKED, self::DISCONTINUED => '#6C757D', // Gray
			};
		}

		/**
		 * Helper to check if stock can be purchased.
		 */
		public function isPurchasable(): bool {
			return match ($this) {
				self::CRITICAL, self::LOW, self::NORMAL, self::GOOD, self::OVERSTOCK, self::UNTRACKED, self::BACKORDER => true,
				self::OUT_OF_STOCK, self::DISCONTINUED                                                                 => false,
			};
		}
	}