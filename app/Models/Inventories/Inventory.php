<?php

	namespace App\Models\Inventories;

	use App\Models\Product;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class Inventory extends Model {

		protected $fillable = [
			'product_id',
			'warehouse_id',
			'location_id',
			'quantity',
			'reserved_quantity',
			'batch_number',
			'manufacturing_date',
			'expiry_date',
		];

		protected $casts = [
			'manufacturing_date' => 'date',
			'expiry_date'        => 'date',
		];

		/**
		 * Relationship to log transactional errors or physical audit discrepancies.
		 * Allows you to call: $inventory->movementLogs()->create([...])
		 */
		public function movementLogs(): Inventory|HasMany {
			return $this->hasMany(InventoryMovementLog::class, 'product_id', 'product_id')
				->whereColumn('warehouse_id', 'inventories.warehouse_id')
				->whereColumn('location_id', 'inventories.location_id');
		}

		/**
		 * Relationship to the physical stocktake audit items.
		 * Allows you to call: $inventory->auditItems()->create([...])
		 */
		public function auditItems(): Inventory|HasMany {
			return $this->hasMany(InventoryAuditItem::class, 'product_id', 'product_id')
				->whereColumn('location_id', 'inventories.location_id');
		}

		/**
		 * Get the product that owns this inventory record.
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		/**
		 * Get the warehouse where this inventory is stored.
		 */
		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		/**
		 * Get the warehouse location for this inventory.
		 */
		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class, 'location_id');
		}

		public function supplier(): BelongsTo {
			return $this->belongsTo(Supplier::class);
		}
	}
