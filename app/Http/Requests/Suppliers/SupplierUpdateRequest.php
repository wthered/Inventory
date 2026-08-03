<?php

	namespace App\Http\Requests\Suppliers;


	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class SupplierUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Checks SupplierPolicy@update which uses 'supplier.update' permission
			return Auth::check() && $this->user()->can('update', $this->route('supplier'));
		}

		/**
		 * Prepare inputs for validation.
		 * Ensures boolean fields are cleanly converted from checkbox input.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'is_active' => $this->boolean('is_active'),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			// Get current supplier ID from route model binding
			$supplierId = $this->route('supplier')?->id ?? $this->route('supplier');

			return [
				'code'           => [
					'required',
					'string',
					'max:50',
					Rule::unique('suppliers', 'code')->ignore($supplierId),
				],
				'name'           => ['required', 'string', 'max:255'],
				'company_name'   => ['nullable', 'string', 'max:255'],
				'contact_person' => ['nullable', 'string', 'max:255'],
				'email'          => ['nullable', 'email', 'max:255'],
				'phone'          => ['required', 'string', 'max:50'],
				'contact_phone'  => ['nullable', 'string', 'max:50'],
				'website'        => ['nullable', 'url', 'max:255'],
				'tax_number'     => ['nullable', 'string', 'max:100'],
				'credit_limit'   => ['required', 'numeric', 'min:0'],
				'payment_terms'  => [
					'required',
					'string',
					Rule::in(['cash', 'credit_7', 'credit_15', 'credit_30', 'credit_60', 'credit_90']),
				],
				'is_active'      => ['required', 'boolean'],
				'address'        => ['nullable', 'string', 'max:1000'],
				'city'           => ['nullable', 'string', 'max:100'],
				'state'          => ['nullable', 'string', 'max:100'],
				'country'        => ['nullable', 'string', 'max:100'],
				'postal_code'    => ['nullable', 'string', 'max:20'],
				'notes'          => ['nullable', 'string', 'max:2000'],
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 *
		 * @return array<string, string>
		 */
		public function attributes(): array {
			return [
				'code'           => 'supplier code',
				'company_name'   => 'company name',
				'contact_person' => 'contact person',
				'tax_number'     => 'tax/VAT number',
				'credit_limit'   => 'credit limit',
				'payment_terms'  => 'payment terms',
				'is_active'      => 'active status',
				'postal_code'    => 'postal code',
			];
		}
	}
