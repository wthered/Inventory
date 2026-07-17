<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use App\Enums\Inventory\MovementStatus;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Validator;
	use Illuminate\Validation\Rule;

	class StockAdjustmentApproveRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('approve-stock-adjustments');
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			// Παίρνουμε μόνο τα backed integer values των έγκυρων Enums για Adjustments
			$allowedStatuses = array_map(
				fn(MovementStatus $status) => $status->value,
				MovementStatus::forAdjustment()
			);

			return [
				'status' => [
					'required',
					// 1. Διασφαλίζει ότι η τιμή αντιστοιχεί γενικά στο Enum
					Rule::enum(MovementStatus::class),
					// 2. Περιορίζει την τιμή μόνο στις επιτρεπόμενες καταστάσεις του Adjustment (DRAFT, PENDING, APPROVED, κλπ.)
					Rule::in($allowedStatuses),
				]
			];
		}

		/**
		 * Custom error messages for validation.
		 */
		public function messages(): array {
			return [
				'status.required' => 'Η επιλογή της κατάστασης είναι υποχρεωτική.',
				'status.enum'     => 'Η επιλεγμένη κατάσταση δεν είναι έγκυρη.',
				'status.in'       => 'Η κατάσταση αυτή δεν επιτρέπεται για προσαρμογή αποθέματος.',
			];
		}

		/**
		 * Configure the validator instance and add "after" validation hooks.
		 */
		public function after(): array {
			return [
				function (Validator $validator) {
					// Παίρνουμε το τρέχον StockAdjustment model από το route binding
					$adjustment = $this->route('adjustment');

					if ($adjustment) {
						// Έλεγχος 1: Αν έχει ήδη εγκριθεί (επιπλέον επίπεδο ασφαλείας στο validation)
						if ($adjustment->approved_at) {
							$validator->errors()->add('status', 'Αυτό το παραστατικό έχει ήδη εγκριθεί και δεν μπορεί να τροποποιηθεί.');
						}

						// Έλεγχος 2: Αν προσπαθεί να το εγκρίνει/ολοκληρώσει χωρίς να έχει προσθέσει προϊόντα
						$targetStatus = (int) $this->input('status');
						$isApproving = in_array($targetStatus, [
							MovementStatus::APPROVED->value,
							MovementStatus::COMPLETED->value
						]);

						if ($isApproving && $adjustment->items()->count() === 0) {
							$validator->errors()->add(
								'status',
								'Δεν μπορείτε να εγκρίνετε ένα παραστατικό που δεν περιέχει προϊόντα.'
							);
						}
					}
				},
			];
		}
	}
