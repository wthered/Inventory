<?php

	namespace App\Http\Requests\Invoices;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;

	class StoreInvoiceRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return true;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'invoice_number'     => 'required|unique:invoices',
				'customer_id'        => 'required|exists:customers,id',
				'invoice_date'       => 'required|date',
				'due_date'           => 'nullable|date|after_or_equal:invoice_date',
				'items.*.product_id' => 'required|exists:products,id',
				'items.*.quantity'   => 'required|integer|min:1',
				'items.*.unit_price' => 'required|numeric|min:0',
			];
		}

		public function messages(): array {
			return [];
		}

		public function validated($key = null, $default = null) {
			return Collection::make(parent::validated());
		}
	}
