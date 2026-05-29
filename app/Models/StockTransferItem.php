<?php

	namespace App\Models;

	use App\Contracts\StockMoveable;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class StockTransferItem extends Model implements StockMoveable {
		protected $fillable = [
			'stock_transfer_id',
			'product_id',
			'quantity_requested',
			'quantity_delivered',
			'quantity_received',
			'processed_by',
			'processed_at',
			'notes',
		];

		public function movement(): BelongsTo {
			return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
		}

		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		public function getWarehouseId(): int {
			return $this->movement->source_warehouse_id ?? $this->movement->target_warehouse_id;
		}
	}