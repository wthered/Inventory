<?php

	namespace App\Models\Purchases;

	use App\Models\User;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Carbon;

	/**
	 * Class PurchaseOrderHistory
	 * * Used to log changes, status updates, or events related to a specific PurchaseOrder.
	 * * @property int $id
	 *
	 * @property int $purchase_order_id
	 * @property string $event
	 * @property string $description
	 * @property int $user_id
	 * @property Carbon $created_at
	 * @property Carbon $updated_at
	 */
	class PurchaseOrderHistory extends Model {
		use HasFactory, softDeletes;

		protected $fillable = [
			'purchase_order_id',
			'event',
			'description',
			'user_id',
		];

		/**
		 * Get the purchase order this history entry belongs to.
		 */
		public function purchaseOrder(): BelongsTo {
			return $this->belongsTo(PurchaseOrder::class);
		}

		/**
		 * Get the user who performed the action.
		 */
		public function user(): BelongsTo {
			return $this->belongsTo(User::class);
		}
	}
