<?php

	namespace App\Models;

	use App\Models\Inventories\Inventory;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class WarehouseLocation extends Model {
		use HasFactory, softDeletes;

		protected $fillable = [
			'name',
			'code',
			'address_id',
			'warehouse_id',
			'zone',
			'aisle',
			'rack',
			'shelf',
			'bin',
			'description',
			'is_active',
		];

		protected $casts = [
			'is_active' => 'boolean',
		];

		/**
		 * Get all inventory records stored in this warehouse.
		 */
		public function inventories(): HasMany {
			return $this->hasMany(Inventory::class, 'location_id');
		}

		/**
		 * Get all products available in this warehouse.
		 */
		public function products(): BelongsToMany {
			return $this->belongsToMany(Product::class, 'inventories', 'location_id', 'product_id')->withPivot([
					'warehouse_id',
					'quantity',
					'reserved_quantity',
					'batch_number'
				]);
		}

		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class, 'warehouse_id')->withPivot([
					'product_id',
					'quantity'
				]);
		}
	}
