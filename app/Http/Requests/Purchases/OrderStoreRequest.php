<?php

	namespace App\Http\Requests\Purchases;

	use App\DataTransferObjects\ProductDTO;
	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rules\Enum;

	class OrderStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', PurchaseOrder::class);
		}

		/**
		 * Prepare data before validation execution.
		 */
		protected function prepareForValidation(): void {
			// Default to DRAFT if status_id is omitted or invalid
			if (!$this->has('status_id') || blank($this->input('status_id'))) {
				$this->merge([
					'status_id' => PurchaseOrderStatus::DRAFT->value,
				]);
			}

			// Sanitize array items to ensure numbers and clean strings
			if ($this->has('items') && is_array($this->input('items'))) {
				$sanitizedItems = [];

				foreach ($this->input('items') as $index => $item) {
					$sanitizedItems[$index] = [
						'category_id'      => $item['category_id'] ?? null,
						'brand_id'         => $item['brand_id'] ?? null,
						'product_id'       => $item['product_id'] ?? null,
						'batch_number'     => isset($item['batch_number']) ? trim($item['batch_number']) : null,
						'expiry_date'      => $item['expiry_date'] ?? null,
						'quantity_ordered' => isset($item['quantity_ordered']) ? (float) $item['quantity_ordered'] : 1,
						'unit_price'       => isset($item['unit_price']) ? (float) $item['unit_price'] : 0.00,
						'discount_rate'    => isset($item['discount_rate']) ? (float) $item['discount_rate'] : 0.00,
					];
				}

				$this->merge(['items' => $sanitizedItems]);
			}
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'supplier_id'              => ['required', 'integer', 'exists:suppliers,id'],
				'warehouse_id'             => ['required', 'integer', 'exists:warehouses,id'],
				'order_date'               => ['required', 'date'],
				'expected_date'            => ['nullable', 'date', 'after_or_equal:order_date'],
				'status_id'                => ['required', 'integer', new Enum(PurchaseOrderStatus::class)],
				'notes'                    => ['nullable', 'string', 'max:2000'],

				// Line Items Validation
				'items'                    => ['required', 'array', 'min:1'],
				'items.*.category_id'      => ['required', 'integer', 'exists:categories,id'],
				'items.*.brand_id'         => ['required', 'integer', 'exists:brands,id'],
				'items.*.product_id'       => ['required', 'integer', 'exists:products,id'],
				'items.*.batch_number'     => ['nullable', 'string', 'max:100'],
				'items.*.expiry_date'      => ['nullable', 'date', 'after:order_date'],
				'items.*.quantity_ordered' => ['required', 'numeric', 'gt:0'],
				'items.*.unit_price'       => ['required', 'numeric', 'gte:0'],
				'items.*.discount_rate'    => ['required', 'numeric', 'between:0,100'],
			];
		}

		/**
		 * Handle post-validation transformations and aggregate calculations.
		 */
		protected function passedValidation(): void {
			// 1. Get validated data directly from the validator instance
			$validated = $this->validator->validated();

			$subtotal = 0;
			$discountAmount = 0;
			$taxAmount = 0;

			// 2. Wrap the items array into a Collection
			foreach (Collection::make($validated['items'] ?? []) as $item) {
				$lineSubtotal = $item['quantity_ordered'] * $item['unit_price'];
				$lineDiscount = $lineSubtotal * ($item['discount_rate'] / 100);

				$subtotal += $lineSubtotal;
				$discountAmount += $lineDiscount;

				// Product DTO Tax Calculation
				$product = new ProductDTO($item['product_id']);

				// If tax_percent is a percentage (e.g. 24 for 24%), convert to decimal multiplier
				$taxRate = $product->tax_percent > 1 ? ($product->tax_percent / 100) : $product->tax_percent;

				// Calculate tax per line item based on ordered quantity
				$taxAmount += ($item['quantity_ordered'] * $product->selling_price) * $taxRate;
			}

			$grandTotal = ($subtotal - $discountAmount) + $taxAmount;

			// Merge the financial aggregates into request input
			$this->merge([
				'subtotal'        => round($subtotal, 2),
				'discount_amount' => round($discountAmount, 2),
				'tax_amount'      => round($taxAmount, 2),
				'grand_total'     => round($grandTotal, 2),
				'created_by'      => Auth::id(),
			]);
		}

		/**
		 * Get custom error messages for localized display.
		 */
		public function messages(): array {
			return [
				'supplier_id.required'         => 'Παρακαλούμε επιλέξτε προμηθευτή.',
				'warehouse_id.required'        => 'Παρακαλούμε επιλέξτε αποθήκη παράδοσης.',
				'order_date.required'          => 'Η ημερομηνία παραγγελίας είναι υποχρεωτική.',
				'expected_date.after_or_equal' => 'Η αναμενόμενη ημερομηνία παράδοσης πρέπει να είναι μεταγενέστερη ή ίση της ημερομηνίας παραγγελίας.',
				'status_id.required'           => 'Παρακαλούμε επιλέξτε αρχική κατάσταση παραγγελίας.',

				'items.required' => 'Πρέπει να προσθέσετε τουλάχιστον ένα προϊόν στην παραγγελία.',
				'items.min'      => 'Πρέπει να προσθέσετε τουλάχιστον ένα προϊόν στην παραγγελία.',

				'items.*.category_id.required'  => 'Η κατηγορία είναι υποχρεωτική.',
				'items.*.brand_id.required'     => 'Το brand είναι υποχρεωτικό.',
				'items.*.product_id.required'   => 'Το προϊόν είναι υποχρεωτικό.',
				'items.*.expiry_date.after'     => 'Η ημερομηνία λήξης πρέπει να είναι μεταγενέστερη της ημερομηνίας παραγγελίας.',
				'items.*.quantity_ordered.gt'   => 'Η ποσότητα πρέπει να είναι μεγαλύτερη του 0.',
				'items.*.unit_price.gte'        => 'Η τιμή μονάδας δεν μπορεί να είναι αρνητική.',
				'items.*.discount_rate.between' => 'Το ποσοστό έκπτωσης πρέπει να είναι από 0 έως 100%.',
			];
		}

		/**
		 * Custom field attributes for generic error message rendering.
		 */
		public function attributes(): array {
			return [
				'supplier_id'   => 'Προμηθευτής',
				'warehouse_id'  => 'Αποθήκη',
				'order_date'    => 'Ημερομηνία Παραγγελίας',
				'expected_date' => 'Αναμενόμενη Ημερομηνία Παράδοσης',
				'status_id'     => 'Αρχική Κατάσταση',
			];
		}
	}
