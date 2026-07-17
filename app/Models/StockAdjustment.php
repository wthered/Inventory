<?php

	namespace App\Models;

	use App\Contracts\StockMovementHeader;
	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\MovementStatus;
	use App\Enums\Inventory\TransactionType;
	use App\Traits\HasStockMovement;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class StockAdjustment extends Model implements StockMovementHeader {
		use SoftDeletes, HasStockMovement;

		/**
		 * The attributes that are mass assignable.
		 * These fields match the header columns in the 'stock_adjustments' table.
		 * Line item details (product_id, quantity) are stored in StockAdjustmentItem.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'adjustment_number',
			'warehouse_id',
			'type',
			'reason',
			'adjustment_date',
			'notes',
			'status',
			'created_by',
			'approved_by',
			'approved_at',
		];

		protected $casts = [
			"product"         => "integer",
			"location"        => "integer",
			"type"            => "string",
			"quantity"        => 'integer',
			"notes"           => "string",
			"warehouse_id"    => "integer",
			"created_at"      => "datetime",
			"approved_at"     => "datetime",
			"adjustment_date" => "date:Y-m-d",
			'reason'          => AdjustmentReason::class,
			"status"          => MovementStatus::class,
		];

		/*
		|--------------------------------------------------------------------------
		| Relationships
		|--------------------------------------------------------------------------
		*/

		/**
		 * Get the individual item lines associated with this adjustment document.
		 * This is crucial as the line items hold the product and quantity details.
		 */
		public function items(): HasMany {
			return $this->hasMany(StockAdjustmentItem::class);
		}

		public function getMovementType(): string {
			return 'adjustment';
		}

		/**
		 * Get the warehouse where the adjustment occurred.
		 */
		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		public function locations(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class);
		}

		/**
		 * Get the user who performed the adjustment.
		 */
		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		/**
		 * Get the user who approved the adjustment.
		 */
		public function approver(): BelongsTo {
			return $this->belongsTo(User::class, 'approved_by');
		}

		public function getSourceWarehouseId(): ?int { return $this->warehouse_id; }

		public function getTargetWarehouseId(): ?int { return null; }

		public function getMovementReason(): string { return TransactionType::ADJUSTMENT->value; }

		public function getReferenceModel(): Model { return $this; }
	}
