<?php

	namespace App\Http\Requests\Stocks\StockReturns;

	use App\Enums\Inventory\StockReturnStatus;
	use App\Models\StockReturn;
	use Carbon\Carbon;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rules\Enum;

	class StockReturnStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('store', StockReturn::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'rma_number'      => [
					'required',
					'string',
					'regex:/^RMA-[A-Z]-\d{8}-[A-Z0-9]+$/',
					'unique:stock_returns,rma_number'
				],
				'return_date'     => [
					'required',
					'date_format:Y-m-d'
				],
				'status'          => [
					'required',
					new Enum(StockReturnStatus::class)
				],
				'carrier'         => [
					'nullable',
					'string',
					'max:100'
				],
				'tracking_number' => [
					'nullable',
					'string',
					'regex:/^[A-Z0-9\-]+$/',
					'max:100'
				],
			];
		}

		protected function prepareForValidation(): void {
			$this->merge([
				'rma_number'      => $this->input('rma_number') ? strtoupper(trim($this->input('rma_number'))) : null,
				'tracking_number' => $this->input('tracking_number') ? strtoupper(trim($this->input('tracking_number'))) : null,
				'carrier'         => $this->input('carrier') ? trim($this->input('carrier')) : null,
				'status'          => $this->input('status') ?? StockReturnStatus::PENDING,
			]);
		}


		protected function passedValidation(): void {
			$this->replace(array_merge($this->validated(), [
				'return_date' => Carbon::createFromFormat('Y-m-d', $this->validated('return_date'))->startOfDay(),
			]));
		}

		public function messages(): array {
			return [
				'rma_number.required'     => 'Ο κωδικός RMA είναι υποχρεωτικός.',
				'rma_number.regex'        => 'Η μορφή του RMA δεν είναι έγκυρη (πρέπει να είναι της μορφής RMA-S-YYYYMMDD-XXXXXX).',
				'rma_number.unique'       => 'Αυτός ο κωδικός RMA χρησιμοποιείται ήδη.',
				'return_date.required'    => 'Η ημερομηνία επιστροφής είναι υποχρεωτική.',
				'return_date.date_format' => 'Η ημερομηνία πρέπει να έχει τη μορφή ΕΤΟΣ-ΜΗΝΑΣ-ΗΜΕΡΑ (ΥΥΥΥ-ΜΜ-DD).',
				'status.required'         => 'Η κατάσταση της επιστροφής είναι υποχρεωτική.',
				'status.enum'             => 'Η επιλεγμένη κατάσταση δεν είναι έγκυρη.',
				'carrier.max'             => 'Το όνομα της μεταφορικής δεν μπορεί να ξεπερνάει τους 100 χαρακτήρες.',
				'tracking_number.regex'   => 'Ο αριθμός αποστολής (Tracking) μπορεί να περιέχει μόνο λατινικούς χαρακτήρες, νούμερα και παύλες.',
				'tracking_number.max'     => 'Ο αριθμός αποστολής δεν μπορεί να ξεπερνάει τους 100 χαρακτήρες.',
			];
		}
	}
