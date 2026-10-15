<?php

	namespace App\Http\Requests\Financial;

	use App\Models\Finance\Payment;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Sales\SalesOrder;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class PaymentStoreRequest extends FormRequest {
		public function authorize(): bool {
			return Auth::check() && Auth::user()->can('create', Payment::class);
		}

		protected function prepareForValidation(): void {
			$this->merge([
				'customer_id' => $this->input('customer_id'),
				'supplier_id' => $this->input('supplier_id'),
				'payable_id'  => $this->input('payable_id'),
			]);
		}

		public function rules(): array {
			$orderTable = $this->boolean('is_incoming') ? 'sales_orders' : 'purchase_orders';

			return [
				'is_incoming'           => [
					'required',
					'boolean'
				],
				'customer_id'           => [
					'nullable',
					'required_if:is_incoming,1',
					'exists:customers,id'
				],
				'supplier_id'           => [
					'nullable',
					'required_if:is_incoming,0',
					'exists:suppliers,id'
				],
				'payable_id'            => [
					'nullable',
					'integer',
					Rule::exists($orderTable, 'id'),
				],
				'amount'                => [
					'required',
					'numeric',
					'min:0.01'
				],
				'currency'              => [
					'required',
					'string',
					'max:3'
				],
				'payment_method'        => [
					'required',
					'string'
				],
				'status'                => [
					'required',
					'string'
				],
				'paid_at'               => [
					'required',
					'date'
				],
				'transaction_reference' => [
					'nullable',
					'string',
					'max:255'
				],
				'notes'                 => [
					'nullable',
					'string'
				],
			];
		}

		/**
		 * Handle post-validation operations and payload mutations.
		 */
		protected function passedValidation(): void {
			$this->merge([
				'payable_type' => $this->boolean('is_incoming')
					? SalesOrder::class
					: PurchaseOrder::class,
			]);
		}
	}