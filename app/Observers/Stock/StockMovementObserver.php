<?php

	namespace App\Observers\Stock;

	use App\Models\StockReturn;
	use App\Services\Inventory\StockMovementService;
	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Str;

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

		/**
		 * Generates a unique RMA (Return Merchandise Authorization) number for customer/supplier returns.
		 *
		 * RMA Number = Εξωτερικός αριθμός που δίνεται στον πελάτη/προμηθευτή για ταυτοποίηση της επιστροφής.
		 * Χρησιμοποιείται για επικοινωνία και αποστολή του προϊόντος πίσω.
		 *
		 * @param  StockReturn  $return
		 *
		 * @return string
		 */
		protected function generateRmaNumber(StockReturn $return): string {
			$date = Carbon::now(config('app.timezone'))->format('Ymd');

			// RMA-C = Customer return (επιστροφή από πελάτη)
			// RMA-S = Supplier return (επιστροφή σε προμηθευτή)
			$type = Str::contains($return->returnable_type, 'Customer') ? 'C' : 'S';

			// Παράδειγμα: RMA-C-20250611-X7K9P2
			return "RMA-" . $type . "-" . $date . "-" . Str::upper(Str::random(6));
		}

		/**
		 * Generates an internal return number for ERP system use only.
		 *
		 * Return Number = Εσωτερικός αριθμός του ERP για παρακολούθηση και logging.
		 * Δεν δίνεται στον πελάτη - χρησιμοποιείται μόνο εσωτερικά.
		 *
		 * @return string
		 */
		protected function generateReturnNumber(): string {
			// Παράδειγμα: RET-20250611-X7K9P2
			return "RET-" . Carbon::now(config('app.timezone'))->format('Ymd') . "-" . Str::upper(Str::random(6));
		}
	}