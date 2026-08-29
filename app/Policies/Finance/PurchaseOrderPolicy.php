<?php

	namespace App\Policies\Finance;

	use App\Models\Purchases\PurchaseOrder;
	use App\Models\User;

	class PurchaseOrderPolicy {
		/**
		 * Προβολή λίστας παραγγελιών αγοράς.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('purchase_order.view');
		}

		/**
		 * Προβολή συγκεκριμένης παραγγελίας.
		 */
		public function view(User $user, PurchaseOrder $purchaseOrder): bool {
			return $user->hasPermissionTo('purchase_order.view');
		}

		/**
		 * Δημιουργία παραγγελίας αγοράς.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('purchase_order.create');
		}

		/**
		 * Ενημέρωση παραγγελίας.
		 * Επιτρέπεται αν είναι σε κατάσταση DRAFT/AWAITING_APPROVAL.
		 */
		public function update(User $user, PurchaseOrder $purchaseOrder): bool {
			if (!$purchaseOrder->isEditable()) {
				return false;
			}

			if ($user->hasRole('admin') || $user->hasPermissionTo('purchase_order.approve')) {
				return true;
			}

			return $user->hasPermissionTo('purchase_order.update') && $purchaseOrder->created_by === $user->id;
		}

		/**
		 * Έγκριση Αγοράς (Purchase Manager / Financial Controller).
		 */
		public function approve(User $user, PurchaseOrder $purchaseOrder): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('purchase_order.approve');
		}

		/**
		 * Ειδική Οικονομική Έγκριση (Financial Controller) για μεγάλες παραγγελίες.
		 * Π.χ., αν το grand_total υπερβαίνει ένα όριο (π.χ. 10.000€).
		 */
		public function financialApprove(User $user, PurchaseOrder $purchaseOrder): bool {
			if ($user->hasRole('admin')) {
				return true;
			}

			// Αν η παραγγελία είναι άνω των 10.000, απαιτείται ειδικό permission Financial Controller
			if ($purchaseOrder->grand_total > 10000) {
				return $user->hasPermissionTo('purchase_order.financial_approve');
			}

			return $user->hasPermissionTo('purchase_order.approve');
		}

		/**
		 * Διαγραφή παραγγελίας.
		 */
		public function delete(User $user, PurchaseOrder $purchaseOrder): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('purchase_order.delete');
		}
	}
