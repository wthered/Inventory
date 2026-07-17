<?php

	namespace App\Models;

	use App\Contracts\StockMovementHeader;
	use App\Enums\Inventory\StockReturnStatus;
	use App\Enums\Inventory\TransactionReason;
	use App\Traits\HasStockMovement;
	use App\Traits\LogsActivity;
	use App\Traits\Stock\HandlesInventoryRestocking;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\Relations\MorphMany;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Throwable;

	/**
	 * @property int                $id
	 * @property int                $product_id
	 * @property int|null           $purchase_order_item_id
	 * @property int|null           $customer_id
	 * @property int                $warehouse_id
	 * @property int|null           $location_id
	 * @property int|null           $received_by
	 * @property int                $created_by
	 * @property string             $return_number
	 * @property int                $quantity
	 * @property float|null         $unit_cost
	 * @property float|null         $total_cost
	 * @property string|null        $return_reason
	 * @property string             $status
	 * @property string|null        $batch_number
	 * @property string|null        $rma_number
	 * @property string|null        $reference_number
	 * @property string|null        $quality_status
	 * @property string|null        $quality_notes
	 * @property bool               $is_restockable
	 * @property float|null         $restock_percentage
	 * @property string|Carbon      $return_date
	 * @property string|Carbon|null $processed_at
	 * @property string|Carbon|null $restocked_at
	 * @property string|Carbon|null $disposed_at
	 * @property string|null        $notes
	 * @property string|null        $customer_notes
	 * @property string|null        $inspection_notes
	 * @property string|null        $attachments           // JSON/array or comma-separated
	 * @property bool               $is_warranty_return
	 * @property bool               $is_refund_issued
	 * @property bool               $is_exchange
	 * @property bool               $requires_inspection
	 * @property bool               $requires_disposal
	 * @property string|null        $tracking_number
	 * @property string|null        $carrier
	 *
	 * @property Carbon             $created_at
	 * @property Carbon             $updated_at
	 */
	class StockReturn extends Model implements StockMovementHeader {
		use SoftDeletes, LogsActivity, HasStockMovement, HasFactory;
		use HandlesInventoryRestocking;

		protected $table = 'stock_returns';

		protected $fillable = [
			'return_number',
			'rma_number',
			'returnable_id',   // Polymorphic (Customer/Supplier)
			'returnable_type', // Polymorphic
			'warehouse_id',
			'status',
			'return_date',
			'received_by',
			'created_by',
			'tracking_number',
			'carrier',
			'notes',
			'customer_notes',
			'is_refund_issued',
			'is_exchange',
		];

		protected $casts = [
			'return_date'      => 'datetime',
			'is_refund_issued' => 'boolean',
			'is_exchange'      => 'boolean',
			'status'           => StockReturnStatus::class,
		];

		/*
		|--------------------------------------------------------------------------
		| Relationships
		|--------------------------------------------------------------------------
		*/

		/**
		 * Generate a unique Return Number
		 * Format: RET-YYYY-MM-DD-XXXX
		 */
		public static function generateReturnNumber(): string {
			$date = Carbon::now(config('app.timezone'))->format('Y-m-d');
			$number = "RET-".$date."-".Str::upper(Str::random(4));

			// Ensure uniqueness
			if (static::where('return_number', $number)->exists()) {
				return self::generateReturnNumber();
			}

			return $number;
		}

		public static function generateRmaNumber(StockReturn $return): string {

			// Προσδιορισμός τύπου: C για Customer, S για Supplier
			$typeLetter = ($return->returnable_type === Supplier::class) ? 'S' : 'C';

			$datePart = Carbon::now(config('app.timezone'))->format('Ymd');

			// Παίρνουμε το επόμενο ID (ή χρησιμοποιούμε ένα random αν το μοντέλο δεν έχει σωθεί ακόμα)
			$nextId = self::max('id') + 1;
			$idPart = str_pad($nextId, 4, '0', STR_PAD_LEFT);

			// 2 τυχαίοι χαρακτήρες για ασφάλεια/αισθητική
			$randomPart = Str::upper(Str::substr(md5(uniqid()), 0, 4));

			return "RMA-".$typeLetter."-".$datePart."-".$idPart."-".$randomPart;
		}

		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		public function location(): BelongsTo {
			return $this->belongsTo(WarehouseLocation::class);
		}

		public function customer(): BelongsTo {
			return $this->belongsTo(Customer::class);
		}

		public function creator(): BelongsTo {
			return $this->belongsTo(User::class, 'created_by');
		}

		/*
		|--------------------------------------------------------------------------
		| Logic Methods
		|--------------------------------------------------------------------------
		*/

		/**
		 * Professional Restocking Logic
		 * Updated to match our new 'in/out' InventoryTransaction schema.
		 *
		 * @throws Throwable
		 */
		public function restockInventory(): bool {
			if (!$this->is_restockable || $this->restockable_quantity <= 0 || $this->restocked_at) {
				return false;
			}

			return DB::transaction(function () {
				dd($this->inventoryTransactions()->get());
				$this->inventoryTransactions()->create([
					'product_id'   => $this->product_id,
					'warehouse_id' => $this->warehouse_id,
					'location_id'  => $this->location_id,
					'type'         => 'in',
					'reason'       => 'return',
					'quantity'     => $this->restockable_quantity,
					'notes'        => "Restock from Return #".$this->return_number,
					'created_by'   => Auth::id(),
				]);
				return $this->update([
					'restocked_at' => Carbon::now()
					                        ->toDateTimeString(),
					'status'       => 'completed'
				]);
			});
		}

		// Accessors and status helpers...

		/**
		 * Polymorphic relationship for ledger tracking.
		 */
		public function inventoryTransactions(): MorphMany {
			return $this->morphMany(InventoryTransaction::class, 'reference');
		}

		public function getIsProcessedAttribute(): bool {
			return $this->status === 'completed';
		}

		public function items(): HasMany {
			return $this->hasMany(StockReturnItem::class);
		}

		public function returnable(): MorphTo {
			return $this->morphTo();
		}

		// Το Interface τώρα δουλεύει δυναμικά για Customer/Supplier
		public function getSourceWarehouseId(): ?int {
			return ($this->returnable_type === 'App\Models\Supplier') ? $this->warehouse_id : null;
		}

		public function getTargetWarehouseId(): ?int {
			return ($this->returnable_type === 'App\Models\Customer') ? $this->warehouse_id : null;
		}

		public function getMovementReason(): string {
			// Εδώ αποφασίζεις αν θα στείλεις το 11 (CUSTOMER_RETURN_IN) ή κάποιο άλλο ID
			return TransactionReason::RETURNED->value;
		}

		public function getReferenceModel(): Model {
			return $this;
		}
	}