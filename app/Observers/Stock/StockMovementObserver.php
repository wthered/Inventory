<?php

	namespace App\Observers\Stock;

	use App\Enums\Inventory\TransactionReason;
	use App\Models\StockReturn;
	use App\Services\Inventory\StockMovementService;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Str;
	use App\Models\StockAdjustment;
	use App\Models\StockTransfer;

	class StockMovementObserver {

		protected StockMovementService $stockService;

		public function __construct(StockMovementService $service) {
			$this->stockService = $service;
		}

		/**
		 * Τρέχει ΜΟΝΟ για το Header (π.χ. StockReturn)
		 */
		public function creating(Model $model): void {
			if ($model instanceof StockReturn) {
				$model->rma_number    = $this->generateRmaNumber($model);
				$model->return_number = $this->generateReturnNumber();
			}
		}

		protected function generateRmaNumber(StockReturn $return): string {
			$date   = Carbon::now(config('app.timezone'))->format('Ymd');
			$type = Str::contains($return->returnable_type, 'Customer') ? 'C' : 'S';
			return "RMA-".$type."-".$date."-" . Str::upper(Str::random(6));
		}

		protected function generateReturnNumber(): string {
			return "RET-" . Carbon::now(config('app.timezone'))->format('Ymd') . "-" . Str::upper(Str::random(6));
		}

		protected function resolveReason(Model $parent): int {
			return match (get_class($parent)) {
				StockAdjustment::class => TransactionReason::ADJUSTMENT->value,
				StockReturn::class => $parent->getMovementReason(),
				StockTransfer::class => TransactionReason::TRANSFER_IN->value,
				default => TransactionReason::OTHER->value,
			};
		}
	}