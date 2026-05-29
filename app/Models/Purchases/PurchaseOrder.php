<?php

	namespace App\Models\Purchases;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Supplier;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class PurchaseOrder extends Model {
		use SoftDeletes;

		protected $fillable = [
			'supplier_id',
			'warehouse_id',
			'order_number',
			'order_date',
			'expected_delivery_date',
			'actual_delivery_date',
			'status',
			'payment_status',
			'subtotal',
			'tax_amount',
			'discount_amount',
			'shipping_cost',
			'total_amount',
			'notes',
			'reference_number',
			'created_by',
			'approved_by',
			'approved_at',
		];

		protected $casts = [
			'status'                 => SalesOrderStatus::class,
			'payment_status'         => PaymentStatus::class,
			'order_date'             => 'date',
			'expected_delivery_date' => 'date',
			'actual_delivery_date'   => 'date',
			'approved_at'            => 'datetime',
		];

		/**
		 * Relationships
		 */

		public function supplier(): BelongsTo {
			return $this->belongsTo(Supplier::class);
		}

		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		public function items(): HasMany {
			return $this->hasMany(PurchaseOrderItem::class);
		}
	}
