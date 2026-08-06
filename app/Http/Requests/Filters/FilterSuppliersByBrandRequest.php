<?php

	namespace App\Http\Requests\Filters;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class FilterSuppliersByBrandRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			// Merge brand_id from route parameter if using route parameters (e.g., /filters/brands/{brand_id}/suppliers)
			// or ensure query parameter is formatted as integer/null
			$this->merge([
				'brand_id' => $this->route('brand') ?? $this->input('brand_id'),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'brand_id' => [
					'required',
					'integer',
					'exists:brands,id',
				],
			];
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			$this->replace([
				'brand_id' => (int) $this->validated('brand_id'),
			]);
		}
	}
