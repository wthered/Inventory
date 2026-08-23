<?php

	namespace App\Http\Requests\Customers;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class CustomerIndexFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('customer.view');
		}

		/**
		 * Prepare input data prior to validation processing.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'search' => $this->filled('search') ? trim((string) $this->search) : null,
				'status' => $this->filled('status') ? strtolower(trim((string) $this->status)) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'search' => ['nullable', 'string', 'max:100'],
				'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
			];
		}

		/**
		 * Modify or strip data after successful validation.
		 */
		protected function passedValidation(): void {
			// Strip out null values so $request->validated() only contains defined filters
			$this->replace(
				array_filter($this->validated(), fn($value) => !is_null($value))
			);
		}

		/**
		 * Get custom attributes for validator errors.
		 *
		 * @return array<string, string>
		 */
		public function attributes(): array {
			return [
				'search' => 'search query',
				'status' => 'customer status filter',
			];
		}
	}
