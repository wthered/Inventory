<?php

	namespace App\Http\Requests\Categories;

	use App\Models\Category;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class CategoryUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('update', Category::class);
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'name' => $this->name ? filter_var(trim($this->name), FILTER_SANITIZE_SPECIAL_CHARS) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			$category = $this->route('category');

			return [
				'name'        => ['required', 'string', 'max:255'],
				'description' => ['nullable', 'string', 'max:1000'],
				'parent_id'   => [
					'nullable',
					'integer',
					Rule::exists('categories', 'id'),
					Rule::notIn([$category instanceof Category ? $category->id : $category]),
				],
				'brands'      => ['nullable', 'array'],
				'brands.*'    => ['integer', Rule::exists('brands', 'id')], // Validate every ID inside the array
			];
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'name.required'    => 'The category name field is mandatory.',
				'parent_id.not_in' => 'A category cannot be assigned as its own parent division.',
				'parent_id.exists' => 'The selected parent category structure does not exist.',
			];
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			$category = $this->route('category');
			$targetParentId = $this->input('parent_id');

			if ($this->has('name')) {
				$this->merge([
					'slug' => Str::slug($this->input('name')),
				]);
			}

			// Perform structural change checks cleanly using the active model instance
			if ($category instanceof Category && $category->parent_id != $targetParentId) {

				// Target standard subcategory array sequence or root node sequence
				$query = Category::query();
				if (is_null($targetParentId)) {
					$query->whereNull('parent_id');
				} else {
					$query->where('parent_id', $targetParentId);
				}

				$maxSortOrder = $query->max('sort_order');
				$nextSortOrder = is_null($maxSortOrder) ? 0 : ($maxSortOrder + 1);

				$this->merge([
					'sort_order' => $nextSortOrder,
				]);
			}
		}
	}