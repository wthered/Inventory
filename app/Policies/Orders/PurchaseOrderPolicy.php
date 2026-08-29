<?php

	namespace App\Policies\Orders;

	use App\Models\Purchases\PurchaseOrder;
	use App\Models\User;

	class PurchaseOrderPolicy {
		/**
		 * Determine whether the user can view any purchase orders.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('purchase_order.view');
		}

		/**
		 * Determine whether the user can view the specific purchase order.
		 */
		public function view(User $user, PurchaseOrder $purchaseOrder): bool {
			return $user->hasPermissionTo('purchase_order.view');
		}

		/**
		 * Determine whether the user can create purchase orders.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('purchase_order.create');
		}

		/**
		 * Determine whether the user can update the purchase order.
		 */
		public function update(User $user, PurchaseOrder $order): bool {
			// Admins and users with approval authority can update any purchase order
			if ($user->hasRole('admin') || $user->hasPermissionTo('purchase_order.approve')) {
				return true;
			}

			// Standard users can only update their own created draft/pending orders
			return $user->hasPermissionTo('purchase_order.update') && $order->created_by === $user->id;
		}

		/**
		 * Determine whether the user can approve purchase orders (Purchase Manager).
		 */
		public function approve(User $user, PurchaseOrder $order): bool {
			return $user->hasPermissionTo('purchase_order.approve');
		}

		/**
		 * Δικαίωμα φυσικής παραλαβής προϊόντων (Receive Goods into Warehouse).
		 */
		public function receive(User $user, PurchaseOrder $order): bool {
			// Ο supervisor μπορεί να παραλάβει αν έχει το σχετικό δικαίωμα
			// και η παραγγελία βρίσκεται σε κατάσταση που επιτρέπει παραλαβή (π.χ. APPROVED / PARTIALLY_RECEIVED)
			return $user->hasRole('admin') || $user->hasPermissionTo('purchase_order.receive');
		}

		/**
		 * Determine whether the user can delete the purchase order.
		 */
		public function delete(User $user, PurchaseOrder $order): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('purchase_order.delete');
		}

		/**
		 * Δικαίωμα ενημέρωσης Bins/Locations & Batches κατά την παραλαβή.
		 */
		public function updateItemsLocation(User $user, PurchaseOrder $purchaseOrder): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('warehouse.manage_locations');
		}
	}
