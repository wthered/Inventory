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
		 * Get the validation rules that apply to the request.
		 *
		 * Deleted for CategoryController@filter
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
//			dd($this->input('warehouse'));
			return [
				'product'   => [
					'required',
					'integer',
					Rule::exists('inventories', 'product_id')->where(function ($query) {
						$query->where('warehouse_id', $this->input('warehouse'));
					}),
				],
				'warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id'),
				],
			];
		}
	}
