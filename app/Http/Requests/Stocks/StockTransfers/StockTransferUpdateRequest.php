<?php

	namespace App\Http\Requests\Stocks\StockTransfers;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;

	class StockTransferUpdateRequest extends FormRequest {

		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return $this->user()->can('stock_transfer.update');
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'notes' => [
					'nullable',
					'string',
					'max:500'
				],
				'items' => [
					'required',
					'array'
				],
				'items.*.id' => [
					'required',
					'exists:stock_transfer_items,id'
				],
				'items.*.quantity' => [
					'required',
					'integer',
					'min:1'
				],
			];
		}

		/**
		 * Custom μηνύματα σφαλμάτων στα Ελληνικά για το UI.
		 */
		public function messages(): array {
			return [
				'items.required'           => 'Η λίστα των ειδών είναι υποχρεωτική.',
				'items.*.quantity.required' => 'Η ποσότητα είναι υποχρεωτική.',
				'items.*.quantity.min'      => 'Η ποσότητα πρέπει να είναι τουλάχιστον 1.',
				'items.*.quantity.integer'  => 'Η ποσότητα πρέπει να είναι έγκυρος ακέραιος αριθμός.',
				'notes.max'                 => 'Οι σημειώσεις δεν μπορούν να υπερβαίνουν τους 500 χαρακτήρες.',
			];
		}
	}
