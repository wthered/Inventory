<?php

	namespace App\Models\Finance;

	use App\Enums\Financial\InvoiceStatus;
	use App\Models\Customer;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\Relations\MorphMany;
	use Illuminate\Database\Eloquent\Relations\MorphTo;

	class Invoice extends Model {
		protected $fillable = [
			'invoice_number',
			'payable_type',
			'payable_id',
			'customer_id',
			'invoice_date',
			'due_date',
			'subtotal',
			'tax',
			'total',
			'status',
			'notes',
		];

		protected $casts = [
			'status'       => InvoiceStatus::class,
			'invoice_date' => 'date',
			'due_date'     => 'date',
			'subtotal'     => 'decimal:2',
			'tax'          => 'decimal:2',
			'total'        => 'decimal:2',
		];

		/**
		 * Polymorphic relationship to linked order (e.g., SalesOrder / PurchaseOrder).
		 */
		public function payable(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * Customer associated with this invoice.
		 */
		public function customer(): BelongsTo {
			return $this->belongsTo(Customer::class);
		}

		/**
		 * Line items in the invoice.
		 */
		public function items(): HasMany {
			return $this->hasMany(InvoiceItem::class);
		}

		/**
		 * Payments associated with this invoice via polymorphic morphMany.
		 */
		public function payments(): MorphMany {
			return $this->morphMany(Payment::class, 'payable');
		}
	}