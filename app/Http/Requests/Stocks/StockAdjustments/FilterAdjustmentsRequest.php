<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class FilterAdjustmentsRequest extends FormRequest {
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
				'product' => ['required', 'string'],
				'reason'  => ['nullable', 'string'],
				'date'    => ['nullable', 'date', 'date_format:Y-m-d'],
			];
		}
	}
