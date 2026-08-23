@extends('templates.general')

@section('title', 'Customers Directory')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/customers/index.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-users icon-accent"></i> Customer Management
				</h1>
				<p class="subtitle">View, search, and manage your customer accounts</p>
			</div>

			@can('customer.create')
				<a href="{{ route('inventory.customers.create') }}" class="btn-primary">
					<i class="fas fa-plus"></i> Add Customer
				</a>
			@endcan
		</div>

		<!-- Alert Messages -->
		@if(session('success'))
			<div class="alert alert-success">
				<i class="fas fa-check-circle"></i> {{ session('success') }}
			</div>
		@endif

		@if(session('error'))
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-circle"></i> {{ session('error') }}
			</div>
		@endif

		<!-- Filters & Search Toolbar -->
		<div class="toolbar-card">
			<form action="{{ route('inventory.customers.index') }}" method="GET" class="toolbar-form" id="search-form">
				<div class="search-box">
					<i class="fas fa-search search-icon"></i>
					<input type="text"
					       id="search-input"
					       name="search"
					       value="{{ request('search') }}"
					       placeholder="Search by name, email, phone, code..."
					       aria-label="Search customers"
					       class="form-control"
					       autocomplete="off">
				</div>

				<div class="filter-group">
					<select name="status" id="status-select" class="form-control select-filter">
						<option value="">All Statuses</option>
						<option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
							Active
						</option>
						<option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
							Inactive
						</option>
					</select>

					<button type="submit" class="btn-secondary">Filter</button>

					@if(request()->hasAny(['search', 'status']))
						<a href="{{ route('inventory.customers.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Customers Data Table -->
		<div class="table-card">
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th>Code</th>
						<th>Customer / Company</th>
						<th>Contact Info</th>
						<th>City / Country</th>
						<th>Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody id="customers-table-body">
					@include('partials.customers.index_rows', ['customers' => $customers])
					</tbody>
				</table>
			</div>

			<!-- Pagination Container -->
			<div id="pagination-container">
				@if($customers->hasPages())
					<div class="table-pagination" id="pagination">
						{{ $customers->links('pagination::simple') }}
					</div>
				@endif
			</div>
		</div>

	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/customers/index.js') }}"></script>
@endsection
