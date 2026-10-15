<?php

	namespace App\Models;

	// use Illuminate\Contracts\Auth\MustVerifyEmail;
	use App\Models\HumanResources\Employee;
	use App\Models\Inventories\InventoryAudit;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\Relations\HasOne;
	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Illuminate\Notifications\Notifiable;
	use Spatie\Permission\Traits\HasRoles;

	class User extends Authenticatable {
		use HasFactory, Notifiable, HasRoles;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var list<string>
		 */
		protected $fillable = [
			'name',
			'email',
			'email_verified_at',
			'password',
			'remember_token',
		];

		/**
		 * The attributes that should be hidden for serialization.
		 *
		 * @var list<string>
		 */
		protected $hidden = [
			'password',
			'remember_token',
		];

		public function account(): HasOne {
			return $this->hasOne(Account::class, 'username', 'name');
		}

		public function warehouses(): HasMany {
			return $this->hasMany(Warehouse::class, 'manager_id');
		}

		// Σύνδεση User -> Employee
		// Account έχουν μόνο όσοι μπορούν να κάνουν login στο σύστημα
		// Employees είναι όλοι τους
		public function employee(): HasOne {
			return $this->hasOne(Employee::class);
		}

		// Transfers που δημιούργησε ο χρήστης
		public function createdTransfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'created_by');
		}

		// Audits που δημιούργησε ο χρήστης
		public function createdAudits(): HasMany {
			return $this->hasMany(InventoryAudit::class, 'created_by');
		}

		/**
		 * Get the attributes that should be cast.
		 *
		 * @return array<string, string>
		 */
		protected function casts(): array {
			return [
				'email_verified_at' => 'datetime',
				'password'          => 'hashed',
			];
		}

		public function getMainRoleNameAttribute(): string {
			// Επιστρέφει το όνομα του πρώτου ρόλου με ωραία εμφάνιση (π.χ. Admin αντί για admin)
			$role = $this->roles->first()?->name ?? 'Staff Member with no role';
			return ucwords(str_replace('_', ' ', $role));
		}
	}
