<?php

	namespace App\Models\Concerns;

	use App\Enums\Inventory\TransferStatus;
	use App\Models\StockTransfer;
	use Illuminate\Database\Eloquent\Collection;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use LaravelIdea\Helper\App\Models\_IH_StockTransfer_C;

	trait HasStockTransfers {
		public function pendingIncomingTransfers(): HasMany {
			return $this->incomingTransfers()->whereIn('status', [
				TransferStatus::PENDING->value,
				TransferStatus::IN_TRANSIT->value,
			]);
		}

		public function incomingTransfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'target_warehouse_id');
		}

		public function pendingOutgoingTransfers(): HasMany {
			return $this->outgoingTransfers()->whereIn('status', [
				TransferStatus::PENDING->value,
				TransferStatus::IN_TRANSIT->value,
			]);
		}

		public function outgoingTransfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
		}

		/**
		 * Επειδή το "transfers" δεν είναι καθαρό Relationship (έχει OR),
		 * το κρατάμε ως Helper ή Attribute.
		 */
		public function getAllTransfersAttribute(): _IH_StockTransfer_C|Collection|array {
			return StockTransfer::query()->where('source_warehouse_id', $this->id)->orWhere('target_warehouse_id', $this->id)->get();
		}
	}
