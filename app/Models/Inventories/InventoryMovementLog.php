<?php

	namespace App\Models\Inventories;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;

	class InventoryMovementLog extends Model {
		/**
		 * Τα πεδία που είναι διαθέσιμα για μαζική ανάθεση (Mass Assignment).
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'product_id',
			'warehouse_id',
			'location_id',
			'action',
			'status',
			'requested_quantity',
			'before_quantity',
			'error_message',
			'loggable_type',
			'loggable_id',
			'user_id',
		];

		/**
		 * Η πολυμορφική σχέση με το αντικείμενο (π.χ. StockTransferItem, SalesOrderItem)
		 * που προκάλεσε την καταγραφή αυτού του log.
		 */
		public function loggable(): MorphTo {
			return $this->morphTo();
		}
	}