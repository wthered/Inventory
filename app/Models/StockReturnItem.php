<?php

	namespace App\Models;

	use App\Contracts\StockMoveable;
	use App\Traits\LogsActivity;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	/**
	 * @property int $id
	 * @property int $stock_return_id
	 * @property int $product_id
	 * @property int $quantity
	 * @property string $quality_status
	 */
	class StockReturnItem extends Model implements StockMoveable {

		use SoftDeletes, LogsActivity;

		protected $fillable = [
			'stock_return_id',
			'product_id',
			'quantity',
			'unit_cost',
			'total_cost',
			'quality_status',
			'is_restockable',
			'restock_percentage',
			'requires_inspection',
			'notes'
		];

		protected $casts = [
			'quantity'            => 'integer',
			'unit_cost'           => 'decimal:2',
			'total_cost'          => 'decimal:2',
			'is_restockable'      => 'boolean',
			'requires_inspection' => 'boolean',
			'restock_percentage'  => 'decimal:2',
		];

		public function movement(): BelongsTo {
			return $this->belongsTo(StockReturn::class, 'stock_return_id');
		}

		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class, 'location_id');
		}

		public function getWarehouseId(): int {
			return $this->movement->warehouse_id;
		}
	}
