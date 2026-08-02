<?php

	namespace App\Http\Requests\Customers;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rule;
	use Illuminate\Validation\Rules\Enum;

	class CustomerUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Use the CustomerPolicy 'update' ability
			return $this->user()?->can('update', $this->route('customer')) ?? false;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'code'             => [
					'required',
					'string',
					'max:255',
					Rule::unique('customers', 'code')->ignore($this->route('customer')),
				],
				'name'             => ['required', 'string', 'max:255'],
				'company_name'     => ['nullable', 'string', 'max:255'],
				'email'            => ['nullable', 'email', 'max:255'],
				'phone'            => ['required', 'string', 'max:255'],
				'tax_number'       => ['nullable', 'string', 'max:255'],
				'customer_type'    => ['required', new Enum(CustomerType::class)],
				'is_active'        => ['required', 'boolean'],
				'credit_limit'     => ['nullable', 'numeric', 'min:0'],
				'payment_terms'    => ['required', new Enum(PaymentTerms::class)],
				'billing_address'  => ['nullable', 'string'],
				'shipping_address' => ['nullable', 'string'],
				'city'             => ['nullable', 'string', 'max:255'],
				'state'            => ['nullable', 'string', 'max:255'],
				'country'          => ['nullable', 'string', 'max:255'],
				'postal_code'      => ['nullable', 'string', 'max:255'],
				'notes'            => ['nullable', 'string'],
			];
		}
	}
