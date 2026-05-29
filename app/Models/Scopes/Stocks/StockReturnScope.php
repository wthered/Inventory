<?php

	namespace App\Models\Scopes\Stocks;

	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Scope;
	use Illuminate\Support\Facades\Auth;

	class StockReturnScope implements Scope {
		/**
		 * Apply the scope to a given Eloquent query builder.
		 * * Purpose: Automatically filter returns based on user permissions
		 * and warehouse assignment.
		 */
		public function apply(Builder $builder, Model $model): void {
			// 1. If not logged in (e.g., console/seeding), do nothing
			if (!Auth::check()) {
				return;
			}

			$user = Auth::user();

			// 2. Admins can see everything
			if ($user->hasRole('admin')) {
				return;
			}

			// 3. For Warehouse Managers or Clerks, filter by their assigned warehouse
			// Assuming your User model has a 'warehouse_id' column
			if (isset($user->warehouse_id)) {
				$builder->where('warehouse_id', $user->warehouse_id);
			}
		}
	}