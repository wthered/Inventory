<table class="data-table" id="payments-table">
	<thead>
	<tr>
		<th>Transaction #</th>
		<th>Invoice</th>
		<th>Payment Date</th>
		<th>Type</th>
		<th>Method</th>
		<th>Status</th>
		<th class="text-right">Amount</th>
		<th class="text-right">Actions</th>
	</tr>
	</thead>
	<tbody>
	@forelse($payments as $payment)
		<tr>
			<!-- Transaction ID -->
			<td class="td-id">
				<a href="{{ route('inventory.payments.show', $payment->id) }}" class="payment-code-link">
					#{{ $payment->transaction_number ?? sprintf('TRX-%05d', $payment->id) }}
				</a>
			</td>

			<!-- Invoice Reference -->
			<td class="td-name">
				@if($payment->invoice)
					<a href="{{ route('inventory.invoices.show', $payment->invoice_id) }}" class="invoice-link">
						#{{ $payment->invoice->invoice_number ?? sprintf('INV-%05d', $payment->invoice_id) }}
					</a>
				@else
					<span class="text-muted">N/A</span>
				@endif
			</td>

			<!-- Payment Date -->
			<td>
				{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') : '-' }}
			</td>

			<!-- Type Column (Receipt / Payment) -->
			<td>
				@if($payment->is_incoming)
					<span class="badge badge-type badge-receipt">
                        {{ \App\Enums\Financial\PaymentType::RECEIPT->label() }}
                    </span>
				@else
					<span class="badge badge-type badge-payment">
                        {{ \App\Enums\Financial\PaymentType::PAYMENT->label() }}
                    </span>
				@endif
			</td>

			<!-- Method Column (Cash / Card / Bank) -->
			<td>
				@php
					$methodEnum = $payment->payment_method instanceof \App\Enums\Financial\PaymentMethod
						? $payment->payment_method
						: \App\Enums\Financial\PaymentMethod::tryFrom($payment->payment_method);
				@endphp
				<span class="method-tag">
                    <i class="fa-solid {{ match($methodEnum?->value ?? $payment->payment_method) {
                        'credit_card', 'debit_card' => 'fa-credit-card',
                        'cash'                      => 'fa-money-bill-wave',
                        'cheque'                    => 'fa-money-check',
                        'paypal'                    => 'fa-brands fa-paypal',
                        default                     => 'fa-building-columns'
                    } }}"></i>
                    {{ $methodEnum?->label() ?? ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Bank Transfer')) }}
                </span>
			</td>

			<!-- Status Column -->
			<td>
				@php
					$statusEnum = $payment->status instanceof \App\Enums\Financial\TransactionStatus
						? $payment->status
						: \App\Enums\Financial\TransactionStatus::tryFrom($payment->status);
					$hex = $statusEnum?->hexColor() ?? '#64748b';
				@endphp
				<span class="status-badge" style="background-color: {{ $hex }}26; color: {{ $hex }}; border-color: {{ $hex }}40;">
                    {{ $statusEnum?->label() ?? ucfirst($payment->status ?? 'Pending') }}
                </span>
			</td>

			<!-- Amount Column -->
			<td class="text-right amount-cell {{ $payment->is_incoming ? 'amount-positive' : 'amount-negative' }}">
				{{ $payment->is_incoming ? '+' : '-' }}${{ number_format($payment->amount ?? 0, 2) }}
			</td>

			<!-- Actions -->
			<td class="text-right actions-cell">
				<div class="action-buttons-group">
					<a href="{{ route('inventory.payments.show', $payment->id) }}"
					   class="btn-action btn-show"
					   title="View Payment">
						<i class="fa-solid fa-eye"></i>
					</a>
					<a href="{{ route('inventory.payments.edit', $payment->id) }}"
					   class="btn-action btn-edit"
					   title="Edit Payment">
						<i class="fa-solid fa-pen-to-square"></i>
					</a>
					<form action="{{ route('inventory.payments.destroy', $payment->id) }}"
					      method="POST"
					      class="inline-form"
					      onsubmit="return confirm('Are you sure you want to delete this payment record?');">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn-action btn-delete" title="Delete Payment">
							<i class="fa-solid fa-trash-can"></i>
						</button>
					</form>
				</div>
			</td>
		</tr>
	@empty
		<tr>
			<td colspan="8" class="table-empty-state">
				<i class="fa-solid fa-credit-card empty-icon"></i>
				<p>No payment transactions found matching your criteria.</p>
				<a href="{{ route('inventory.payments.create') }}" class="btn-primary btn-sm">
					Record First Payment
				</a>
			</td>
		</tr>
	@endforelse
	</tbody>
</table>

@if(method_exists($payments, 'hasPages') && $payments->hasPages())
	<div class="table-pagination">
		{{ $payments->withQueryString()->links() }}
	</div>
@endif