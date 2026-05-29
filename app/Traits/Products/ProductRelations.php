<?php

	namespace App\Traits\Products;

	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Inventories\Inventory;
	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Products\ProductHistory;
	use App\Models\Products\ProductImage;
	use App\Models\Purchases\PurchaseOrderItem;
	use App\Models\Supplier;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	trait ProductRelations {
		/**
		 * Get all inventory records across all warehouses for this product.
		 */
		public function inventories(): HasMany {
			return $this->hasMany(Inventory::class);
		}

		/**
		 * Get all warehouses where this product is currently stored.
		 */
		public function warehouses(): BelongsToMany {
			return $this->belongsToMany(Warehouse::class, 'inventories')
				->withPivot(['quantity', 'reserved_quantity', 'available_quantity', 'location_id', 'batch_number']);
		}

		/**
		 * The category this product belongs to.
		 */
		public function category(): BelongsTo {
			return $this->belongsTo(Category::class);
		}

		/**
		 * The brand/manufacturer associated with the product.
		 */
		public function brand(): BelongsTo {
			return $this->belongsTo(Brand::class);
		}

		/**
		 * Purchase order items that include this product.
		 */
		public function orderItems(): HasMany {
			return $this->hasMany(PurchaseOrderItem::class);
		}

		/**
		 * Suppliers that provide this product with pricing and lead times.
		 */
		public function suppliers(): BelongsToMany {
			return $this->belongsToMany(Supplier::class, 'suppliers_products')
				->withPivot(['price', 'lead_time_days', 'is_preferred', 'moq']);
		}

		/**
		 * Gallery of images for the product.
		 */
		public function images(): HasMany {
			return $this->hasMany(ProductImage::class);
		}

		/**
		 * Audit trail of changes made to the product attributes.
		 */
		public function history(): HasMany {
			return $this->hasMany(ProductHistory::class);
		}

		/**
		 * History of stock movements (in/out/transfer).
		 */
		public function transactions(): HasMany {
			return $this->hasMany(InventoryTransaction::class)->with(['warehouse', 'creator']);
		}

		/**
		 * Specific aisle/bin locations where the product is placed.
		 */
		public function locations(): BelongsToMany {
			return $this->belongsToMany(WarehouseLocation::class, 'inventories', 'product_id', 'location_id')
				->withPivot(['warehouse_id']);
		}
	}
