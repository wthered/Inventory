<?php

	namespace App\Http\Requests\Sales;

	use App\Exceptions\Inventory\InsufficientStockException;
	use App\Models\Product;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Validation\Validator;

	class SaleOrderStoreRequest extends FormRequest {
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('sales_order.create');
		}

		public function rules(): array {
			return [
				'customer_id'              => ['required', 'exists:customers,id'],
				'warehouse_id'             => ['required', 'exists:warehouses,id'],
				'order_date'               => ['required', 'date'],
				'notes'                    => ['nullable', 'string'],
				'discount_amount'          => ['nullable', 'numeric', 'min:0'],
				'tax_amount'               => ['nullable', 'numeric', 'min:0'],
				'items'                    => ['required', 'array', 'min:1'],
				'items.*.product_id'       => ['required', 'exists:products,id'],
				'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
				'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
				'items.*.discount_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
			];
		}

		/**
		 * Validate inventory stock levels before passing request to controller.
		 *
		 * @throws InsufficientStockException
		 */
		public function withValidator(Validator $validator): void {
			$validator->after(function ($validator) {
				if ($validator->errors()->any()) {
					return;
				}

				$warehouseId = $this->input('warehouse_id');
				$items = $this->input('items', []);

				foreach ($items as $item) {
					$productId = $item['product_id'] ?? null;
					$quantity = $item['quantity_ordered'] ?? 0;

					if (!$productId) {
						continue;
					}

					$availableStock = DB::table('inventories')
					                    ->where('warehouse_id', $warehouseId)
					                    ->where('product_id', $productId)
					                    ->value('quantity') ?? 0;

					if ($availableStock < $quantity) {
						$productObject = new Product($productId);
						$message = "Ανεπαρκές απόθεμα για το προϊόν ".$productObject->name.". Διαθέσιμο: ".$availableStock.", Ζητήθηκαν: ".$quantity;
//						throw new InsufficientStockException($message);
						$validator->errors()->add('items', $message);
					}
				}
			});
		}
	}