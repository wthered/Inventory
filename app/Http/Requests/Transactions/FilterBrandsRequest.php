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
			// Category deleted because part of the URL
			return [
				'parent_category' => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->whereNull('parent_id'),
				],
				'product'         => [
					'required',
					'integer',
					Rule::exists('inventories', 'product_id')->where('warehouse_id', $this->input('warehouse')),
				],
				'warehouse'       => [
					'required',
					'integer',
					'exists:warehouses,id',
				]
			];
		}

		public function prepareForValidation(): void {
			$this->merge([
				'category' => intval($this->route('category')->id),
			]);
		}
	}
