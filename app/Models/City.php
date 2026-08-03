<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class City extends Model {
		use HasFactory, SoftDeletes;

		protected $fillable = [
			'country_id',
			'name',
			'state',
			'postal_code',
			'is_active',
		];

		protected $casts = [
			'country_id'  => 'integer',
			'name'        => 'string',
			'state'       => 'string',
			'postal_code' => 'string',
			'is_active'   => 'boolean',
		];

		/**
		 * Relationship: A City belongs to a Country.
		 */
		public function country(): BelongsTo {
			return $this->belongsTo(Country::class);
		}
	}