<?php

	namespace App\Http\Requests\Products;

	use App\DataTransferObjects\ProductDTO;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class ProductInventoryRequest extends FormRequest {
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
				'product'           => [
					'required',
					'exists:products,id'
				],
				'warehouse'         => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id')
				],
				'inventory'         => [
					'required',
					'integer',
					Rule::exists('inventories', 'id')
						->where(function ($query) {
							$query
								->where('product_id', $this->input('product'))
								->where('warehouse_id', $this->input('warehouse'));
						}),
				],
				'include_locations' => [
					'sometimes',
					'boolean'
				],
				'include_pricing'   => [
					'sometimes',
					'boolean'
				],
				'request_source'    => [
					'sometimes',
					'string'
				],
			];
		}

		public function messages(): array {
			return [];
		}

		public function validated($key = null, $default = null) {
			return Collection::make([
				'product'           => new ProductDTO($this->input('product')),
				'warehouse'         => $this->input('warehouse'),
				'inventory'         => $this->input('inventory'),
				'include_locations' => $this->input('include_locations'),
				'include_pricing'   => $this->input('include_pricing'),
				'request_source'    => $this->input('request_source'),
			]);
		}
	}
