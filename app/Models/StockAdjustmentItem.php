<?php

	namespace App\Models;

	use App\Contracts\StockMoveable;
	use App\Models\Inventories\InventoryTransaction;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\MorphMany;

	/**
	 * @property int $id
	 * @property int $stock_adjustment_id
	 * @property int $product_id
	 * @property int|null $location_id
	 * @property string $type (increase|decrease)
	 * @property string $reason (damage|loss|found|recount|expired|other)
	 * @property int $quantity
	 * @property int $quantity_before
	 * @property int $quantity_after
	 * @property float|null $unit_cost
	 * @property string|null $notes
	 * @property Carbon|null $created_at
	 * @property Carbon|null $updated_at
	 * @property-read StockAdjustment $adjustment
	 * @property-read Product $product
	 * @property-read WarehouseLocation|null $location
	 */
	class StockAdjustmentItem extends Model implements StockMoveable {

		/**
		 * Updated fillable to match our Audit-Ready Schema.
		 */
		protected $fillable = [
			'stock_adjustment_id',
			'product_id',
			'location_id',
			'type',
			'reason',
			'quantity',
			'quantity_before',
			'quantity_after',
			'unit_cost',
			'notes',
		];

		protected $casts = [
			'stock_adjustment_id' => 'integer',
			'product_id'          => 'integer',
			'location_id'         => 'integer',
			'quantity'            => 'integer',
			'quantity_before'     => 'integer',
			'quantity_after'      => 'integer',
			'unit_cost'           => 'float',
			'notes'               => 'string',
		];

		/*
		|--------------------------------------------------------------------------
		| Relationships
		|--------------------------------------------------------------------------
		*/

		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		/**
		 * Link to the specific Warehouse Location (Bin/Shelf).
		 */
		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class, 'location_id');
		}

		public function transactions(): MorphMany {
			return $this->morphMany(InventoryTransaction::class, 'reference');
		}

		public function movement(): BelongsTo {
			return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
		}

		public function getWarehouseId(): int {
			// Εδώ επιστρέφεις το ID από τη σχέση που ήδη έχεις
			return $this->movement->warehouse_id;
		}
	}