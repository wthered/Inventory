<?php

	namespace App\Policies;

	use App\Models\User;
	use App\Models\Warehouse;

	class WarehousePolicy {
		/**
		 * Determine whether the user can view any models.
		 */
		public function viewAny(User $user): bool {
			// Admins or anyone with view permissions can see the list
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.view');
		}

		/**
		 * Determine whether the user can view the model.
		 */
		public function view(User $user, Warehouse $warehouse): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.view');
		}

		/**
		 * Determine whether the user can create models.
		 */
		public function create(User $user): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.manage');
		}

		/**
		 * Determine whether the user can update the model.
		 */
		public function update(User $user, Warehouse $warehouse): bool {
			// 1. Admin bypass
			if ($user->hasRole('admin')) {
				return true;
			}

			// 2. Warehouse managers can update only if they have the global permission
			// AND they are explicitly assigned to this specific warehouse
			return $user->hasPermissionTo('warehouse.update') && $warehouse->manager_id === $user->id;
		}

		/**
		 * Determine whether the user can delete the model.
		 */
		public function delete(User $user, Warehouse $warehouse): bool {
			// Safe protection: Typically only admins or supreme accounts can drop entire entities
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.manage');
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
