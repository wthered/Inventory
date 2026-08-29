<?php

	namespace App\Policies\Inventory;

	use App\Models\Inventories\InventoryTransaction;
	use App\Models\User;

	class InventoryTransactionPolicy {
		/**
		 * Determine whether the user can view transaction logs/audit trail.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('inventory.view');
		}

		/**
		 * Determine whether the user can view a specific transaction record.
		 */
		public function view(User $user, InventoryTransaction $transaction): bool {
			return $user->hasPermissionTo('inventory.view');
		}

		/**
		 * Determine whether the user can record a new stock adjustment or movement.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('stock_count.create')
				|| $user->hasPermissionTo('purchase_order.receive')
				|| $user->hasPermissionTo('sales_order.ship');
		}

		/**
		 * Audit logs/transactions should NEVER be updated directly to preserve ledger integrity.
		 */
		public function update(User $user, InventoryTransaction $transaction): bool {
			return false;
		}

		/**
		 * Audit logs/transactions should NEVER be deleted directly.
		 */
		public function delete(User $user, InventoryTransaction $transaction): bool {
			return false;
		}
	}
