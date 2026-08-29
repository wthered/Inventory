<?php

	namespace App\Policies\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Models\HumanResources\LeaveRequest;
	use App\Models\User;

	class LeaveRequestPolicy {
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
		 * Determine whether the user can view any leave requests.
		 */
		public function viewAny(User $user): bool {
			return $user->hasPermissionTo('leave_request.view');
		}

		/**
		 * Determine whether the user can view a specific leave request.
		 */
		public function view(User $user, LeaveRequest $leaveRequest): bool {
			if ($user->hasPermissionTo('leave_request.view')) {
				return true;
			}

			// Ο εργαζόμενος μπορεί να δει τη δική του αίτηση
			return $leaveRequest->employee_id === $user->employee?->id;
		}

		/**
		 * Determine whether the user can create leave requests.
		 */
		public function create(User $user): bool {
			return $user->hasPermissionTo('leave_request.create');
		}

		/**
		 * Determine whether the user can approve/reject the leave request.
		 */
		public function approve(User $user, LeaveRequest $leaveRequest): bool {
			// Μπορεί να εγκριθεί μόνο αν εκκρεμεί
			if ($leaveRequest->status !== LeaveStatus::PENDING) {
				return false;
			}

			return $user->hasPermissionTo('leave_request.approve');
		}

		/**
		 * Determine whether the user can cancel their own leave request.
		 */
		public function cancel(User $user, LeaveRequest $leaveRequest): bool {
			return $leaveRequest->employee_id === $user->employee?->id
				&& $leaveRequest->status === LeaveStatus::PENDING;
		}

		/**
		 * Determine whether the user can delete a leave request record.
		 */
		public function delete(User $user, LeaveRequest $leaveRequest): bool {
			return $user->hasPermissionTo('leave_request.delete');
		}
	}
