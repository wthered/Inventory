<?php

	namespace App\Models\Inventories;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;

	class InventoryAudit extends Model {
		protected $guarded = [];

		protected $casts = [
			'metadata' => 'array',
		];

		/**
		 * Η σχέση με το αντικείμενο που προκάλεσε το audit.
		 */
		public function auditable(): MorphTo {
			return $this->morphTo();
		}
	}
