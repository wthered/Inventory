<?php

	namespace App\Http\Requests\Filters\Financial;

	use App\Enums\Financial\PaymentMethod;
	use App\Enums\Financial\PaymentType;
	use App\Enums\Financial\TransactionStatus;
	use App\Models\Finance\Payment;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rules\Enum;

	class FilterPaymentsRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return $this->user()?->can('viewAny', Payment::class) ?? false;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'search' => [
					'nullable',
					'string',
					'max:255',
				],
				'type'   => [
					'nullable',
					new Enum(PaymentType::class),
				],
				'method' => [
					'nullable',
					new Enum(PaymentMethod::class),
				],
				'status' => [
					'nullable',
					new Enum(TransactionStatus::class),
				],
			];
		}

		protected function prepareForValidation(): void {
			if ($this->has('query') && !$this->has('search')) {
				$this->merge([
					'search' => $this->input('query'),
				]);
			}
		}
	}