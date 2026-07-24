<?php

	namespace App\Models\Sales;

	use App\Contracts\StockMoveable;
	use App\Models\Product;
	use App\Models\WarehouseLocation; // Προσθήκη για το Bin location
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class SalesOrderItem extends Model implements StockMoveable {

		protected $fillable = [
			'sales_order_id',
			'product_id',
			'batch_number',
			'location_id',
			'quantity_ordered',
			'quantity_shipped',
			'unit_price',
			'discount_rate',
			'discount_amount',
		];

		/**
		 * Επιστροφή στην παραγγελία (Header)
		 */
		public function sale(): BelongsTo {
			return $this->belongsTo(SalesOrder::class, 'sales_order_id');
		}

		/**
		 * Σύνδεση με το Προϊόν
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		/**
		 * Σύνδεση με τη Θέση/Bin της Αποθήκης (Picking Location)
		 */
		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class, 'location_id');
		}

		public function getWarehouseId(): int {
			return $this->salesOrder?->warehouse_id ?? 0;
		}

		/**
		 * Accessor για τον Observer
		 */
		public function getQuantityAttribute() {
			return $this->quantity_ordered;
		}
	}