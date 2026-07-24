<?php

	namespace App\Http\Requests\Categories;

	use App\Models\Category;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class CategoryStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', Category::class);
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'name' => $this->input('name') ? filter_var(trim($this->input('name')), FILTER_SANITIZE_SPECIAL_CHARS) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 */
		public function rules(): array {
			return [
				'name'        => ['required', 'string', 'max:255'],
				'description' => ['nullable', 'string', 'max:1000'],
				'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')],
			];
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'name.required'    => 'The category name field is mandatory.',
				'parent_id.exists' => 'The selected parent category structure does not exist.',
			];
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			if ($this->has('name')) {
				$this->merge([
					'slug' => Str::slug($this->input('name')),
				]);
			}
		}
	}
