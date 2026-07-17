<?php

	namespace App\Models;

	use App\Contracts\StockMovementHeader;
	use App\Enums\Inventory\TransferStatus;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class StockTransfer extends Model implements StockMovementHeader {
		use SoftDeletes;

		protected $fillable = [
			'transfer_number',
			'source_warehouse_id',
			'target_warehouse_id',
			'status_id',
			'transfer_date',
			'expected_delivery_date',
			'approved_at',
			'received_at',
			'created_by',
			'approved_by',
			'received_by',
			'notes',
		];

		// This is the magic part!
		protected $casts = [
			'status_id'     => TransferStatus::class,
			'approved_at'   => 'datetime',
			'received_at'   => 'datetime',
			'transfer_date' => 'date:Y-m-d',
		];

		public static function generateTransferNumber(): string {
			return 'TRF-' . date('Y-m-d') . '-' . fake()->regexify('[A-Z]\d{2}[A-Z]{2}\d[A-Z]');
		}

		/**
		 * Get the products/items associated with this transfer.
		 */
		public function items(): HasMany {
			return $this->hasMany(StockTransferItem::class);
		}

		public function sourceWarehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
		}

		public function targetWarehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
		}

		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		public function getSourceWarehouseId(): ?int { return $this->source_warehouse_id; }

		public function getTargetWarehouseId(): ?int { return $this->target_warehouse_id; }

		public function getMovementReason(): string { return 'TRANSFER'; }

		public function getReferenceModel(): Model { return $this; }
	}