<?php

	namespace App\Http\Requests\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class OrderUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('update', PurchaseOrder::class);
		}

		/**
		 * Hook: Prepare input data BEFORE validation runs.
		 */
		protected function prepareForValidation(): void {
			if ($this->has('items') && is_array($this->items)) {
				$cleanedItems = [];

				foreach ($this->items as $index => $item) {
					// 1. Strip currency symbols or commas if they sneaked in from the UI
					$unitPrice = isset($item['unit_price'])
						? str_replace(['$', ','], '', $item['unit_price'])
						: 0.00;

					// 2. Set sensible defaults for null/empty values so validation passes smoothly
					$cleanedItems[$index] = array_merge($item, [
						'quantity_ordered' => (int) ($item['quantity_ordered'] ?? 1),
						'unit_price'       => (float) $unitPrice,
						'discount_rate'    => filled($item['discount_rate'] ?? null) ? (float) $item['discount_rate'] : 0.00,
						'batch_number'     => filled($item['batch_number'] ?? null) ? trim($item['batch_number']) : null,
						'expiry_date'      => filled($item['expiry_date'] ?? null) ? $item['expiry_date'] : null,
					]);
				}

				// Merge cleaned items back into the request payload
				$this->merge(['items' => $cleanedItems]);
			}
		}

		/**
		 * Get the validation rules that apply to the request.
		 */
		/**
		 * Get the validation rules that apply to the request.
		 */
		public function rules(): array {
			$rules = [
				// Main Order Info
				'supplier_id'              => ['required', 'integer', 'exists:suppliers,id'],
				'warehouse_id'             => ['required', 'integer', 'exists:warehouses,id'],
				'order_date'               => ['required', 'date'],
				'expected_date'            => ['nullable', 'date', 'after_or_equal:order_date'],
				'notes'                    => ['nullable', 'string', 'max:2000'],

				// Items Array
				'items'                    => ['required', 'array', 'min:1'],
				'items.*.category_id'      => ['required', 'integer', 'exists:categories,id'],
				'items.*.brand_id'         => ['required', 'integer', 'exists:brands,id'],
				'items.*.product_id'       => ['required', 'integer', 'exists:products,id'],

				// Batch & Expiry
				'items.*.batch_number'     => ['nullable', 'string', 'max:100'],
				'items.*.expiry_date'      => ['nullable', 'date', 'after:order_date'],

				// Quantities & Pricing
				'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
				'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
				'items.*.discount_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
			];

			// Διαβάζουμε τα items από το request για να χτίσουμε τους δυναμικούς κανόνες
			$items = $this->input('items', []);

			foreach ($items as $index => $item) {
				$categoryId = $item['category_id'] ?? null;
				$brandId = $item['brand_id'] ?? null;

				if ($categoryId && $brandId) {
					// 1. Έλεγχος αν το product ανήκει στο συγκεκριμένο category_id και brand_id
					$rules["items.{$index}.product_id"][] = Rule::exists('products', 'id')
					                                            ->where('category_id', $categoryId)
					                                            ->where('brand_id', $brandId);

					// 2. Έλεγχος αν ο συνδυασμός brand_id και category_id υπάρχει στον πίνακα brand_category
					$rules["items.{$index}.brand_id"][] = Rule::exists('brand_category', 'brand_id')
					                                          ->where('category_id', $categoryId);
				}
			}

			return $rules;
		}

		/**
		 * Hook: Modify validated data AFTER validation passes successfully.
		 */
		protected function passedValidation(): void {
			$items = $this->validated('items');

			// Compute and inject row-level totals directly into each item array
			foreach ($items as $index => $item) {
				$subtotal = $item['quantity_ordered'] * $item['unit_price'];
				$discountAmount = $subtotal * ($item['discount_rate'] / 100);

				$items[$index]['row_total'] = round($subtotal - $discountAmount, 2);

				$item['category_id'] = intval($this->validated($item['category_id']));
				$item['brand_id'] = intval($this->validated($item['brand_id']));
			}

			// Replace the request's items with the mutated array containing pre-calculated totals
			$this->merge([
				'items' => $items,
				'notes' => trim($this->validated('notes'))
			]);
		}

		/**
		 * Custom validation messages.
		 */
		public function messages(): array {
			return [
				'expected_date.after_or_equal' => 'The expected delivery date cannot be before the order date.',
				'items.required'               => 'You must add at least one line item to this order.',
				'items.min'                    => 'You must add at least one line item to this order.',

				'items.*.category_id.required'      => 'Category is required.',
				'items.*.brand_id.required'         => 'Brand is required.',
				'items.*.product_id.required'       => 'Product is required.',
				'items.*.quantity_ordered.required' => 'Qty required.',
				'items.*.quantity_ordered.min'      => 'Must be ≥ 1.',
				'items.*.unit_price.required'       => 'Price required.',
				'items.*.unit_price.min'            => 'Must be ≥ 0.',
				'items.*.discount_rate.max'         => 'Max discount is 100%.',
				'items.*.expiry_date.after'         => 'Expiry must be after the order date.',
			];
		}
	}