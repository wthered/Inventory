<?php

	namespace App\Http\Requests\Customers;

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use App\Rules\Customers\GreekAfm;
	use App\Rules\Customers\GreekPhoneNumber;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;
	use Illuminate\Validation\Rules\Enum;

	class CustomerUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			$customer = $this->route('customer');
			return $this->user()?->can('update', $customer) ?? false;
		}

		/**
		 * Prepare data prior to validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				// Strip whitespace/dashes from tax numbers and phone
				'tax_number' => $this->input('tax_number') ? preg_replace('/\s+/', '', $this->input('tax_number')) : null,
				'phone'      => $this->input('phone') ? preg_replace('/[\s\-.()]+/', '', $this->input('phone')) : null,

				// Cast empty string select options to null
				'country'    => $this->input('country') !== '' ? $this->input('country') : null,
				'city'       => $this->input('city') !== '' ? $this->input('city') : null,

				// Trim string inputs
				'code'       => $this->input('code') ? trim($this->input('code')) : null,
				'name'       => $this->input('name') ? trim($this->input('name')) : null,
				'email'      => $this->input('email') ? Str::lower(trim($this->input('email'))) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			$customer = $this->route('customer')?->id ?? $this->route('customer');

			return [
				'code'             => [
					'required',
					'string',
					'max:255',
					Rule::unique('customers', 'code')->ignore($customer)
				],
				'name'             => ['required', 'string', 'max:255'],
				'company_name'     => ['nullable', 'string', 'max:255'],
				'email'            => ['nullable', 'email', 'max:255'],
				'phone'            => ['required', 'string', new GreekPhoneNumber()],
				'tax_number'       => ['nullable', 'string', new GreekAfm()],
				'customer_type'    => ['required', new Enum(CustomerType::class)],
				'is_active'        => ['required', 'boolean'],
				'credit_limit'     => ['nullable', 'numeric', 'min:0'],
				'payment_terms'    => ['required', new Enum(PaymentTerms::class)],
				'billing_address'  => ['nullable', 'string'],
				'shipping_address' => ['nullable', 'string'],
				'city'             => [
					'nullable',
					'integer',
					Rule::exists('cities', 'id')->where(function ($query) {
						$query->where('country_id', $this->input('country'));
					}),
				],
				'state'            => ['nullable', 'string', 'max:255'],
				'country'          => ['nullable', 'integer', Rule::exists('countries', 'id')],
				'postal_code'      => ['nullable', 'string', 'max:255'],
				'notes'            => ['nullable', 'string'],
			];
		}

		/**
		 * Handle data processing after validation passes.
		 */
		protected function passedValidation(): void {
			if ($this->has('postal_code') && $this->validated('postal_code')) {
				$this->merge([
					'postal_code' => Str::upper($this->validated('postal_code')),
				]);
			}

			if ($this->has('code') && !empty($this->validated('code'))) {
				$this->merge([
					'code' => Str::upper($this->validated('code')),
				]);
			}
		}
	}