<?php

	namespace App\Http\Controllers\Financial;

	use App\Enums\Financial\PaymentMethod;
	use App\Enums\Financial\PaymentType;
	use App\Enums\Financial\TransactionStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Financial\PaymentSearchRequest;
	use App\Http\Requests\Financial\PaymentStoreRequest;
	use App\Http\Requests\Financial\PaymentUpdateRequest;
	use App\Models\Customer;
	use App\Models\Finance\Payment;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Sales\SalesOrder;
	use App\Models\Supplier;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;

	class PaymentController extends Controller {
		/**
		 * Display a listing of payments with filtering & pagination.
		 */
		public function index(Request $request) {
			$query = Payment::query()->with([
				'customer',
				'supplier',
				'creator',
				'payable'
			]);

			return view('payments.index', [
				'payments'     => $query->latest('paid_at')->paginate(15)->withQueryString(),
				'methods'      => PaymentMethod::class,
				'transactions' => TransactionStatus::class,
				'types'        => PaymentType::class,
			]);
		}

		/**
		 * Show the form for creating a new payment.
		 */
		public function create() {
			return view('payments.create', [
				'customers'    => Customer::query()->orderBy('name')->get(),
				'suppliers'    => Supplier::query()->orderBy('name')->get(),
				'methods'      => PaymentMethod::class,
				'transactions' => TransactionStatus::class,
			]);
		}

		/**
		 * Store a newly created payment in storage.
		 */
		/**
		 * Store a newly created payment in storage.
		 */
		public function store(PaymentStoreRequest $request) {
			$validated = $request->validated();

			$validated['payable_type']   = $validated['is_incoming'] ? SalesOrder::class : PurchaseOrder::class;
			$validated['payment_number'] = Payment::generatePaymentNumber($validated['is_incoming'], $validated['paid_at']);
			$validated['created_by']     = Auth::id();

//			dd($validated);

			$payment = Payment::query()->create($validated);

			return redirect()->route('inventory.payments.show', ['payment' => $payment])
			                 ->with('success', 'Payment recorded successfully.');
		}

		/**
		 * Display the specified payment.
		 */
		public function show(Payment $payment) {
			$payment->load([
				'customer',
				'supplier',
				'creator',
				'payable'
			]);

			return view('payments.show', ['payment' => $payment]);
		}

		/**
		 * Show the form for editing the specified payment.
		 */
		public function edit(Payment $payment) {
			$payment->load([
				'customer',
				'supplier',
				'creator'
			]);

			return view('payments.edit', [
				'payment'      => $payment,
				'customers'    => Customer::query()->orderBy('name')->get(),
				'suppliers'    => Supplier::query()->orderBy('name')->get(),
				'methods'      => PaymentMethod::class,
				'transactions' => TransactionStatus::class,
				'types'        => PaymentType::class,
			]);
		}

		/**
		 * Update the specified payment in storage.
		 */
		public function update(PaymentUpdateRequest $request, Payment $payment) {
			$payment->update($request->validated());

			return redirect()->route('inventory.payments.show', ['payment' => $payment])
			                 ->with('success', 'Payment details updated successfully.');
		}

		/**
		 * Remove the specified payment from storage.
		 */
		public function destroy(Payment $payment) {
			$payment->delete();

			return redirect()->route('inventory.payments.index')
			                 ->with('success', 'Payment record deleted successfully.');
		}

		/**
		 * Search matching customers or suppliers for autocomplete via POST.
		 */
		public function search(PaymentSearchRequest $request) {
			$input = $request->validated();

			$query      = $input['party_name'];
			$isIncoming = $input['is_incoming'];

			if (empty($query)) {
				return response()->json();
			}

			// Determine query based on PaymentType enum cases
			if (!empty($isIncoming)) {
				$results = Customer::query()->whereHas('orders')->where(function ($queryBuilder) use ($query) {
					$queryBuilder->where('name', 'LIKE', "%" . $query . "%")
					             ->orWhere('company_name', 'LIKE', "%" . $query . "%")
					             ->orWhere('code', 'LIKE', "%" . $query . "%")
					             ->orWhere('tax_number', 'LIKE', "%" . $query . "%");
				});
			} else {
				$results = Supplier::query()->whereHas('orders')->where(function ($queryBuilder) use ($query) {
					$queryBuilder->where('name', 'LIKE', "%" . $query . "%")
					             ->orWhere('company_name', 'LIKE', "%" . $query . "%")
					             ->orWhere('contact_person', 'LIKE', "%" . $query . "%")
					             ->orWhere('code', 'LIKE', "%" . $query . "%");
				});
			}

			$items = $results->select('id', 'name', 'company_name', 'code')->limit(10)->get()->map(function ($result) {
				return "<div class='autocomplete-item' data-id='" . $result->id . "'>" . e($result->name) . " - " . e($result->company_name) . "</div>";
			})->join('');

			return response()->json([
				'items'     => $items,
				'reference' => fake()->regexify('[A-Z]{4}GR[A-Z0-9]{2}([A-Z0-9]{3})?'),
			]);
		}
	}