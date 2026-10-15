<?php

	namespace App\Http\Requests\Products;

	use App\Models\Purchases\PurchaseOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class ProductDetailsRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && Auth::user()->canAny([
					'create',
					'update'
				], PurchaseOrder::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse' => [
					'required',
					'integer',
					Rule::exists('warehouses', 'id')
				]
			];
		}
	}
