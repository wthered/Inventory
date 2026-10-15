<?php

	namespace App\Http\Requests\Filters\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class FilterProductInventoryRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('view', PurchaseOrder::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse' => ['required', 'integer', Rule::exists('warehouses', 'id')],
			];
		}
	}
