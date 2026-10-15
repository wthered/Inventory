@extends('templates.general')

@section('title', 'Payments - Inventory System')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/payments/index.css') }}">
@endsection

@section('content')
	<div class="page-container">
		<!-- Page Header -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fa-solid fa-credit-card icon-accent"></i>
					Payments
				</h1>
				<p class="subtitle">Monitor incoming and outgoing transactions, payment methods, and invoice
					balances.</p>
			</div>
			<div>
				<a href="{{ route('inventory.payments.create') }}" class="btn-primary">
					<i class="fa-solid fa-plus"></i> Record Payment
				</a>
			</div>
		</div>

		<!-- System Feedback Alerts -->
		@if(session('success'))
			<div class="alert alert-success">
				<i class="fa-solid fa-circle-check"></i>
				<span>{{ session('success') }}</span>
			</div>
		@endif

		@if(session('error'))
			<div class="alert alert-danger">
				<i class="fa-solid fa-circle-exclamation"></i>
				<span>{{ session('error') }}</span>
			</div>
		@endif

		<!-- Search & Filter Toolbar -->
		<!-- Search & Filter Toolbar -->
		<div class="payments-toolbar">
			<form action="{{ route('inventory.payments.index') }}" method="GET" class="toolbar-form" id="payments-filter-form">
				<!-- Text Search -->
				<div class="search-box">
					<i class="fa-solid fa-magnifying-glass search-icon"></i>
					<input type="text"
					       name="search"
					       class="form-control filter-input js-payment-filter"
					       placeholder="Search transaction # or invoice..."
					       value="{{ request('search') }}">
				</div>

				<div class="filter-group">
					<!-- 1. Payment Type Dropdown -->
					<select name="type" class="form-select filter-select js-payment-filter">
						<option value="">All Types</option>
						@foreach($types::cases() as $type)
							<option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>
								{{ $type->label() }}
							</option>
						@endforeach
					</select>

					<!-- 2. Payment Method Dropdown -->
					<select name="method" class="form-select filter-select js-payment-filter">
						<option value="">All Methods</option>
						@foreach($methods::cases() as $method)
							<option value="{{ $method->value }}" {{ request('method') === $method->value ? 'selected' : '' }}>
								{{ $method->label() }}
							</option>
						@endforeach
					</select>

					<!-- 3. Transaction / Payment Status Dropdown -->
					<select name="status" class="form-select filter-select js-payment-filter">
						<option value="">All Transactions</option>
						@foreach($transactions::cases() as $status)
							<option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
								{{ $status->label() }}
							</option>
						@endforeach
					</select>

					<!-- Reset Filter Button -->
					@if(request()->hasAny(['search', 'type', 'method', 'status']))
						<a href="{{ route('inventory.payments.index') }}" class="btn-secondary btn-reset">
							<i class="fa-solid fa-xmark"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Payments Data Table -->
		<div class="table-card">
			<div class="table-wrapper">
				@include('partials.payments.table', ['payments' => $payments])
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/payments/filter.js') }}"></script>
@endsection