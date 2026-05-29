<?php

	namespace App\Models\Inventories;

	use App\Models\Product;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
