<?php

	namespace App\Models\Scopes\Stocks;

	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Scope;
	use Illuminate\Support\Facades\Auth;

	class StockAdjustmentScope implements Scope {
		/**
		 * Apply the scope to a given Eloquent query builder.
		 */
		public function apply(Builder $builder, Model $model): void {
			if (!Auth::check()) {
				return;
			}

			$user = Auth::user();

			// Allow Admins to see all adjustments across all warehouses
			if ($user->hasRole('admin')) {
				return;
			}

			// Filter by the user's assigned warehouse
			if (isset($user->warehouse_id)) {
				$builder->where('warehouse_id', $user->warehouse_id);
			}
		}
	}