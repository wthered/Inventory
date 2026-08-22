<?php

	namespace App\Http\Requests\Sales;

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use App\Models\Sales\SalesOrder;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rules\Enum;

	class SaleOrderUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('update', SalesOrder::class);
		}

		/**
		 * Prepare the data for validation.
		 * Sanitizes inputs before rules are evaluated.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				// Default discount_amount to 0 if empty or omitted
				'discount_amount' => $this->filled('discount_amount')
					? (float) str_replace(',', '.', $this->input('discount_amount'))
					: 0.00,

				// Default tax_amount to 0 if empty or omitted
				'tax_amount'      => $this->filled('tax_amount')
					? (float) str_replace(',', '.', $this->input('tax_amount'))
					: 0.00,

				// Trim whitespace from notes if present
				'notes'           => $this->filled('notes') ? trim($this->input('notes')) : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'warehouse_id'             => ['required', 'exists:warehouses,id'],
				'status_id'                => ['required', new Enum(SalesOrderStatus::class)],
				'payment_status_id'        => ['required', new Enum(PaymentStatus::class)],
				'order_date'               => ['required', 'date'],
				'shipping_date'            => ['nullable', 'date', 'after_or_equal:order_date'],
				'discount_amount'          => ['nullable', 'numeric', 'min:0'],
				'tax_amount'               => ['nullable', 'numeric', 'min:0'],
				'notes'                    => ['nullable', 'string', 'max:2048'],

				// Items Array Validation Rules
				'items'                    => ['required', 'array', 'min:1'],
				'items.*.product_id'       => ['required', 'integer', 'exists:products,id'],
				'items.*.quantity_ordered' => ['required', 'numeric', 'gt:0'],
				'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
				'items.*.discount_rate'    => ['nullable', 'numeric', 'between:0,100'],
			];
		}

		/**
		 * Custom error messages for validation failures.
		 *
		 * @return array<string, string>
		 */
		public function messages(): array {
			return [
				'warehouse_id.required'        => 'Παρακαλούμε επιλέξτε μια αποθήκη εξυπηρέτησης.',
				'warehouse_id.exists'          => 'Η επιλεγμένη αποθήκη δεν είναι έγκυρη.',
				'status_id.required'           => 'Η κατάσταση παραγγελίας είναι υποχρεωτική.',
				'payment_status_id.required'   => 'Η κατάσταση πληρωμής είναι υποχρεωτική.',
				'order_date.required'          => 'Συμπληρώστε την ημερομηνία παραγγελίας.',
				'order_date.date'              => 'Η ημερομηνία παραγγελίας δεν έχει έγκυρη μορφή.',
				'shipping_date.after_or_equal' => 'Η ημερομηνία αποστολής δεν μπορεί να είναι προγενέστερη της ημερομηνίας παραγγελίας.',
				'discount_amount.numeric'      => 'Το ποσό έκπτωσης πρέπει να είναι αριθμός.',
				'discount_amount.min'          => 'Το ποσό έκπτωσης δεν μπορεί να είναι αρνητικό.',
				'tax_amount.numeric'           => 'Το ποσό ΦΠΑ πρέπει να είναι αριθμός.', // Added custom error message
				'tax_amount.min'               => 'Το ποσό ΦΠΑ δεν μπορεί να είναι αρνητικό.',

				// Added custom error message
				'notes.max'                    => 'Οι σημειώσεις δεν μπορούν να υπερβαίνουν τους 2000 χαρακτήρες.',

				'items.required'                    => 'Η παραγγελία πρέπει να περιέχει τουλάχιστον ένα προϊόν.',
				'items.min'                         => 'Πρέπει να προσθέσετε τουλάχιστον ένα προϊόν στην παραγγελία.',
				'items.*.product_id.required'       => 'Το προϊόν είναι υποχρεωτικό.',
				'items.*.product_id.exists'         => 'Το επιλεγμένο προϊόν δεν υπάρχει.',
				'items.*.quantity_ordered.required' => 'Η ποσότητα είναι υποχρεωτική.',
				'items.*.quantity_ordered.gt'       => 'Η ποσότητα πρέπει να είναι μεγαλύτερη από 0.',
				'items.*.unit_price.required'       => 'Η τιμή μονάδας είναι υποχρεωτική.',
				'items.*.discount_rate.between'     => 'Το ποσοστό έκπτωσης πρέπει να είναι μεταξύ 0 και 100.',
			];
		}

		/**
		 * Get custom attributes for validator errors.
		 *
		 * @return array<string, string>
		 */
		public function attributes(): array {
			return [
				'warehouse_id'             => 'Αποθήκη',
				'status_id'                => 'Κατάσταση Παραγγελίας',
				'payment_status_id'        => 'Κατάσταση Πληρωμής',
				'order_date'               => 'Ημερομηνία Παραγγελίας',
				'shipping_date'            => 'Ημερομηνία Αποστολής',
				'discount_amount'          => 'Ποσό Έκπτωσης',
				'tax_amount'               => 'Ποσό ΦΠΑ',
				'notes'                    => 'Σημειώσεις',

				'items'                    => 'Προϊόντα Παραγγελίας',
				'items.*.product_id'       => 'Προϊόν',
				'items.*.quantity_ordered' => 'Ποσότητα',
				'items.*.unit_price'       => 'Τιμή Μονάδας',
				'items.*.discount_rate'    => 'Έκπτωση (%)',
			];
		}
	}