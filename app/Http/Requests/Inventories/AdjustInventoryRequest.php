<?php

	namespace App\Http\Requests\Inventories;

	use App\Models\Inventories\InventoryTransaction;
	use App\Models\Inventory;
	use App\Models\Product;
	use Carbon\Carbon;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Contracts\Validation\Validator;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class AdjustInventoryRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'product_id'  => [
					'required',
					'integer',
					Rule::exists('products', 'id'),
				],
				'location_id' => [
					'required',
					'integer',
					Rule::exists('inventories', 'location_id')->where(function ($query) {
						return $query->where('product_id', $this->input('product_id'));
					}),
				],
				'type'        => [
					'required',
					'string',
					Rule::in([
						'increase',
						'decrease'
					]),
				],
				'quantity'    => [
					'required',
					'integer',
					'min:1'
				],
				'reason'      => [
					'required',
					'string'
				],
				'notes'       => [
					'nullable',
					'string'
				],
			];
		}

		/**
		 * Get custom attributes for validator errors (optional).
		 */
		public function attributes(): array {
			return [
				'product_id'  => 'product',
				'location_id' => 'location',
				'type'        => 'adjustment type',
			];
		}

		public function messages(): array {
			return [
				// Product ID
				'product_id.required'   => 'Please select a product to adjust.',
				'product_id.integer'    => 'Product ID must be a valid number.',
				'product_id.exists'     => 'The selected product is no longer available.',

				// Location ID
				'location_id.required'  => 'Please select a location for the adjustment.',
				'location_id.integer'   => 'Location ID must be a valid number.',
				'location_id.exists'    => 'The selected location is no longer available.',

				// Type - more user-friendly messages
				'type.required'         => 'Please specify if you want to increase or decrease stock.',
				'type.string'           => 'Adjustment type format is invalid.',
				'type.in'               => 'Adjustment type must be either "Increase" or "Decrease".',

				// Quantity - contextual messages based on type
				'quantity.required'     => 'Please enter the adjustment quantity.',
				'quantity.integer'      => 'Quantity must be a whole number (no decimals).',
				'quantity.min'          => 'Adjustment quantity must be at least 1 unit.',

				// Reason
				'reason.required'       => 'Please provide a reason for this stock adjustment.',
				'reason.string'         => 'Reason must be valid text.',

				// Notes
				'notes.string'          => 'Notes must be valid text.',

				// Business rule messages (you can add these to custom validation)
				'quantity.insufficient' => 'Insufficient stock available for this decrease.',
				'product_id.active'     => 'This product is no longer active.',
				'location_id.active'    => 'This location is no longer active.',
			];
		}

		/**
		 * Configure the validator instance.
		 * This is called AUTOMATICALLY by Laravel if you define it.
		 */
		public function withValidator(Validator $validator): void {
			$validator->after(function ($validator) {
				// This runs AFTER the basic rules are validated
				// All data is available via $this->input()

				// Example 1: Check if product is active
				$product = Product::find($this->input('product_id'));
				if ($product && !$product->is_active) {
					$validator->errors()->add('product_id', 'This product is inactive and cannot be adjusted.');
				}

				// Example 2: Business logic for decrease adjustments
				if ($this->input('type') === 'decrease') {
					$inventory = Inventory::query()
						->where('product_id', $this->input('product_id'))
						->where('location_id', $this->input('location_id'))
						->first();

					if (!$inventory) {
						$validator->errors()->add('quantity', 'No inventory record found for this product and location.');
					} elseif ($inventory->quantity < $this->input('quantity')) {
						$validator->errors()->add('quantity', "Insufficient stock. Available: ".$inventory->quantity.", Requested: ".$this->input('quantity'));
					}
				}

				// Example 3: Check for duplicate recent adjustments
				$recentAdjustment = InventoryTransaction::where('product_id', $this->input('product_id'))
					->where('location_id', $this->input('location_id'))
					->where('quantity', $this->input('quantity'))
					->where('type', $this->input('type'))
					->where('created_at', '>=', Carbon::now()->subMinutes(5));

				if ($recentAdjustment->exists()) {
					$validator->errors()->add('quantity', 'A similar adjustment was made recently (last 5 minutes). Please confirm this is intentional.');
				}

				// Example 4: Quantity limits based on reason
				if ($this->input('reason') === 'damaged' && $this->input('quantity', 0) > 100) {
					$validator->errors()->add('quantity', 'Damaged goods adjustment cannot exceed 100 units at once.');
				}
			});
		}

		/**
		 * Prepare data for validation (optional).
		 * Runs BEFORE validation.
		 */
		protected function prepareForValidation(): void {
			// Clean up input data
			$this->merge([
				'product_id'  => (int) $this->input('product'),
				'location_id' => (int) $this->input('location'),
				'quantity'    => (int) $this->input('quantity'),
				'reason'      => trim($this->input('reason')),
				'notes'       => $this->input('notes') ? trim($this->input('notes')) : null,
			]);
		}

		public function validated($key = null, $default = null) {
			return Collection::make(parent::validated());
		}
	}
