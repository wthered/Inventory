<?php

	namespace App\Models;

	use App\Enums\Inventory\TransferStatus;
	use App\Enums\WarehouseType;
	use App\Models\Concerns\HasStockTransfers;
	use App\Models\Inventories\Inventory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use LaravelIdea\Helper\App\Models\_IH_StockTransfer_C;

	class Warehouse extends Model {
		use softDeletes, HasStockTransfers;

		protected $fillable = [
			'name',
			'code',
			'type',
			'address_id',
			'zones',
			'aisles',
			'racks',
			'shelves',
			'bins',
		];

		protected $casts = [
			'type' => WarehouseType::class,
		];

		/**
		 * Get all inventory records stored in this warehouse.
		 */
		public function inventories(): HasMany {
			return $this->hasMany(Inventory::class);
		}

		/**
		 * Get all products available in this warehouse.
		 */
		public function products(): BelongsToMany {
			return $this->belongsToMany(Product::class, 'inventories')->withPivot([
				'location_id',
				'quantity',
				'reserved_quantity',
				'batch_number'
			])->where('is_active', true);
		}

		/**
		 * Get all warehouse locations within this warehouse.
		 */
		public function locations(): HasMany {
			return $this->hasMany(WarehouseLocation::class, 'warehouse_id');
		}

		/**
		 * Get the manager assigned to this warehouse.
		 */
		public function manager(): BelongsTo {
			return $this->belongsTo(User::class, 'manager_id');
		}

		public function inventoryLocations(): BelongsToMany {
			return $this->belongsToMany(WarehouseLocation::class, 'inventories')->withPivot([
				'product_id',
				'location_id',
				'quantity',
				'reserved_quantity',
				'available_quantity'
			]);
		}

		/**
		 * Get all transfers related to this warehouse (both incoming and outgoing).
		 */
		public function transfers(): StockTransfer {
			return StockTransfer::where(function($query) {
				$query->where('source_warehouse_id', $this->id)->orWhere('target_warehouse_id', $this->id);
			});
		}

		/**
		 * Get all transfers related to this warehouse (both incoming and outgoing).
		 * Χρήση: $warehouse->all_transfers (ως attribute)
		 */
		public function getAllTransfersAttribute(): _IH_StockTransfer_C|array {
			return StockTransfer::query()->where('source_warehouse_id', $this->id)->orWhere('target_warehouse_id', $this->id)->get();
		}

		public function getOccupancyPercentageAttribute(): float|int {
			if ($this->capacity <= 0) return 0;
			return 100 * ($this->current_capacity / $this->capacity);
		}

		/**
		 * Get all outgoing stock transfers where this warehouse is the source.
		 */
		public function outgoingTransfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
		}

		/**
		 * Get all incoming stock transfers where this warehouse is the target.
		 */
		public function incomingTransfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'target_warehouse_id');
		}

		/**
		 * Get pending incoming transfers that need action.
		 */
		public function pendingIncomingTransfers(): HasMany {
			return $this->incomingTransfers()->whereIn('status', [
				TransferStatus::PENDING->value,
				TransferStatus::IN_TRANSIT->value,
			]);
		}

		/**
		 * Get pending outgoing transfers that need action.
		 */
		public function pendingOutgoingTransfers(): HasMany {
			return $this->outgoingTransfers()->whereIn('status', [
				TransferStatus::PENDING->value,
				TransferStatus::IN_TRANSIT->value,
			]);
		}
	}
