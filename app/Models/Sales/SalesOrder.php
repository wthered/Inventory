<?php

	namespace App\Models\Sales;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Customer;
	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class SalesOrder extends Model {
		use SoftDeletes;

		// Αν έχεις soft deletes στο migration σου

		protected $fillable = [
			'order_number',
			'customer_id',
			'warehouse_id',
			'order_date',
			'delivery_date',
			'status',
			'payment_status',
			'subtotal',
			'tax_amount',
			'total_amount',
			'notes',
			'created_by',
			'status_id',
			'payment_status_id',
			'discount_amount',
			'grand_total',
		];

		/**
		 * Το "κλειδί" για να δουλεύουν σωστά τα Enums
		 */
		protected $casts = [
			'order_date'     => 'date',
			'delivery_date'  => 'date',
			'status'         => SalesOrderStatus::class,
			'payment_status' => PaymentStatus::class,
		];

		/**
		 * Σχέση με τα Items της παραγγελίας
		 */
		public function items(): HasMany {
			return $this->hasMany(SalesOrderItem::class);
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
		 * Εδώ ορίζεις πότε μια παραγγελία θεωρείται έτοιμη για αφαίρεση αποθέματος
		 */
		public function isReadyForStockUpdate(): bool {
			return in_array($this->status, [
				SalesOrderStatus::SHIPPED,
				SalesOrderStatus::DELIVERED
			]);
		}

		/**
		 * Helpers για τον Observer (για να ξέρει τι να αφαιρέσει)
		 */
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
