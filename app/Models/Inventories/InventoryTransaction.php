<?php

	namespace App\Models\Inventories;

	use App\Enums\Inventory\TransactionReason;
	use App\Enums\Inventory\TransactionType;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\StockAdjustment;
	use App\Models\StockAdjustmentItem;
	use App\Models\StockReturnItem;
	use App\Models\User;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Support\Str;

	class InventoryTransaction extends Model {

		protected $fillable = [
			'transaction_number',
			'product_id',
			'warehouse_id',
			'location_id',
			'type',
			'reason',
			'quantity',
			'quantity_before',
			'quantity_after',
			'batch_number',
			'unit_cost',
			'total_cost',
			'reference_type',
			'reference_id',
			'created_by',
			'notes',
		];

		protected $casts = [
			'type'            => TransactionType::class,
			'reason'          => TransactionReason::class,
			'quantity'        => 'decimal:2',
			'quantity_before' => 'integer',
			'quantity_after'  => 'integer',
			'unit_cost'       => 'decimal:2',
			'total_cost'      => 'decimal:2',
			'created_at'      => 'datetime',
		];

		public static function generateTransactionNumber(string $prefix = 'TRX'): string {
			$date   = now()->format('Ymd');
			$random = Str::upper(Str::random(4));
			$number = $prefix."-".$date."-".$random;

			// Αλλάζουμε το transaction_number σε batch_number
			if (static::where('batch_number', $number)->exists()) {
				return static::generateTransactionNumber($prefix);
			}

			return $number;
		}

		public function getReferenceDisplayAttribute(): array {
			if (!$this->reference) {
				return [
					'label' => 'Manual',
					'icon'  => 'fa-fingerprint',
					'class' => 'pill-generic'
				];
			}

			return match ($this->reference_type) {
				PurchaseOrder::class => [
					'label' => $this->reference->order_number ?? 'PO-' . $this->reference->id,
					'icon'  => 'fa-shopping-cart',
					'class' => 'pill-order'
				],
				StockAdjustment::class => [
					'label' => $this->reference->adjustment_number ?? 'ADJ-' . $this->reference->id,
					'icon'  => 'fa-adjust',
					'class' => 'pill-adjustment'
				],
				StockAdjustmentItem::class => [
					'label' => 'Item-ADJ-' . $this->reference_id,
					'icon'  => 'fa-box',
					'class' => 'pill-adjustment'
				],
				StockReturnItem::class => [
					'label' => 'RET-' . $this->reference_id,
					'icon'  => 'fa-undo',
					'class' => 'pill-danger' // Θα χρειαστείς ένα κόκκινο pill στο CSS
				],
				default => [
					'label' => '#' . $this->reference_id,
					'icon'  => 'fa-file-alt',
					'class' => 'pill-generic'
				],
			};
		}

		/** Relationships **/
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class);
		}

		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		public function reference(): MorphTo {
			return $this->morphTo();
		}
	}