<?php

	namespace App\Models\Scopes;

	use App\Models\Inventories\InventoryTransaction;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Scope;

	class InventoryTransactionScope implements Scope {
		/**
		 * Apply the scope to a given Eloquent query builder.
		 */
		public function apply(Builder $builder, Model $model): void {
			// This is for global scopes that apply to all queries
			// Leave empty if you only want local scopes
		}

		/**
		 * Local scopes
		 */
		public function scopeInbound(Builder $query): Builder {
			return $query->where('type', InventoryTransaction::TYPE_IN);
		}

		public function scopeOutbound(Builder $query): Builder {
			return $query->where('type', InventoryTransaction::TYPE_OUT);
		}

		public function scopeAdjustments(Builder $query): Builder {
			return $query->where('type', 'adjustment');
		}

		public function scopeTransfers(Builder $query): Builder {
			return $query->where('type', 'transfer');
		}

		public function scopeReturns(Builder $query): Builder {
			return $query->where('type', 'return');
		}

		public function scopeForProduct(Builder $query, int $productId): Builder {
			return $query->where('product_id', $productId);
		}

		public function scopeForWarehouse(Builder $query, int $warehouseId): Builder {
			return $query->where('warehouse_id', $warehouseId);
		}

		public function scopeForLocation(Builder $query, int $locationId): Builder {
			return $query->where('location_id', $locationId);
		}

		public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder {
			return $query->whereBetween('created_at', [
				$startDate,
				$endDate
			]);
		}

		public function scopeWithPositiveQuantity(Builder $query): Builder {
			return $query->where('quantity', '>', 0);
		}

		public function scopeWithNegativeQuantity(Builder $query): Builder {
			return $query->where('quantity', '<', 0);
		}

		public function scopeOfType(Builder $query, string $type): Builder {
			return $query->where('type', $type);
		}

		public function scopeOfReason(Builder $query, string $reason): Builder {
			return $query->where('reason', $reason);
		}

		public function scopeWithCostData(Builder $query): Builder {
			return $query
				->whereNotNull('cost_price')
				->whereNotNull('total_cost');
		}

		public function scopeWithoutCostData(Builder $query): Builder {
			return $query
				->whereNull('cost_price')
				->orWhereNull('total_cost');
		}

		public function scopeRecent(Builder $query, int $days = 30): Builder {
			return $query->where('created_at', '>=', Carbon::now()
				->subDays($days));
		}

		public function scopeForReference(Builder $query, string $type, int $id): Builder {
			return $query
				->where('reference_type', $type)
				->where('reference_id', $id);
		}

		public function scopeWithBatchNumber(Builder $query, string $batchNumber): Builder {
			return $query->where('batch_number', $batchNumber);
		}
	}
