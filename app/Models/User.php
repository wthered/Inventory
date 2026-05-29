<?php

	namespace App\Models;

	// use Illuminate\Contracts\Auth\MustVerifyEmail;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
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
