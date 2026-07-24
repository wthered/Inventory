<?php

	namespace App\Models\Sales;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Customer;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	// Προσθήκη για τη σχέση με τον User

	class SalesOrder extends Model {
		use SoftDeletes;

		protected $fillable = [
			'order_number',
			'customer_id',
			'warehouse_id',
			'status_id',
			'payment_status_id',
			'order_date',
			'shipping_date', // Διορθώθηκε από delivery_date
			'subtotal',
			'tax_amount',
			'discount_amount',
			'grand_total',
			'created_by',
			'notes',
		];

		protected $casts = [
			'order_date'        => 'date',
			'shipping_date'     => 'date', // Διορθώθηκε από delivery_date
			'status_id'         => SalesOrderStatus::class,
			'payment_status_id' => PaymentStatus::class,
		];

		/**
		 * Σχέση με τον Υπάλληλο/Χρήστη που δημιούργησε την παραγγελία
		 */
		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		/**
		 * Σχέση με το Ιστορικό / Audit Trail της παραγγελίας
		 */
		public function history(): HasMany {
			return $this->hasMany(SalesOrderHistory::class, 'sales_order_id');
		}

		/**
		 * Σχέση με τα Items της παραγγελίας
		 */
		public function items(): HasMany {
			return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
		}

		/**
		 * Σχέση με τον Πελάτη
		 */
		public function customer(): BelongsTo {
			return $this->belongsTo(Customer::class);
		}

		/**
		 * Σχέση με την Αποθήκη
		 */
		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		/**
		 * Helper για τον StockMovementObserver
		 */
		public function isReadyForStockUpdate(): bool {
			return in_array($this->status_id, [
				SalesOrderStatus::SHIPPED,
				SalesOrderStatus::DELIVERED
			]);
		}

		public function getMovementItems() {
			return $this->items;
		}

		public function getAffectedWarehouseId() {
			return $this->warehouse_id;
		}

		public function getMovementType(): string {
			return 'out';
		}
	}