<?php

	namespace App\Models\Purchases;

	use App\Models\Product;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class PurchaseOrderItem extends Model {

		protected $fillable = [
			'purchase_order_id',
			'product_id',
			'quantity_ordered',
			'quantity_received',
			'unit_cost',
			'discount_percent',
			'discount_amount',
			'tax_percent',
			'tax_amount',
			'subtotal',
			'total',
			'notes',
		];

		/**
		 * Each item belongs to a purchase order.
		 */
		public function purchaseOrder(): BelongsTo {
			return $this->belongsTo(PurchaseOrder::class);
		}

		/**
		 * Each item references a product.
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}
	}
