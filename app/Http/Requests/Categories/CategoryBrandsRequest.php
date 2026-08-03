<?php

	namespace App\Http\Requests\Categories;

	use App\Models\Category;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class CategoryBrandsRequest extends FormRequest {
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
				'parent_category' => [
					'required',
					'integer',
					Rule::exists(Category::class, 'id')->whereNull('parent_id'),
				],
				'category'        => [
					'required',
					'integer',
					Rule::exists(Category::class, 'id')->where(function ($query) {
						return $query->where('id', $this->input('parent_category'));
					}),
				]
			];
		}

		protected function prepareForValidation(): void {
			$this->merge([
				'parent_category' => intval($this->input('parent_category')),
				'category'        => intval($this->route('category')->id),
			]);
		}
	}
