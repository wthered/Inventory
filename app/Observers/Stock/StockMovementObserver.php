<?php

	namespace App\Observers\Stock;

	use App\Models\StockReturn;
	use App\Services\Inventory\StockMovementService;
	use Illuminate\Database\Eloquent\Model;

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
				$model->rma_number = $model->generateRmaNumber($model);
				$model->return_number = $model->generateReturnNumber();
			}

			$model->items->notes = "Set by movement observer";
		}
	}
