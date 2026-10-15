@extends('templates.general')

@section('title', 'Payment Details #' . $payment->payment_number)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/payments/show.css') }}">
@endsection

@section('content')
	<div class="page-container">
		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fa-solid fa-credit-card icon-accent"></i>
					Payment #{{ $payment->payment_number }}
				</h1>
				<div class="subtitle">
					Recorded on {{ optional($payment->paid_at)->format('M d, Y') ?? 'N/A' }}
				</div>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('inventory.payments.index') }}" class="btn-secondary">
					<i class="fa-solid fa-arrow-left"></i> Back to Payments
				</a>
				<a href="{{ route('inventory.payments.edit', $payment->id) }}" class="btn-primary">
					<i class="fa-solid fa-pen-to-square"></i> Edit Payment
				</a>
				<button type="button" class="btn-secondary" onclick="window.print();">
					<i class="fa-solid fa-print"></i> Print
				</button>
			</div>
		</div>

		<!-- Alert / System Feedback -->
		@if(session('success'))
			<div class="alert alert-success">
				<i class="fa-solid fa-circle-check"></i>
				<span>{{ session('success') }}</span>
			</div>
		@endif

		<!-- Main Payment Summary Cards -->
		<div class="payment-details-grid">
			<!-- Overview Card -->
			<div class="table-card payment-info-card">
				<div class="card-header">
					<h3><i class="fa-solid fa-circle-info"></i> Basic Information</h3>
					<span class="badge-status badge-status-{{ strtolower($payment->status->value ?? $payment->status) }}">
						{{ ucfirst($payment->status->label() ?? $payment->status) }}
					</span>
				</div>

				<div class="details-list">
					<div class="detail-item">
						<span class="detail-label">Payment Number:</span>
						<span class="detail-value td-id">#{{ $payment->payment_number }}</span>
					</div>
					<div class="detail-item">
						<span class="detail-label">Financial Direction:</span>
						<span class="detail-value">
							{{ $payment->is_incoming ? 'Inflow (Customer Payment)' : 'Outflow (Supplier Payout)' }}
						</span>
					</div>
					<div class="detail-item">
						<span class="detail-label">Payment Date:</span>
						<span class="detail-value">
							{{ optional($payment->paid_at)->format('Y-m-d H:i') ?? 'N/A' }}
						</span>
					</div>
					<div class="detail-item">
						<span class="detail-label">Payment Method:</span>
						<span class="detail-value td-name">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
					</div>
					<div class="detail-item">
						<span class="detail-label">Amount Paid:</span>
						<span class="detail-value text-accent amount-highlight">
							€{{ number_format($payment->amount, 2) }} {{ $payment->currency }}
						</span>
					</div>
					<div class="detail-item">
						<span class="detail-label">Processed By:</span>
						<span class="detail-value">
							{{ $payment->creator->account->full_name ?? 'System' }}
						</span>
					</div>
				</div>
			</div>

			<!-- Associated Party / Invoice Card -->
			<div class="table-card payment-info-card">
				<div class="card-header">
					<h3><i class="fa-solid fa-file-invoice-dollar"></i> Related Transaction Details</h3>
				</div>

				<div class="details-list">
					@if($payment->invoice)
						<div class="detail-item">
							<span class="detail-label">Invoice Reference:</span>
							<span class="detail-value">
								<a href="{{ route('inventory.invoices.show', $payment->invoice->id) }}" class="badge-count">
									#{{ $payment->invoice->invoice_number }}
								</a>
							</span>
						</div>
					@endif

					@if($payment->customer)
						<div class="detail-item">
							<span class="detail-label">Customer:</span>
							<span class="detail-value td-name">
								<a href="{{ route('inventory.customers.show', $payment->customer_id) }}" class="link-primary">
									{{ $payment->customer->name }}
								</a>
							</span>
						</div>
					@elseif($payment->supplier)
						<div class="detail-item">
							<span class="detail-label">Supplier:</span>
							<span class="detail-value td-name">
								<a href="{{ route('inventory.suppliers.show', $payment->supplier_id) }}" class="link-primary">
									{{ $payment->supplier->name }}
								</a>
							</span>
						</div>
					@endif

					@if($payment->payable)
						<div class="detail-item">
					        <span class="detail-label">
					            {{ $payment->is_incoming ? 'Sales Order:' : 'Purchase Order:' }}
					        </span>
							<span class="detail-value">
                                @if($payment->is_incoming)
									<a href="{{ route('inventory.sales.show', $payment->payable_id) }}" class="badge-count">
					                    <i class="fa-solid fa-cart-shopping"></i> #{{ $payment->payable->order_number ?? $payment->payable_id }}
					                </a>
								@else
									<a href="{{ route('inventory.purchases.show', $payment->payable_id) }}" class="badge-count">
					                    <i class="fa-solid fa-truck-ramp-box"></i> #{{ $payment->payable->order_number ?? $payment->payable_id }}
					                </a>
								@endif
							</span>
						</div>
					@endif

					<div class="detail-item">
						<span class="detail-label">Transaction Reference:</span>
						<span class="detail-value">{{ $payment->transaction_reference ?? 'N/A' }}</span>
					</div>

					<div class="detail-item">
						<span class="detail-label">Notes / Remarks:</span>
						<span class="detail-value td-description">
							{{ $payment->notes ?? 'No notes provided for this payment.' }}
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection