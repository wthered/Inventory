<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;

	class StockAdjustmentValidationRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
//			return Auth::check() && $this->user()->can('stock.adjust');
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				// Βασικό payload δεδομένων εισαγωγής
				'inputData'                   => ['required', 'array'],

				// Έλεγχος ότι τα IDs υπάρχουν ενεργά στους αντίστοιχους πίνακες
				'inputData.product'           => ['required', 'integer', 'exists:products,id'],
				'inputData.warehouse'         => ['required', 'integer', 'exists:warehouses,id'],
				'inputData.location'          => ['required', 'integer', 'exists:warehouse_locations,id'],
				'inputData.inventory'         => ['required', 'integer', 'exists:inventories,id'],

				// Τρέχουσες και μέγιστες μετρικές του Slot
				'inputData.currentQuantity'   => ['required', 'integer', 'min:0'],
				'inputData.maximumQuantity'   => ['required', 'integer', 'min:1'],

				// Η νέα ποσότητα προς ρύθμιση / προσαρμογή
				'quantity'                    => [
					'required',
					'integer',
					'min:0',
					// Δυναμικός κανόνας: Η νέα ποσότητα δεν πρέπει να ξεπερνάει το maximumQuantity του request
					'max:' . $this->input('inputData.maximumQuantity', 999999)
				],
			];
		}

		/**
		 * Custom error messages για καθαρά ERP alerts στο frontend.
		 */
		public function messages(): array {
			return [
				'quantity.max' => 'The target adjustment volume exceeds the maximum structural slot capacity restriction.',
				'inputData.location.exists' => 'The specified operational warehouse location code is invalid.',
				'inputData.inventory.exists' => 'The targeting active inventory ledger map record does not exist.',
			];
		}

		/**
		 * Hook: Εκτελείται ΠΡΙΝ το validation
		 */
		protected function prepareForValidation(): void {
			if ($this->has('quantity')) {
				// Διασφάλιση sanity check πριν ελεγχθεί από τα rules
				$this->merge([
					// Αφαιρεί τυχόν κενά και μετατρέπει το quantity σε καθαρό int
					'quantity' => filter_var($this->input('quantity'), FILTER_VALIDATE_INT),
				]);
			}
		}

		protected function passedValidation(): void {
			// Αντικαθιστά όλο το request payload με ένα καθαρό, flat array
			// έτοιμο για μαζική εισαγωγή στη βάση ή πέρασμα σε Service
			$this->replace([
				'product_id'   => $this->input('inputData.product'),
				'warehouse_id' => $this->input('inputData.warehouse'),
				'location_id'  => $this->input('inputData.location'),
				'inventory_id' => $this->input('inputData.inventory'),
				'quantity'     => trim($this->input('quantity')),
			]);
		}
	}
