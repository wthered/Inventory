<?php

	namespace App\Policies;

	use App\Models\User;
	use App\Models\Warehouse;

	class WarehousePolicy {
		/**
		 * Determine whether the user can view any models.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('warehouse.view');
		}

		/**
		 * Determine whether the user can view the model.
		 */
		public function view(User $user, Warehouse $warehouse): bool {
			return $user->hasPermissionTo('warehouse.view');
		}

		/**
		 * Determine whether the user can create models.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('warehouse.create') || $user->hasPermissionTo('warehouse.manage');
		}

		/**
		 * Determine whether the user can update the model.
		 */
		public function update(User $user, Warehouse $warehouse): bool {
			if ($user->hasRole('admin') || $user->hasPermissionTo('warehouse.manage')) {
				return true;
			}

			return $user->hasPermissionTo('warehouse.update') && $warehouse->manager_id === $user->id;
		}

		/**
		 * Determine whether the user can delete the model.
		 */
		public function delete(User $user, Warehouse $warehouse): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.delete');
		}

		/**
		 * Determine whether the user can restore the model.
		 */
		public function restore(User $user, Warehouse $warehouse): bool {
			return $user->hasRole('admin');
		}

		/**
		 * Determine whether the user can permanently delete the model.
		 */
		public function forceDelete(User $user, Warehouse $warehouse): bool {
			return $user->hasRole('admin');
		}
	}
