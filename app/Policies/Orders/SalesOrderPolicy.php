<?php

	namespace App\Policies\Orders;

	use App\Enums\Inventory\MovementStatus;
	use App\Models\Sales\SalesOrder;
	use App\Models\User;

	class SalesOrderPolicy {
		/**
		 * Perform pre-authorization checks. Super admins bypass all permission checks.
		 */
		public function before(User $user, string $ability): ?bool {
			if ($user->hasRole('admin')) {
				return true;
			}

			return null; // Fallback to standard policy methods
		}

		/**
		 * Determine whether the user can view any sales orders.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('sales_order.view');
		}

		/**
		 * Determine whether the user can view the specific sales order.
		 */
		public function view(User $user, SalesOrder $salesOrder): bool {
			return $user->hasPermissionTo('sales_order.view');
		}

		/**
		 * Determine whether the user can create sales orders.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('sales_order.create');
		}

		/**
		 * Determine whether the user can update the sales order.
		 */
		public function update(User $user, SalesOrder $salesOrder): bool {
			if ($user->hasPermissionTo('sales_order.approve')) {
				return true;
			}

			return $user->hasPermissionTo('sales_order.update') && $salesOrder->created_by === $user->id;
		}

		/**
		 * Determine whether the user can approve sales orders (Sales Supervisor & Manager).
		 */
		public function approve(User $user, SalesOrder $salesOrder): bool {
			return $user->hasPermissionTo('sales_order.approve');
		}

		/**
		 * Determine whether the user can delete the sales order.
		 */
		public function delete(User $user, SalesOrder $salesOrder): bool {
			return $user->hasPermissionTo('sales_order.delete');
		}

		/**
		 * Determine whether the user can cancel the sales order.
		 */
		public function cancel(User $user, SalesOrder $salesOrder): bool {
			if ($user->hasPermissionTo('sales_order.approve')) {
				return true;
			}

			// Sales rep can cancel their own order only if it's still in draft/pending state
			return $user->hasPermissionTo('sales_order.update')
				&& $salesOrder->created_by === $user->id
				&& $salesOrder->status_id === MovementStatus::DRAFT->value;
		}

		/**
		 * Determine whether the user can ship/dispatch the sales order (Inventory/Warehouse).
		 */
		public function ship(User $user, SalesOrder $salesOrder): bool {
			return $user->hasPermissionTo('sales_order.ship');
		}
	}
