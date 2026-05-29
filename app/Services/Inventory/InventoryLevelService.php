<?php

	namespace App\Services\Inventory;

	use App\Models\Product;
	use Illuminate\Support\Collection;

	class InventoryLevelService {
		/**
		 * Get all products with their inventory tiers
		 */
		public function getAllProductsInventory(): Collection {
			$products = Product::query()
				->pluck('id');
			$result   = Collection::empty();

			foreach ($products as $product) {
				$result->push($this->getInventoryAnalysis($product->id));
			}
			dd($result);
			return $result;
		}

		/**
		 * Get detailed inventory level analysis
		 */
		public function getInventoryAnalysis(int $productId): array {
			$product         = Product::findOrFail($productId);
			$currentQuantity = $this->getCurrentQuantity($product);
			$tier = $this->calculateTierForQuantity($currentQuantity['available_quantity'], $product->min_stock_level, $product->max_stock_level);

			return [
				'product_id'        => $productId,
				'product_name'      => $product->name,
				'current_quantity'  => $currentQuantity,
				'min_stock'         => $product->min_stock_level,
				'max_stock'         => $product->max_stock_level,
				'tier'              => $tier,
				'tier_label'        => $this->getTierLabel($tier),
				'percentage_of_max' => $product->max_stock_level > 0 ? ($currentQuantity['available_quantity'] / $product->max_stock_level) * 100 : 0,
				'status'            => $this->getStatus($tier, $currentQuantity['available_quantity'], $product),
				'suggested_action'  => $this->getSuggestedAction($tier, $currentQuantity['available_quantity'], $product),
			];
		}

		/**
		 * Get current total inventory quantity for product
		 */
		public function getCurrentQuantity(Product $product): Collection {
			$inventories = Collection::empty();
			$quantity    = 0;
			$available   = 0;
			$product->inventories()->whereHas('location')->each(function ($inventory) use (&$inventories, &$quantity, &$available) {
				$inventories->push([
					'warehouse_id'       => $inventory->warehouse_id,
					'location_id'        => $inventory->location_id,
					'quantity'           => $inventory->quantity,
					'available_quantity' => $inventory->available_quantity,
				]);
				$quantity += $inventory->quantity;
				$available += $inventory->available_quantity;
			});
			return Collection::make(['quantity' => $quantity, 'available_quantity' => $available]);
		}

		/**
		 * Calculate tier for specific quantity
		 */
		public function calculateTierForQuantity(float $currentQuantity, float $minStock, ?float $maxStock): int {
			// Prevent division by zero
			if ($maxStock <= 0 || is_null($maxStock)) {
				return 0;
			}

			// Calculate percentages
			$criticalThreshold  = $minStock * 0.2;
			$eightyPercentOfMax = $maxStock * 0.8;

			// Determine tier
			if ($currentQuantity <= $criticalThreshold) {
				return 0; // Critical
			} elseif ($currentQuantity <= $minStock) {
				return 1; // Low
			} elseif ($currentQuantity <= $eightyPercentOfMax) {
				return 2; // Normal
			} elseif ($currentQuantity <= $maxStock) {
				return 3; // Good
			} else {
				return 4; // Overstock
			}
		}

		/**
		 * Get tier label
		 */
		protected function getTierLabel(int $tier): string {
			$labels = [
				0 => 'critical',
				1 => 'low',
				2 => 'normal',
				3 => 'good',
				4 => 'overstock',
			];

			return $labels[$tier] ?? 'unknown';
		}

		/**
		 * Get status message
		 */
		protected function getStatus(int $tier, float $quantity, Product $product): string {
			return match ($tier) {
				0 => "CRITICAL: Only " . $quantity . " units left (below 20% of minimum)",
				1 => "LOW: " . $quantity . " units (below minimum stock of " . $product->min_stock_level . ")",
				2 => "NORMAL: " . $quantity . " units (between min and 80% of max)",
				3 => "GOOD: " . $quantity . " units (between 80% and max)",
				4 => "OVERSTOCK: " . $quantity . " units (exceeds max of " . $product->max_stock_level . ")",
				default => "UNKNOWN",
			};
		}

		/**
		 * Get suggested action
		 */
		protected function getSuggestedAction(int $tier, float $quantity, Product $product): string {
			switch ($tier) {
				case 0:
					$needed = $product->min_stock_level - $quantity;
					return "URGENT: Order " . $needed . " units immediately";
				case 1:
					$needed = $product->min_stock_level - $quantity;
					return "Order " . $needed . " units soon";
				case 2:
					return "Monitor stock levels";
				case 3:
					return "Optimal stock level";
				case 4:
					$excess = $quantity - $product->max_stock_level;
					return "Consider discounting or transferring " . $excess . " units";
				default:
					return "Review stock levels";
			}
		}

		/**
		 * Get products by tier
		 */
		public function getProductsByTier(int $tier): Collection {
			$products = Product::query()
				->pluck('id');
			$result   = Collection::empty();

			foreach ($products as $product) {
				$currentTier = $this->calculateTier($product->id);

				if ($currentTier === $tier) {
					$result->push($this->getInventoryAnalysis($product->id));
				}
			}
			return $result;
		}

		/**
		 * Calculate inventory level tier (0-4)
		 *
		 * Tier 0: Critical (below 20% of min)
		 * Tier 1: Low (20% - min stock)
		 * Tier 2: Normal (min - 80% of max)
		 * Tier 3: Good (80% - max)
		 * Tier 4: Overstock (above max)
		 */
		public function calculateTier(int $productId): int {
			$product = Product::with('inventories')
				->findOrFail($productId);

			// Get current total quantity
			$currentQuantity = $this->getCurrentQuantity($product);

			// Calculate tiers
			return $this->calculateTierForQuantity($currentQuantity, $product->min_stock_level, $product->max_stock_level);
		}

		/**
		 * Check if product needs reorder
		 */
		public function needsReorder(int $productId): bool {
			$product         = Product::findOrFail($productId);
			$currentQuantity = $this->getCurrentQuantity($product);
			$reorderPoint    = $this->calculateReorderPoint($product);

			return $currentQuantity <= $reorderPoint;
		}

		/**
		 * Calculate reorder point
		 */
		public function calculateReorderPoint(Product $product): float {
			// Reorder point is typically min stock + safety stock
			$safetyStock = $product->min_stock_level * 0.3; // 30% safety stock
			return $product->min_stock_level + $safetyStock;
		}
	}