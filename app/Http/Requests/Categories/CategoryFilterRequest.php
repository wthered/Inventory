<?php

	namespace App\Http\Requests\Categories;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class CategoryFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Merge route parameter into validation data.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'category_id' => intval($this->route('category')->id),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'category_id' => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->whereNull('parent_id'),
				],
			];
		}
	}
