<?php

	namespace App\Http\Requests\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class FilterRelevantProductsRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', PurchaseOrder::class);
		}

		/**
		 * Prepare inputs prior to running validation rules.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'category'  => $this->has('category') && !empty($this->input('category')) ? $this->input('category') : null,
				'brand'     => $this->has('brand') && !empty($this->input('brand')) ? $this->input('brand') : null,
				'warehouse' => $this->has('warehouse') && !empty($this->input('warehouse')) ? $this->input('warehouse') : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'category'  => ['required', 'integer', Rule::exists('categories', 'id')],
				'brand'     => ['required', 'integer', Rule::exists('brands', 'id')],
				'warehouse' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
			];
		}

		/**
		 * Handle actions after successful validation.
		 */
		protected function passedValidation(): void {
			// Example transformation or default assignment post-validation if needed
			if (!$this->has('warehouse')) {
				$this->merge([
					'warehouse' => null,
				]);
			}
		}
	}
