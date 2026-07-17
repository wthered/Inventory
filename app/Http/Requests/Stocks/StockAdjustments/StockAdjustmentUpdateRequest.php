<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Models\StockAdjustment;
	use Exception;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rule;

	class StockAdjustmentUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Επιστρέφουμε true για να επιτραπεί η εκτέλεση του αιτήματος,
			// ή ενσωματώνετε τον δικό σας έλεγχο δικαιωμάτων (π.χ. $this->user()->can('update', ...))
			return $this
				->user()
				->can('update', StockAdjustment::class);
		}

		/**
		 * Get the validation rules that apply to the request using Array Syntax.
		 * @throws Exception
		 */
		public function rules(): array {
			return [
				// Κανόνες για τα Γενικά Στοιχεία (Header)
				'warehouse_id'        => [
					'required',
					'integer',
					'exists:warehouses,id'
				],
				'adjustment_date'     => [
					'required',
					'date'
				],
				'notes'               => [
					'nullable',
					'string',
					'max:1000'
				],

				// Κανόνες για τις δυναμικές γραμμές (Items array)
				'items'               => [
					'required',
					'array',
					'min:1'
				],
				'items.*.product_id'  => [
					'required',
					'integer',
					'exists:products,id'
				],
				'items.*.location_id' => [
					'required',
					'integer',
					'exists:warehouse_locations,id'
				],
				'items.*.reason'      => [
					'required',
					'string',
					Rule::enum(AdjustmentReason::class),
				],
				'items.*.type'        => [
					'required',
					'string',
					Rule::enum(AdjustmentType::class),
				],
				'items.*.quantity'    => [
					'required',
					'integer',
					'min:1'
				],
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 */
		public function attributes(): array {
			return [
				'warehouse_id'        => 'Αποθήκη',
				'adjustment_date'     => 'Ημερομηνία Προσαρμογής',
				'notes'               => 'Σημειώσεις',
				'items.*.product_id'  => 'Προϊόν',
				'items.*.location_id' => 'Θέση',
				'items.*.reason'      => 'Αιτιολογία',
				'items.*.type'        => 'Τύπος Κίνησης',
				'items.*.quantity'    => 'Ποσότητα',
			];
		}

		/**
		 * Προετοιμασία δεδομένων ΠΡΙΝ το validation (Before Validation).
		 * Μετατρέπει όλες τις ποσότητες σε απόλυτες (θετικές) τιμές ώστε να περάσει με ασφάλεια το rule 'min:1'.
		 */
		protected function prepareForValidation(): void {
			if ($this->has('items')) {
				$items = $this->input('items');

				foreach ($items as $key => $item) {
					if (isset($item['quantity'])) {
						// Εξασφαλίζουμε ότι η ποσότητα είναι θετικός ακέραιος
						$items[$key]['quantity'] = abs((int) $item['quantity']);
					}
				}

				// Ενημερώνουμε το Request με τις καθαρισμένες τιμές
				$this->merge(['items' => $items]);
			}
		}

		/**
		 * Διαχείριση δεδομένων ΜΕΤΑ από επιτυχές validation (Passed Validation).
		 * Μετατρέπει αυτόματα την ποσότητα σε αρνητική αν ο τύπος κίνησης είναι μείωση αποθέματος.
		 */
		protected function passedValidation(): void {
			$items = $this->validated()['items'] ?? [];

			foreach ($items as $key => $item) {
				// Αν το type δηλώνει έξοδο ή μείωση, βάζουμε αρνητικό πρόσημο για τη βάση δεδομένων
				if ($item['type'] ===  AdjustmentType::DECREASE->value) {
					$items[$key]['quantity'] = -abs($item['quantity']);
				} else {
					$items[$key]['quantity'] = abs($item['quantity']);
				}
			}

			// Κάνουμε override το items array στο request με τις τελικές τιμές προς αποθήκευση
			$this->merge(['items' => $items]);
		}

		/**
		 * Get the error messages for the defined validation rules.
		 */
		public function messages(): array {
			return [
				// Γενικά Στοιχεία (Header)
				'warehouse_id.required'        => 'Η επιλογή της αποθήκης είναι υποχρεωτική.',
				'warehouse_id.integer'         => 'Η αποθήκη πρέπει να είναι έγκυρος ακέραιος αριθμός.',
				'warehouse_id.exists'          => 'Η επιλεγμένη αποθήκη δεν είναι έγκυρη.',

				'adjustment_date.required'     => 'Η ημερομηνία προσαρμογής είναι υποχρεωτική.',
				'adjustment_date.date'         => 'Η ημερομηνία δεν έχει έγκυρη μορφή.',

				'notes.string'                 => 'Οι σημειώσεις πρέπει να είναι κείμενο.',
				'notes.max'                    => 'Οι σημειώσεις δεν μπορούν να υπερβαίνουν τους :max χαρακτήρες.',

				// Δυναμικές γραμμές (Items array)
				'items.required'               => 'Πρέπει να προσθέσετε τουλάχιστον μία γραμμή προϊόντος.',
				'items.array'                  => 'Τα προϊόντα πρέπει να είναι σε μορφή πίνακα.',
				'items.min'                    => 'Απαιτείται τουλάχιστον :min γραμμή προϊόντος.',

				'items.*.product_id.required'  => 'Το προϊόν είναι υποχρεωτικό σε όλες τις γραμμές.',
				'items.*.product_id.integer'   => 'Το αναγνωριστικό του προϊόντος πρέπει να είναι ακέραιος αριθμός.',
				'items.*.product_id.exists'    => 'Το επιλεγμένο προϊόν δεν υπάρχει στο σύστημα.',

				'items.*.location_id.required' => 'Η θέση αποθήκευσης είναι υποχρεωτική σε όλες τις γραμμές.',
				'items.*.location_id.integer'  => 'Η θέση πρέπει να είναι έγκυρος ακέραιος αριθμός.',
				'items.*.location_id.exists'   => 'Η επιλεγμένη θέση δεν είναι έγκυρη.',

				'items.*.reason.required' => 'Η αιτιολογία είναι υποχρεωτική σε όλες τις γραμμές.',
				'items.*.reason.string'   => 'Η αιτιολογία πρέπει να είναι κείμενο.',
				'items.*.reason.enum'     => 'Η επιλεγμένη αιτιολογία ":value" δεν είναι έγκυρη.',

				'items.*.type.required' => 'Ο τύπος κίνησης είναι υποχρεωτικός σε όλες τις γραμμές.',
				'items.*.type.string'   => 'Ο τύπος κίνησης πρέπει να είναι κείμενο.',
				'items.*.type.enum'     => 'Ο επιλεγμένος τύπος κίνησης ":value" δεν είναι έγκυρος.',

				'items.*.quantity.required'    => 'Η ποσότητα είναι υποχρεωτική σε όλες τις γραμμές.',
				'items.*.quantity.integer'     => 'Η ποσότητα πρέπει να είναι ακέραιος αριθμός.',
				'items.*.quantity.min'         => 'Η ποσότητα πρέπει να είναι τουλάχιστον :min.',
			];
		}
	}
