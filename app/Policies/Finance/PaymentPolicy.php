<?php

	namespace App\Policies\Finance;

	use App\Models\Finance\Payment;
	use App\Models\User;

	class PaymentPolicy {
		/**
		 * Determine whether the user can view any payments list.
		 */
		public function viewAny(User $user): bool {
			return $user->can('payment.view');
		}

		/**
		 * Determine whether the user can view a specific payment detail.
		 */
		public function view(User $user, Payment $payment): bool {
			return $user->can('payment.view');
		}

		/**
		 * Determine whether the user can record/create payments.
		 */
		public function create(User $user): bool {
			return $user->can('payment.create');
		}

		/**
		 * Determine whether the user can update a payment record.
		 */
		public function update(User $user, Payment $payment): bool {
			return $user->can('payment.update');
		}

		/**
		 * Determine whether the user can delete a payment record.
		 */
		public function delete(User $user, Payment $payment): bool {
			return $user->can('payment.delete');
		}

		/**
		 * Determine whether the user can restore a soft-deleted payment record.
		 */
		public function restore(User $user, Payment $payment): bool {
			return $user->can('payment.delete');
		}

		/**
		 * Determine whether the user can permanently delete a payment record.
		 */
		public function forceDelete(User $user, Payment $payment): bool {
			return $user->can('payment.delete');
		}
	}