<?php

	namespace App\Http\Requests\Financial;

	use App\Enums\Financial\PaymentMethod;
	use App\Enums\Financial\TransactionStatus;
	use App\Models\Finance\Payment;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Carbon;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class PaymentUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && Auth::user()->can(['update'], Payment::class);
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			// Map form input names 'customer' / 'supplier' to '_id' attributes if present
			$this->merge([
				'customer_id' => $this->input('customer_id', $this->input('customer')),
				'supplier_id' => $this->input('supplier_id', $this->input('supplier')),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'is_incoming'           => [
					'required',
					'boolean',
				],
				'customer_id'           => [
					'nullable',
					'required_if:is_incoming,1,true',
					'exists:customers,id',
				],
				'supplier_id'           => [
					'nullable',
					'required_if:is_incoming,0,false',
					'exists:suppliers,id',
				],
				'amount'                => [
					'required',
					'numeric',
					'min:0.01',
				],
				'currency'              => [
					'required',
					'string',
					'max:3',
					'in:EUR,USD'
				],
				'payment_method'        => [
					'required',
					'string',
					Rule::enum(PaymentMethod::class),
				],
				'status'                => [
					'required',
					'string',
					Rule::enum(TransactionStatus::class),
				],
				'paid_at'               => [
					'required',
					'date',
				],
				'transaction_reference' => [
					'nullable',
					'string',
					'max:255',
				],
				'notes'                 => [
					'nullable',
					'string',
				],
			];
		}

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			if ($this->has('paid_at')) {
				$paidAt = Carbon::parse($this->input('paid_at'));

				// If no seconds were specified in the input (e.g. HH:mm:00), append current seconds or random seconds
				if ($paidAt->second === 0) {
					$paidAt->setSecond(mt_rand(0, 59));
				}

				$this->merge([
					'paid_at' => $paidAt->toDateTimeString(),
				]);
			}
		}
	}