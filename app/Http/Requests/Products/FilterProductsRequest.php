<?php

	namespace App\Http\Requests\Products;

	use App\Enums\Stock\ProductStockStatus;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class FilterProductsRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Hook: Executed BEFORE validation.
		 * Used for sanitizing and casting raw inputs.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'category'       => $this->filled('category') ? intval($this->input('category')) : null,
				'child_category' => $this->filled('child_category') ? intval($this->input('child_category')) : null,
				'brand'          => $this->filled('brand') ? intval($this->input('brand')) : null,
				'supplier'       => $this->filled('supplier') ? intval($this->input('supplier')) : null,
				'status'         => $this->filled('status') ? $this->input('status') : ProductStockStatus::NORMAL->value,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'category'       => [
					'nullable',
					'integer',
					'exists:categories,id',
				],
				'child_category' => [
					'nullable',
					'integer',
					Rule::exists('categories', 'id')->where('parent_id', $this->input('category')),
				],
				'brand'          => [
					'nullable',
					'integer',
					'exists:brands,id',
				],
				'supplier'       => [
					'nullable',
					'integer',
					'exists:suppliers,id',
				],
				'status'         => [
					'nullable',
					'string',
					Rule::enum(ProductStockStatus::class),
				],
			];
		}

		/**
		 * Custom error messages.
		 */
		public function messages(): array {
			return [
				// --- Category Messages ---
				'category.integer'       => 'The selected category ID must be a valid whole number.',
				'category.exists'        => 'The chosen parent category does not exist in the database.',
				'child_category.integer' => 'The subcategory ID must be a valid whole number.',
				'child_category.exists'  => 'The chosen subcategory does not exist or is not a valid child category.',

				// --- Supplier Messages ---
				'supplier.integer'       => 'The selected supplier must be a whole number.',
				'supplier.exists'        => 'The chosen supplier is not recognized.',

				// --- Status Messages ---
				'status.string'          => 'The stock status must be text.',
				'status.enum'            => 'The selected stock status is invalid.',

				// --- Brand Messages ---
				'brand.integer'          => 'The selected brand must be a whole number.',
				'brand.exists'           => 'The chosen brand does not exist.',
			];
		}

		/**
		 * Hook: Executed AFTER successful validation.
		 * Converts the status string directly into a backed Enum instance for controller usage.
		 */
		protected function passedValidation(): void {
			if (!empty($this->validated('status'))) {
				$this->merge([
					// Keep status as the scalar string backing value (e.g. 'in_stock')
					'status' => ProductStockStatus::from($this->validated('status'))->value,
				]);
			}
		}
	}
