<?php

	namespace App\Http\Requests\Transactions;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class FilterBrandsRequest extends FormRequest {
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
				'category'        => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->where(function ($query) {
						return $query->where('parent_id', $this->input('parent_category'));
					})
				],
				'parent_category' => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->whereNull('parent_id'),
				]
			];
		}

		public function prepareForValidation(): void {
			$this->merge([
				'category' => intval($this->route('category')->id),
			]);
		}
	}
