<?php

	namespace App\Models;

	use App\Enums\Inventory\AlertStatus;
	use App\Enums\Inventory\AlertType;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class StockAlert extends Model {
		protected $fillable = [
			'product_id',
			'warehouse_id',
			'alert_type',
			'current_quantity',
			'threshold_quantity',
			'expiry_date',
			'message',
			'status',
			'resolved_by',
			'resolved_at'
		];

		/**
		 * Casting για ημερομηνίες και αριθμούς
		 */
		protected $casts = [
			'expiry_date'        => 'date',
			'resolved_at'        => 'datetime',
			'current_quantity'   => 'integer',
			'threshold_quantity' => 'integer',
			'alert_type'         => AlertType::class,
			'status'             => AlertStatus::class,
		];

		/**
		 * Το Προϊόν που αφορά η ειδοποίηση
		 */
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class);
		}

		/**
		 * Η Αποθήκη στην οποία εντοπίστηκε το ζήτημα
		 */
		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		/**
		 * Ο Χρήστης που έλυσε/διαχειρίστηκε την ειδοποίηση
		 */
		public function resolver(): BelongsTo {
			return $this->belongsTo(User::class, 'resolved_by');
		}
	}