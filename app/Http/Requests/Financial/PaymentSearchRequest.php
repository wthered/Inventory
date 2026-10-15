<?php

	namespace App\Http\Requests\Financial;

	use App\Models\Finance\Payment;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class PaymentSearchRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && Auth::user()->can([
					'update',
					'create'
				], Payment::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'is_incoming' => [
					'required',
					'boolean'
				],
				'party_name'  => [
					'required',
					'string',
					'max:255'
				],
			];
		}
	}