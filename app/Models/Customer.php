<?php

	namespace App\Models;

	use App\Enums\Financial\PaymentTerms;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Customer extends Model {
		use softDeletes;

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
			'country',
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
			'tax_number'       => 'integer',    // 18 looks like an integer
			'billing_address'  => 'string',
			'shipping_address' => 'string',
			'city'             => 'string',
			'state'            => 'string',
			'country'          => 'string',
			'postal_code'      => 'integer',     // zip codes often contain hyphens → keep as string
			'customer_type'    => 'string',
			'credit_limit'     => 'decimal:2',  // money with 2 decimal places
			'payment_terms'    => PaymentTerms::class,
			'notes'            => 'string',
			'is_active'        => 'boolean',
		];
	}
