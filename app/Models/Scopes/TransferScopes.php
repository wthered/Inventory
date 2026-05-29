<?php

	namespace App\Models\Scopes;

	use Carbon\Carbon;
	use Illuminate\Contracts\Database\Query\Builder;

	trait TransferScopes {

		/**
		 * Scope a query to only include pending transfers.
		 */
		public function scopePending(Builder $query): Builder {
			return $query->where('status', 'pending');
		}

		/**
		 * Scope a query to only include approved transfers.
		 */
		public function scopeApproved(Builder $query): Builder {
			return $query->where('status', 'approved');
		}

		/**
		 * Scope a query to only include completed transfers.
		 */
		public function scopeCompleted(Builder $query): Builder {
			return $query->where('status', 'completed');
		}

		/**
		 * Scope a query to only include canceled transfers.
		 */
		public function scopeCancelled(Builder $query): Builder {
			return $query->where('status', 'cancelled');
		}

		/**
		 * Scope a query to only include transfers for a specific product.
		 */
		public function scopeForProduct(Builder $query, int $productId): Builder {
			return $query->where('product_id', $productId);
		}

		/**
		 * Scope a query to only include transfers from a specific warehouse.
		 */
		public function scopeFromWarehouse(Builder $query, int $warehouseId): Builder {
			return $query->where('source_warehouse_id', $warehouseId);
		}

		/**
		 * Scope a query to only include transfers to a specific warehouse.
		 */
		public function scopeToWarehouse(Builder $query, int $warehouseId): Builder {
			return $query->where('destination_warehouse_id', $warehouseId);
		}

		/**
		 * Scope a query to only include transfers between specific warehouses.
		 */
		public function scopeBetweenWarehouses(Builder $query, int $fromWarehouseId, int $toWarehouseId): Builder {
			return $query
				->where('source_warehouse_id', $fromWarehouseId)
				->where('destination_warehouse_id', $toWarehouseId);
		}

		/**
		 * Scope a query to only include transfers within a date range.
		 */
		public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder {
			$start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
			$end   = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

			return $query->whereBetween('transfer_date', [
				$start,
				$end
			]);
		}

		/**
		 * Scope a query to only include transfers created within a date range.
		 */
		public function scopeCreatedBetween(Builder $query, $startDate, $endDate): Builder {
			$start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
			$end   = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

			return $query->whereBetween('created_at', [
				$start,
				$end
			]);
		}

		/**
		 * Scope a query to only include recent transfers.
		 */
		public function scopeRecent(Builder $query, int $days = 7): Builder {
			return $query->where('created_at', '>=', now()->subDays($days));
		}

		/**
		 * Scope a query to only include transfers from a specific location.
		 */
		public function scopeFromLocation(Builder $query, int $locationId): Builder {
			return $query->where('source_location_id', $locationId);
		}

		/**
		 * Scope a query to only include transfers to a specific location.
		 */
		public function scopeToLocation(Builder $query, int $locationId): Builder {
			return $query->where('destination_location_id', $locationId);
		}

		/**
		 * Scope a query to only include transfers by a specific user.
		 */
		public function scopeByUser(Builder $query, int $userId): Builder {
			return $query->where('transferred_by', $userId);
		}

		/**
		 * Scope a query to only include transfers approved by a specific user.
		 */
		public function scopeApprovedBy(Builder $query, int $userId): Builder {
			return $query->where('approved_by', $userId);
		}

		/**
		 * Scope a query to only include transfers that require approval.
		 */
		public function scopeRequiringApproval(Builder $query): Builder {
			return $query
				->where('status', 'pending')
				->where('quantity', '>', 100); // Customize threshold as needed
		}

		/**
		 * Scope a query to only include large transfers (above quantity threshold).
		 */
		public function scopeLargeTransfers(Builder $query, int $threshold = 100): Builder {
			return $query->where('quantity', '>', $threshold);
		}

		/**
		 * Scope a query to only include small transfers (below quantity threshold).
		 */
		public function scopeSmallTransfers(Builder $query, int $threshold = 10): Builder {
			return $query->where('quantity', '<', $threshold);
		}

		/**
		 * Scope a query to only include transfers with notes.
		 */
		public function scopeWithNotes(Builder $query): Builder {
			return $query
				->whereNotNull('notes')
				->where('notes', '!=', '');
		}

		/**
		 * Scope a query to only include transfers without notes.
		 */
		public function scopeWithoutNotes(Builder $query): Builder {
			return $query
				->whereNull('notes')
				->orWhere('notes', '');
		}

		/**
		 * Scope a query to only include transfers sorted by newest first.
		 */
		public function scopeNewestFirst(Builder $query): Builder {
			return $query->orderBy('created_at', 'desc');
		}

		/**
		 * Scope a query to only include transfers sorted by oldest first.
		 */
		public function scopeOldestFirst(Builder $query): Builder {
			return $query->orderBy('created_at', 'asc');
		}

		/**
		 * Scope a query to only include transfers sorted by largest quantity first.
		 */
		public function scopeLargestQuantityFirst(Builder $query): Builder {
			return $query->orderBy('quantity', 'desc');
		}

		/**
		 * Scope a query to only include transfers for today.
		 */
		public function scopeToday(Builder $query): Builder {
			return $query->whereDate('created_at', today());
		}

		/**
		 * Scope a query to only include transfers for this week.
		 */
		public function scopeThisWeek(Builder $query): Builder {
			return $query->whereBetween('created_at', [
				Carbon::now()->startOfWeek(),
				Carbon::now()->endOfWeek()
			]);
		}

		/**
		 * Scope a query to only include transfers for this month.
		 */
		public function scopeThisMonth(Builder $query): Builder {
			return $query
				->whereMonth('created_at', now()->month)
				->whereYear('created_at', now()->year);
		}

		/**
		 * Scope a query to only include transfers with specific reference number pattern.
		 */
		public function scopeWithReferenceLike(Builder $query, string $pattern): Builder {
			return $query->where('reference_number', 'LIKE', "%{$pattern}%");
		}

		/**
		 * Scope a query to only include transfers that are not softly deleted.
		 * Useful when working with soft deletes.
		 */
		public function scopeActive(Builder $query): Builder {
			return $query->whereNull('deleted_at');
		}
	}
