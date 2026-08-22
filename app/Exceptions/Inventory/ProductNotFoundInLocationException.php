<?php

	namespace App\Exceptions\Inventory;

	use App\DataTransferObjects\ProductDTO;
	use App\Models\Product;
	use App\Models\WarehouseLocation;
	use Exception;
	use Throwable;

	class ProductNotFoundInLocationException extends Exception {
		/**
		 * Create a new exception instance.
		 */
		public function __construct(
			public readonly ProductDTO $product,
			public readonly WarehouseLocation|int|string|null $location = null,
			string $message = "",
			int $code = 404,
			?Throwable $previous = null
		) {
			$productId = $product instanceof Product ? $product->id : $product;
			$locationId = $location instanceof WarehouseLocation ? $location->id : $location;

			if (empty($message)) {
				$message = sprintf(
					"Product [ID: %s] was not found in warehouse location [ID: %s].",
					$productId,
					$locationId ?? 'N/A'
				);
			}

			parent::__construct($message, $code, $previous);
		}

		/**
		 * Render the exception as an HTTP response (Optional for web/API compatibility).
		 */
		public function render($request) {
			if ($request->expectsJson()) {
				return response()->json([
					'error'   => 'ProductNotFoundInLocation',
					'message' => $this->getMessage(),
				], $this->getCode());
			}

			return back()->with('error', $this->getMessage());
		}
	}