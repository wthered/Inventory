<?php

	namespace App\Models\Sales;

	use App\Contracts\StockMoveable;
	use App\Models\Product;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class SalesOrderItem extends Model implements StockMoveable {
		protected $fillable = [
			'sales_order_id',
			'product_id',
			'quantity_ordered',
			'quantity_shipped',
			'unit_price',
			'discount_percent',
			'discount_amount',
			'tax_percent',
			'tax_amount',
			'subtotal',
			'total',
			'notes',
		];

		/**
		 * Επιστροφή στην παραγγελία (Header)
		 */
		public function salesOrder(): BelongsTo {
			return $this->belongsTo(SalesOrder::class);
		}

		/**
		 * Σύνδεση με το Προϊόν
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		public function getWarehouseId(): int {
			// Χρησιμοποιούμε το null-safe operator (?->) για αποφυγή crash
			// και βεβαιωνόμαστε ότι η σχέση υπάρχει.
			return $this->salesOrder?->warehouse_id ?? 0;
		}

		/**
		 * Accessor για ευκολία (προαιρετικό)
		 * Αν ο Observer ψάχνει για 'quantity', του το δίνουμε εδώ
		 */
		public function getQuantityAttribute() {
			return $this->quantity_ordered;
		}
	}