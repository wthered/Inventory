@extends('templates.general')

@section('title', 'Invoices Management')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/financial/invoices/index.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
@endsection

@section('content')
	<div class="page-container">
		<!-- Header -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fa-solid fa-file-invoice-dollar icon-accent"></i>
					Invoices
				</h1>
				<div class="subtitle">Track and manage customer billing and supplier invoices</div>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('inventory.invoices.create') }}" class="btn-primary">
					<i class="fa-solid fa-plus"></i> New Invoice
				</a>
			</div>
		</div>

		<!-- Success Alert -->
		@if(session('success'))
			<div class="alert alert-success">
				<i class="fa-solid fa-circle-check"></i>
				<span>{{ session('success') }}</span>
			</div>
		@endif

		<!-- Filter Bar Card -->
		<div class="table-card filter-card">
			<form action="{{ route('inventory.invoices.index') }}" method="GET" class="filter-form">
				<div class="filter-grid">
					<!-- Search Field -->
					<div class="form-group">
						<input type="text"
						       name="search"
						       class="form-control"
						       placeholder="Search Invoice # or Notes..."
						       value="{{ request('search') }}">
					</div>

					<!-- Status Filter -->
					<div class="form-group">
						<select name="status" class="form-select">
							<option value="">-- All Statuses --</option>
							<option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid
							</option>
							<option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>
								Partially Paid
							</option>
							<option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
							<option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue
							</option>
						</select>
					</div>

					<!-- Type Filter (Incoming / Outgoing) -->
					<div class="form-group">
						<select name="type" class="form-select">
							<option value="">-- All Types --</option>
							<option value="incoming" {{ request('type') === 'incoming' ? 'selected' : '' }}>Customer
								(Receivable)
							</option>
							<option value="outgoing" {{ request('type') === 'outgoing' ? 'selected' : '' }}>Supplier
								(Payable)
							</option>
						</select>
					</div>

					<!-- Filter Buttons -->
					<div class="filter-actions">
						<button type="submit" class="btn-primary">
							<i class="fa-solid fa-filter"></i> Filter
						</button>
						<a href="{{ route('inventory.invoices.index') }}" class="btn-secondary" title="Reset Filters">
							<i class="fa-solid fa-rotate-left"></i>
						</a>
					</div>
				</div>
			</form>
		</div>

		<!-- Data Table Card -->
		<div class="table-card">
			<div class="table-responsive">
				<table class="data-table">
					<thead>
					<tr>
						<th>Invoice #</th>
						<th>Associated Party</th>
						<th>Date</th>
						<th>Due Date</th>
						<th>Total Amount</th>
						<th>Status</th>
						<th class="align-right">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($invoices as $invoice)
						<tr>
							<td class="td-id">
								<a href="{{ route('inventory.invoices.show', $invoice->id) }}" class="link-primary">
									#{{ $invoice->invoice_number }}
								</a>
							</td>
							<td class="td-name">
								@if($invoice->customer)
									<a href="{{ route('inventory.customers.show', $invoice->customer_id) }}" class="link-primary">
										<i class="fa-solid fa-user icon-subtle"></i> {{ $invoice->customer->name }}
									</a>
								@elseif($invoice->supplier)
									<a href="{{ route('inventory.suppliers.show', $invoice->supplier_id) }}" class="link-primary">
										<i class="fa-solid fa-building icon-subtle"></i> {{ $invoice->supplier->name }}
									</a>
								@else
									<span class="text-muted">N/A</span>
								@endif
							</td>
							<td>{{ optional($invoice->invoice_date)->format('Y-m-d') ?? 'N/A' }}</td>
							<td>{{ optional($invoice->due_date)->format('Y-m-d') ?? 'N/A' }}</td>
							<td class="font-weight-bold">
								€{{ number_format($invoice->total_amount, 2) }}
							</td>
							<td>
								@php
									$statusClass = match($invoice->status->value ?? $invoice->status) {
										'paid' => 'badge-status-completed',
										'partially_paid' => 'badge-status-pending',
										'unpaid' => 'badge-status-pending',
										'overdue' => 'badge-status-failed',
										default => ''
									};
								@endphp
								<span class="badge-status {{ $statusClass }}">
										{{ ucfirst(str_replace('_', ' ', $invoice->status->value ?? $invoice->status)) }}
									</span>
							</td>
							<td class="align-right">
								<div class="table-actions">
									<a href="{{ route('inventory.invoices.show', $invoice->id) }}" class="btn-icon" title="View Details">
										<i class="fa-solid fa-eye"></i>
									</a>
									<a href="{{ route('inventory.invoices.edit', $invoice->id) }}" class="btn-icon" title="Edit Invoice">
										<i class="fa-solid fa-pen-to-square"></i>
									</a>
									<form action="{{ route('inventory.invoices.destroy', $invoice->id) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn-icon text-danger" title="Delete Invoice">
											<i class="fa-solid fa-trash"></i>
										</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="empty-state">
								<i class="fa-solid fa-file-invoice icon-empty"></i>
								<p>No invoices found matching your criteria.</p>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<!-- Pagination Footer -->
			@if($invoices->hasPages())
				<div class="card-footer">
					{{ $invoices->links('pagination::simple') }}
				</div>
			@endif
		</div>
	</div>
@endsection