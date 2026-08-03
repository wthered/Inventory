<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Country extends Model {
		use HasFactory, SoftDeletes;

		protected $fillable = [
			'name',
			'code',
			'code_alpha3',
			'phone_code',
			'is_active',
		];

		protected $casts = [
			'name'       => 'string',
			'code'       => 'string',
			'code_alpha' => 'string',
			'phone_code' => 'string',
			'is_active'  => 'boolean',
		];

		/**
		 * Relationship: A country has many cities.
		 */
		public function cities(): HasMany {
			return $this->hasMany(City::class);
		}
	}
