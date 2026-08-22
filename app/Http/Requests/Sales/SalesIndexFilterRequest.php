<?php

	namespace App\Http\Requests\Sales;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rules\Enum;

	class SalesIndexFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('sales.index'); //[cite: 5]
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'search'         => ['nullable', 'string', 'max:255'],
				'status'         => ['nullable', new Enum(SalesOrderStatus::class)],
				'payment_status' => ['nullable', new Enum(PaymentStatus::class)],
				'date'           => ['nullable', 'date_format:Y-m-d'],
			];
		}

		/**
		 * Prepare inputs for validation (converts empty string values to null).
		 */
		protected function prepareForValidation(): void {
			// $this->filled('search') checks if the input is present and not an empty string.
			$this->merge([
				'search'         => $this->input('search') ?: null,
				'status'         => $this->input('status') ?: null,
				'payment_status' => $this->input('payment_status') ?: null,
				'date'           => $this->input('date') ?: null,
			]);
		}
	}