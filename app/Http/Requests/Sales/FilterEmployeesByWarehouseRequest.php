<?php

	namespace App\Http\Requests\Sales;

	use App\Models\Sales\SalesOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class FilterEmployeesByWarehouseRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('viewAny', SalesOrder::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
			];
		}
	}
