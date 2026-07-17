<?php

	namespace App\Models\Purchases;

	use App\Models\Product;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class PurchaseOrderItem extends Model {

		protected $fillable = [
			'purchase_order_id',
			'product_id',
			'batch_number',
			'manufacturing_date',
			'expiry_date',
			'location_id',
			'quantity_ordered',
			'quantity_received',
			'unit_price',
			'discount_rate',
		];

		protected $casts = [
			'manufacturing_date'   => 'date',
			'expiry_date'          => 'date',
			'quantity_ordered'     => 'integer',
			'quantity_received'    => 'integer',
			'unit_price'           => 'decimal:2',
			'discount_rate'        => 'decimal:2',
			'total_ordered_price'  => 'decimal:2',
			'total_received_price' => 'decimal:2',
		];

		public function order(): BelongsTo {
			return $this->belongsTo(PurchaseOrder::class);
		}

		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}
	}