<?php

	namespace App\Models;

	use App\Enums\Financial\PaymentTerms;
	use App\Models\Sales\SalesOrder;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Customer extends Model {
		use SoftDeletes, HasFactory;

		protected $fillable = [
			'name',
			'code',
			'email',
			'phone',
			'company_name',
			'tax_number',
			'billing_address',
			'shipping_address',
			'city',
			'state',
			'country_id',
			'postal_code',
			'customer_type',
			'credit_limit',
			'payment_terms',
			'notes',
			'is_active',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'code'             => 'string',
			'name'             => 'string',
			'email'            => 'string',
			'phone'            => 'string',
			'company_name'     => 'string',
			'tax_number'       => 'string',    // 18 looks like an integer
			'billing_address'  => 'string',
			'shipping_address' => 'string',
			'city'             => 'string',
			'state'            => 'string',
			'country'          => 'string',
			'postal_code'      => 'string',     // zip codes often contain hyphens → keep as string
			'customer_type'    => 'string',
			'credit_limit'     => 'decimal:2',  // money with 2 decimal places
			'payment_terms'    => PaymentTerms::class,
			'notes'            => 'string',
			'is_active'        => 'boolean',
		];

		/**
		 * Relationship: A Customer has many Sales.
		 */
		public function sales(): HasMany {
			return $this->hasMany(SalesOrder::class);
		}

		public function country(): BelongsTo {
			return $this->belongsTo(Country::class);
		}
	}
