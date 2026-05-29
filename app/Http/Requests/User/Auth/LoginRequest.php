<?php

	namespace App\Http\Requests\User\Auth;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;

	class LoginRequest extends FormRequest {
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
				'email'    => [
					'required',
					'email',
					'exists:users,email'
				],
				'password' => [
					'required',
					'string',
				],
			];
		}

		public function messages(): array {
			return [
				'email.required'    => 'Email is required.',
				'email.email'       => 'Please enter a valid email address.',
				'email.exists'      => 'No account found with that email address.',
				'password.required' => 'Password is required.',
			];
		}

		public function validated($key = null, $default = null): Collection {
			return Collection::make($this->input());
		}
	}
