<?php

	namespace App\Policies;

	use App\Models\Finance\Invoice;
	use App\Models\User;

	class InvoicePolicy {
		/**
		 * Determine whether the user can view any invoices.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('invoice.view');
		}

		/**
		 * Determine whether the user can view the specific invoice.
		 */
		public function view(User $user, Invoice $invoice): bool {
			return $user->hasPermissionTo('invoice.view');
		}

		/**
		 * Determine whether the user can create an invoice.
		 */
		public function create(User $user): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('invoice.create');
		}

		/**
		 * Determine whether the user can update/edit an invoice.
		 * Usually restricted once an invoice is finalized/posted.
		 */
		public function update(User $user, Invoice $invoice): bool {
			if ($user->hasRole('admin')) {
				return true;
			}

			return $user->hasPermissionTo('invoice.update') && $invoice->isDraft();
		}

		/**
		 * Determine whether the user can approve/post an invoice.
		 */
		public function approve(User $user, Invoice $invoice): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('invoice.approve');
		}

		/**
		 * Determine whether the user can void or issue a credit note for an invoice.
		 */
		public function void(User $user, Invoice $invoice): bool {
			return $user->hasRole('admin') || $user->hasPermissionTo('invoice.void');
		}

		/**
		 * Determine whether the user can delete the invoice.
		 */
		public function delete(User $user, Invoice $invoice): bool {
			return $user->hasRole('admin');
		}
	}
