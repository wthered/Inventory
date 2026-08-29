<?php

	namespace App\Traits;

	use App\Models\User;
	use Illuminate\Database\Eloquent\Builder;

	trait HasUserScope {
		/**
		 * Scope a query to filter records based on user permissions or ownership.
		 */
		public function scopeForUser(
			Builder $query,
			User $user,
			string $permissionToBypass = 'manage',
			string $ownerColumn = 'created_by'
		): Builder {
			// Admins or users with the bypass/manager permission see all records
			if ($user->hasRole('admin') || ($permissionToBypass && $user->hasPermissionTo($permissionToBypass))) {
				return $query;
			}

			// Standard users only see records assigned to or created by them
			return $query->where($ownerColumn, $user->id);
		}
	}
