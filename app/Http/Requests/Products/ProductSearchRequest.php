<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class ProductSearchRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// return $this->user->can('search products') some time
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'q'           => [
					'nullable',
					'string',
					'max:255'
				],
				'category_id' => [
					'nullable',
					'integer',
					Rule::exists('categories', 'id')->whereNotNull('parent_id')
				],
				'brand_id'    => [
					'nullable',
					'integer',
					Rule::exists('brands', 'id')
				],
				'product'     => [
					'nullable',
					'integer',
					Rule::exists('inventories', 'product_id')->where(function ($query) {
						$query->where('warehouse_id', $this->input('warehouse'));
					}),
				],
				'warehouse'   => [
					'nullable',
					'integer',
					Rule::exists('warehouses', 'id')
				],
			];
		}
	}
