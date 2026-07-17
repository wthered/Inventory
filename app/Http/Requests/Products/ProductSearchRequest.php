<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;

	class ProductSearchRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Change from false to true to allow authenticated operations
			// todo:
			// return $this->user->can('search products') some time
			return true;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'q'           => ['nullable', 'string', 'max:255'],
				'category_id' => ['nullable', 'integer', 'exists:categories,id'],
				'brand_id'    => ['nullable', 'integer', 'exists:brands,id'],
			];
		}
	}
