<?php

	namespace App\Models\Inventories;

	use App\Models\Product;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class InventoryAuditItem extends Model {
		protected $guarded = [];

		public function audit(): BelongsTo {
			return $this->belongsTo(InventoryAudit::class, 'inventory_audit_id');
		}

		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class);
		}
	}
