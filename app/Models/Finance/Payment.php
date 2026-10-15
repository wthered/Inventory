<?php

	namespace App\Models\Finance;

	use App\Enums\Financial\TransactionStatus;
	use App\Models\Customer;
	use App\Models\Supplier;
	use App\Models\User;
	use DateTimeInterface;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Str;

	class Payment extends Model {
		use SoftDeletes;

		protected $fillable = [
			'payment_number',
			'is_incoming',
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
			'is_incoming' => 'boolean',
			'amount'      => 'decimal:2',
			'status'      => TransactionStatus::class,
			'paid_at'     => 'datetime',
		];

		/**
		 * Polymorphic relationship to SalesOrder, PurchaseOrder, or Invoice.
		 */
		public function payable(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * Shortcut relation to Invoice when payable is an Invoice instance.
		 */
		public function invoice(): BelongsTo {
			return $this->belongsTo(Invoice::class, 'payable_id')->where('payable_type', Invoice::class);
		}

		/**
		 * Linked Customer for inflow payments.
		 */
		public function customer(): BelongsTo {
			return $this->belongsTo(Customer::class);
		}

		/**
		 * Linked Supplier for outflow payments.
		 */
		public function supplier(): BelongsTo {
			return $this->belongsTo(Supplier::class);
		}

		/**
		 * User/Employee who recorded the payment.
		 */
		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		/* =========================================================================
		 * SCOPES
		 * ========================================================================= */

		public function scopeInflows($query) {
			return $query->where('is_incoming', true);
		}

		public function scopeOutflows($query) {
			return $query->where('is_incoming', false);
		}

		public function scopeCompleted($query) {
			return $query->where('status', TransactionStatus::COMPLETED ?? 'completed');
		}

		/**
		 * Generate a unique payment/receipt number.
		 */
		public static function generatePaymentNumber(bool $isIncoming = true, DateTimeInterface|string $date = null): string {
			$year   = $date ? Carbon::parse($date)->format('Y') : date('Y');
			$prefix = $isIncoming ? 'REC' : 'PAY';

			return sprintf('%s-%s-%s', $prefix, $year, Str::upper(Str::random(6)));
		}
	}