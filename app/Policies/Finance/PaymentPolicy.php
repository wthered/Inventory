<?php

namespace App\Policies\Finance;

use App\Models\Finance\Payment;
use App\Models\User;

class PaymentPolicy {
	/**
	 * Super admins bypass all checks.
	 */
	public function before(User $user, string $ability): ?bool {
		if ($user->hasRole('admin')) {
			return true;
		}

		return null;
	}

	/**
	 * Determine whether the user can view payment records.
	 */
	public function viewAny(User $user): bool {
		return $user->hasPermissionTo('payment.view');
	}

	/**
	 * Determine whether the user can view a specific payment record.
	 */
	public function view(User $user, Payment $payment): bool {
		return $user->hasPermissionTo('payment.view');
	}

	/**
	 * Determine whether the user can record a payment (Store Cashier & Accountant).
	 */
	public function create(User $user): bool {
		return $user->hasPermissionTo('payment.create');
	}

	/**
	 * Determine whether the user can update a payment record (Accountant & Controllers).
	 */
	public function update(User $user, Payment $payment): bool {
		return $user->hasPermissionTo('payment.update');
	}

	/**
	 * Determine whether the user can approve/reconcile payments.
	 */
	public function approve(User $user, Payment $payment): bool {
		return $user->hasPermissionTo('payment.approve');
	}

	/**
	 * Determine whether the user can delete a payment record.
	 */
	public function delete(User $user, Payment $payment): bool {
		return $user->hasPermissionTo('payment.delete');
	}
}
