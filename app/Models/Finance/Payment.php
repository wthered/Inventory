<?php

	namespace App\Models\Finance;

	use App\Models\Customer;
	use App\Models\Supplier;
	use App\Models\User;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Payment extends Model {
		use SoftDeletes;

		protected $fillable = [
			'payment_number',
			'type',
			'payable_type',
			'payable_id',
			'customer_id',
			'supplier_id',
			'amount',
			'currency',
			'payment_method',
			'status',
			'paid_at',
			'transaction_reference',
			'created_by',
			'notes',
		];

		protected $casts = [
			'amount'  => 'decimal:2',
			'paid_at' => 'datetime',
		];

		/**
		 * Πολυμορφική σχέση με SalesOrder ή PurchaseOrder.
		 */
		public function payable(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * Σύνδεση με Πελάτη (αν πρόκειται για είσπραξη).
		 */
		public function customer(): BelongsTo {
			return $this->belongsTo(Customer::class);
		}

		/**
		 * Σύνδεση με Προμηθευτή (αν πρόκειται για πληρωμή).
		 */
		public function supplier(): BelongsTo {
			return $this->belongsTo(Supplier::class);
		}

		/**
		 * Ο χρήστης/υπάλληλος που καταχώρησε την πληρωμή.
		 */
		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		/* =========================================================================
		 * SCOPES FOR EASY QUERYING
		 * ========================================================================= */

		public function scopeInflows($query) {
			return $query->where('is_incoming', true);
		}

		public function scopeOutflows($query) {
			return $query->where('is_incoming', false);
		}

		public function scopeCompleted($query) {
			return $query->where('status', 'completed');
		}
	}
