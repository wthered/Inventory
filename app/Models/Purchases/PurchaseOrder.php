<?php

	namespace App\Models\Purchases;

	use App\Enums\Purchases\PurchaseOrderStatus;
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
			'po_number',
			'supplier_id',
			'warehouse_id',
			'status_id',
			'order_date',
			'expected_date',
			'received_at',
			'subtotal',
			'tax_amount',
			'discount_amount',
			'grand_total',
			'created_by',
			'notes',
		];

		protected $casts = [
			'status_id'     => PurchaseOrderStatus::class,
			'order_date'    => 'date',
			'expected_date' => 'date',
			'received_at'   => 'datetime',
		];

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

		/**
		 * Get all history audit logs for the purchase order.
		 */
		public function history(): HasMany {
			return $this->hasMany(PurchaseOrderHistory::class, 'purchase_order_id');
		}

		// Used by Show View File
		public function isEditable(): bool {
			return in_array($this->status_id, [PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::AWAITING_APPROVAL]);
		}
	}