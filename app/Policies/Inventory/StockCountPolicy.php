<?php

	namespace App\Policies\Inventory;

	use App\Models\User;

	class StockCountPolicy {
		/**
		 * Determine whether the user can view any stock counts.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('stock_count.view');
		}

		/**
		 * Determine whether the user can view the specific stock count.
		 */
		public function view(User $user): bool {
			return $user->hasPermissionTo('stock_count.view');
		}

		/**
		 * Determine whether the user can start/create a stock count session.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('stock_count.create');
		}

		/**
		 * Determine whether the user can record/edit counted quantities.
		 */
		public function update(User $user): bool {
			return $user->hasPermissionTo('stock_count.update');
		}

		/**
		 * Determine whether the user can approve/finalize the stock count (Supervisor/Manager).
		 */
		public function approve(User $user): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('stock_count.approve');
		}

		/**
		 * Determine whether the user can delete a stock count record.
		 */
		public function delete(User $user): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('stock_count.delete');
		}
	}
