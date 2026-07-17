<?php

	namespace App\Http\Requests\Stocks\StockReturns;

	use App\Enums\Inventory\StockReturnStatus;
	use Carbon\Carbon;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rules\Enum;

	class StockReturnUpdateRequest extends FormRequest {
		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				// Trim whitespace and force uppercase on tracking and RMA fields
				'rma_number'      => $this->input('rma_number') ? strtoupper(trim($this->input('rma_number'))) : null,
				'tracking_number' => $this->input('tracking_number') ? strtoupper(trim($this->input('tracking_number'))) : null,
				'carrier'         => $this->input('carrier') ? trim($this->input('carrier')) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 */
		public function rules(): array {
			return [
				'rma_number'      => [
					'required',
					'string',
					'regex:/^RMA-[A-Z]-\d{8}-[A-Z0-9]+$/',
					'unique:stock_returns,rma_number,'.$this->route('return')?->id
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

		/**
		 * Handle a passed validation attempt.
		 */
		protected function passedValidation(): void {
			// Mutate verified data into cast types (Carbon and BackedEnum)
			// so $request->validated() yields ready-to-use objects in your Controller
			$this->replace(array_merge($this->validated(), [
				'return_date' => Carbon::createFromFormat('Y-m-d', $this->validated('return_date'))->startOfDay(),
			]));
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'rma_number.required' => 'Ο κωδικός RMA είναι υποχρεωτικός.',
				'rma_number.regex'    => 'Η μορφή του RMA δεν είναι έγκυρη (πρέπει να είναι της μορφής RMA-S-YYYYMMDD-XXXXXX).',
				'rma_number.unique'   => 'Αυτός ο κωδικός RMA χρησιμοποιείται ήδη.',

				'return_date.required'    => 'Η ημερομηνία επιστροφής είναι υποχρεωτική.',
				'return_date.date_format' => 'Η ημερομηνία πρέπει να έχει τη μορφή ΕΤΟΣ-ΜΗΝΑΣ-ΗΜΕΡΑ (ΥΥΥΥ-ΜΜ-DD).',

				'status.required' => 'Η κατάσταση της επιστροφής είναι υποχρεωτική.',
				'status.enum'     => 'Η επιλεγμένη κατάσταση δεν είναι έγκυρη.',

				'carrier.max' => 'Το όνομα της μεταφορικής δεν μπορεί να ξεπερνάει τους 100 χαρακτήρες.',

				'tracking_number.regex' => 'Ο αριθμός αποστολής (Tracking) μπορεί να περιέχει μόνο λατινικούς χαρακτήρες, νούμερα και παύλες.',
				'tracking_number.max'   => 'Ο αριθμός αποστολής δεν μπορεί να ξεπερνάει τους 100 χαρακτήρες.',
			];
		}
	}
