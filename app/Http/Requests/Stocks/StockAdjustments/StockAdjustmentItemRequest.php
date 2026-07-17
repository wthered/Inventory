<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use Illuminate\Support\Facades\Auth;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;

	class StockAdjustmentItemRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
//			return Auth::check() && $this->user()->can('stock.adjust');
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'location_id' => ['required', 'integer', 'exists:warehouse_locations,id'],
			];
		}
	}
