<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Casts\Attribute;

	class Account extends Model {
		public    $incrementing = false;
		protected $primaryKey   = 'username';
		protected $keyType      = 'string';

		protected $fillable = [
			'username',
			'first_name',
			'last_name',
			'phone',
			'avatar',
			'is_active',
			'last_login_at'
		];

		/**
		 * Accessor για το πλήρες ονοματεπώνυμο.
		 * Χρήση: $account->full_name
		 */
		protected function fullName(): Attribute {
			return Attribute::make(
				get: fn () => $this->first_name." ".$this->last_name,
			);
		}

		public function user(): BelongsTo {
			// Σωστή σειρά: Target Model, Foreign Key on this table, Owner Key on target table
			return $this->belongsTo(User::class, 'username', 'name');
		}
	}
