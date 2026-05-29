<?php

	namespace App\Http\Requests\Warehouses;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class IndexWarehousesRequest extends FormRequest {
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
				'product_id'   => [
					'required',
					'integer',
					Rule::exists('products', 'id'),
				],
				'warehouse_id' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id'),
				],
				'location_id'  => [
					'required',
					'integer',
					Rule::exists('warehouse_locations', 'id'),
				]
			];
		}

		public function validated($key = null, $default = null): Collection {
			return Collection::make([
				'product_id'   => intval($this->input('product_id')),
				'warehouse_id' => intval($this->input('warehouse_id')),
				'location_id'  => intval($this->input('location_id')),
			]);
		}
	}
