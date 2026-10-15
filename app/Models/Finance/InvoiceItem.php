<?php

	namespace App\Models\Finance;

	use App\Models\Product;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class InvoiceItem extends Model {
		protected $fillable = [
			'invoice_id',
			'product_id',
			'quantity',
			'unit_price',
			'total',
		];

		protected $casts = [
			'quantity'   => 'integer',
			'unit_price' => 'decimal:2',
			'total'      => 'decimal:2',
		];

		/**
		 * Parent invoice.
		 */
		public function invoice(): BelongsTo {
			return $this->belongsTo(Invoice::class);
		}

		/**
		 * Billed product.
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}
	}