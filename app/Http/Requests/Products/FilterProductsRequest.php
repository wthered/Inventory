<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;

	class FilterProductsRequest extends FormRequest {
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
				'category' => [
					'nullable',
					'integer',
					'exists:categories,id'
				],
				'supplier' => [
					'nullable',
					'integer',
					'exists:suppliers,id'
				],
				'status'   => [
					'nullable',
					'string',
					'in:Stock,Low,Out'
				],
			];
		}

		public function messages(): array {
			return [
				// --- Category Messages ---
				'category.integer' => 'The selected category ID must be a whole number and valid category.',
				'category.exists'  => 'The chosen category does not exist in the database. Please select a valid one.',

				// --- Supplier Messages ---
				'supplier.integer' => 'The selected supplier must be a whole number and valid supplier.',
				'supplier.exists'  => 'The chosen supplier is not recognized. Please select a valid supplier.',

				// --- Status Messages ---
				'status.string'    => 'The status must be provided as text',
				'status.in'        => 'The status must be one of the following options: Stock, Low, or Out.',
			];
		}

		/**
		 * Hook: Εκτελείται ΠΡΙΝ το validation.
		 * Χρησιμοποιείται για το "Sanitization" των δεδομένων.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'category' => $this->filled('category') ? intval($this->input('category')) : null,
				'supplier' => $this->filled('supplier') ? intval($this->input('supplier')) : null,
				'status'   => $this->input('status') ?: 'Stock',
			]);
		}

		/**
		 * Hook: Εκτελείται ΜΕΤΑ την επιτυχή επαλήθευση.
		 * Εδώ μπορούμε να κάνουμε τελικές διορθώσεις στα δεδομένα.
		 */
		protected function passedValidation(): void {
			$this->merge([
				'status'   => Str::ucfirst(Str::lower($this->validated('status'))),
				'category' => intval($this->validated('category')),
				'supplier' => $this->validated('supplier'),
			]);
		}
	}
