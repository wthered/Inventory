<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Models\Inventory;
	use App\Models\User;
	use App\Models\WarehouseLocation;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class StockAdjustmentStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			$user_valid = User::role('admin')
				->pluck('id')
				->contains(Auth::id());
			return Auth::check() && $user_valid;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				"product"  => [
					'required',
					'integer',
					Rule::exists('products', 'id')
				],
				"location" => [
					'required',
					'integer',
					Rule::exists('warehouse_locations', 'id')
				],
				"type"     => [
					"required",
					Rule::in(AdjustmentType::cases()),
				],
				"quantity" => [
					'required',
					'numeric',
					'min:0.01',
					function ($attribute, $value, $fail) {
//						if ($this->input('type') == TransactionType::OUT->value) {
						// Ελέγχουμε μόνο αν η κίνηση είναι τύπου OUT (μείωση)
						if ($this->input('type') == AdjustmentType::DECREASE->value) {
							$currentStock = Inventory::where([
								'product_id'   => $this->product_id,
								'warehouse_id' => $this->warehouse_id,
								'location_id'  => $this->location_id,
							])
								                ->value('quantity') ?? 0;

							if ($value > $currentStock) {
								$fail("Ανεπαρκές απόθεμα. Διαθέσιμο: " . $currentStock . ", Ζητήθηκε: " . $value);
							}
						}
					}
				],

				// Το "reason" στο ERP συνήθως ταυτίζεται με το type.
				// Αν είναι ξεχωριστό πεδίο κειμένου, το βάζουμε string:
				"reason"   => [
					'required',
					'string',
					function ($attribute, $value, $fail) {
						// Χρησιμοποιούμε $this->type για να πάρουμε την τιμή από το request
						$type   = AdjustmentType::tryFrom($this->input('type'));
						$reason = AdjustmentReason::tryFrom($value);

						if (!$type || !$reason) {
							return $fail('Invalid transaction type (' . $this->input('type') . ') or reason: ' . $value);
						}

						// Έλεγχος συμβατότητας
						$reasonIsValid = $type->value === 'increase' ? $reason->isIncreaseReason() : ($type->value === 'decrease' && $reason->isDecreaseReason());
						if (!$reasonIsValid) {
							// Χρησιμοποιούμε το label() για πιο φιλικό μήνυμα στον χρήστη
							$fail(__('inventory.invalid_combination', [
								'reason'       => __('inventory.reasons.'.$reason->value),
								'type'         => __('inventory.types.'.$type->name),
							]));
						}
						return true;
					}
				],

				// Χρησιμοποιούμε τη μέθοδο requiresNotes() του Enum σου
				"notes"    => [
					Rule::requiredIf(function () {
						// 1. Παίρνουμε την τιμή 'type' από το request
						$typeValue = $this->input('type');

						// 2. Προσπαθούμε να φτιάξουμε το Enum instance
						$reason = AdjustmentReason::tryFrom($typeValue);

						// 3. Αν υπάρχει το instance, καλούμε τη μέθοδο, αλλιώς false
						return !empty($reason) && $reason->requiresNotes();
					}),
					'nullable',
					'string',
					'max:1000'
				]
			];
		}

		/**
		 * Rename attributes for cleaner error messages.
		 */
		public function attributes(): array {
			return [
				'product'  => __('validation.attributes.product'),
				'location' => __('validation.attributes.location'),
				'quantity' => __('validation.attributes.quantity'),
				'type'     => __('validation.attributes.type'),
				'notes'    => __('validation.attributes.notes'),
			];
		}

		/**
		 * Εξατομικευμένα μηνύματα σφάλματος.
		 */
		public function messages(): array {
			return [
				'product.required' => 'Πρέπει να επιλέξετε ένα προϊόν.',
				'product.exists'   => 'Το προϊόν που επιλέξατε δεν είναι έγκυρο.',

				'location.required' => 'Η θέση αποθήκης είναι υποχρεωτική.',

				'type.required' => 'Πρέπει να ορίσετε την αιτιολογία (Type).',
				'type.enum'     => 'Η αιτιολογία που δώσατε ( :input ) δεν αντιστοιχεί στις επιτρεπόμενες κατηγορίες.',

				'quantity.min' => 'Η ποσότητα πρέπει να είναι τουλάχιστον 0.01.',

				'notes.required_if' => 'Οι σημειώσεις είναι υποχρεωτικές για τον συγκεκριμένο τύπο προσαρμογής.',
			];
		}

		/**
		 * Επεξεργασία των έγκυρων δεδομένων πριν τη χρήση τους στον Controller.
		 */
		public function validated($key = null, $default = null): array {
			// Βρίσκουμε την αποθήκη στην οποία ανήκει το location
			$location = WarehouseLocation::find($this->input('location'));

			return array_merge(parent::validated(), [
				'warehouse_id' => $location?->warehouse_id,
				'created_by'   => Auth::id() ?? User::role('admin')->pluck('id')->random(),
			]);
		}

		/**
		 * Προετοιμασία δεδομένων πριν το validation.
		 */
		protected function prepareForValidation(): void {
			$location = WarehouseLocation::find($this->input('location'));
			$this->merge([
				'product'  => intval($this->input('product')),
				'location' => $location->id,
			]);

			if ($this->has('quantity')) {
				$this->merge([
					'quantity' => intval($this->input('quantity')),
				]);
			}
		}
	}
