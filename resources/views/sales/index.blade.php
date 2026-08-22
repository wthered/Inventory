@extends('templates.general')

@section('title', 'Sales Orders')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/sales/index.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<!-- Header Block -->
		<div class="page-header">
			<div class="header-title">
				<h1>Sales Orders</h1>
				<p class="subtitle">Manage outbound customer orders, tracking, and fulfillment</p>
			</div>
			<div class="header-actions">
				<a href="{{ route('inventory.sales.create') }}" class="btn btn-primary">
					<i class="fas fa-plus"></i> Create Sales Order
				</a>
			</div>
		</div>

		<!-- Filter Card -->
		<div class="filter-card">
			<form method="GET" action="{{ route('inventory.sales.index') }}" class="filter-form">
				<div class="filter-group">
					<label for="search">Search Order</label>
					<input type="text" name="search" id="search" value="{{ session('sales_filters.search') }}"
					       placeholder="Order #, Customer...">
				</div>

				<div class="filter-group">
					<label for="status">Status</label>
					<select name="status" id="status">
						<option value="">All Statuses</option>
						@foreach($statuses::cases() as $status)
							<option
								value="{{ $status->value }}" {{ session('sales_filters.status') == $status->value ? 'selected' : '' }}>
								{{ $status->label() }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="filter-group">
					<label for="payment_status">Payment Status</label>
					<select name="payment_status" id="payment_status">
						<option value="">{{ __('enums.payment_status.all') }}</option>
						@foreach($payments::cases() as $payment)
							<option
								value="{{ $payment->value }}" {{ session('sales_filters.payment_status')  == $payment->value ? 'selected' : '' }}>
								{{ $payment->label() }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="filter-group">
					<label for="date">Order Date</label>
					<input type="date" name="date" id="date" value="{{ session('sales_filters.date') }}">
				</div>

				<div class="filter-actions">
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-filter"></i> Filter
					</button>
					<a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary">Clear</a>
				</div>
			</form>
		</div>

		<!-- Data Table Card -->
		<div class="table-card">
			<div class="table-responsive">
				<table class="data-table">
					<thead>
					<tr>
						<th>Order Number</th>
						<th>Customer Name</th>
						<th>Fulfilling Warehouse</th>
						<th>Order Date</th>
						<th>Status</th>
						<th>Payment Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody id="sale_list">
					@forelse($sales as $sale)
						<tr>
							<td>
								<strong>
									<a href="{{ route('inventory.sales.show', $sale->id) }}" class="order-link">
										{{ $sale->order_number ?? '#' . $sale->id }}
									</a>
								</strong>
							</td>

							{{-- Customer Name Column --}}
							<td>{{ $sale->customer->name ?? 'N/A' }}</td>

							{{-- Fixed: Displaying assigned execution facility warehouse location name --}}
							<td>{{ $sale->warehouse->name ?? 'N/A' }}</td>

							{{-- Fixed: Pointing explicitly to your casted order_date model property --}}
							<td>{{ $sale->order_date ? $sale->order_date->format('Y-m-d') : 'N/A' }}</td>

							<td>
								{{-- Fixed: Resolving label methods safely through casted status Enum instance --}}
								@if($sale->status_id)
									<span class="badge"
									      style="background-color: {{ $sale->status_id->color() }}26; color: {{ $sale->status_id->color() }};">
                                        {{ $sale->status_id->label() }}
                                    </span>
								@else
									<span class="badge text-muted">Unknown</span>
								@endif
							</td>

							<td>
								{{-- Payment Status Column --}}
								@if($sale->payment_status_id)
									<span class="badge"
									      style="background-color: {{ $sale->payment_status_id->hexColor() }}26; color: {{ $sale->payment_status_id->hexColor() }};">
                                        {{ $sale->payment_status_id->label() }}
                                    </span>
								@else
									<span class="badge text-muted">Unknown</span>
								@endif
							</td>

							<td class="text-right table-actions">
								<a href="{{ route('inventory.sales.show', $sale->id) }}" class="btn-icon"
								   title="View Details">
									<i class="fas fa-eye"></i>
								</a>

								{{-- Fixed: Evaluating custom eligibility states on casted status values safely --}}
								@if($sale->status_id && method_exists($sale->status_id, 'isEditable') && $sale->status_id->isEditable())
									<a href="{{ route('inventory.sales.edit', $sale->id) }}"
									   class="btn-icon edit-btn" title="Edit">
										<i class="fas fa-edit"></i>
									</a>
									<form action="{{ route('inventory.sales.destroy', $sale->id) }}" method="POST"
									      class="inline-form delete-form">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn-icon delete-btn" title="Cancel/Delete"
										        onclick="return confirm('Are you sure you want to cancel this sales order?')">
											<i class="fas fa-trash-alt"></i>
										</button>
									</form>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="text-center no-data">
								<i class="fas fa-shopping-bag fa-2x"></i>
								<p>No sales orders found matching your criteria.</p>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<!-- Pagination Wrapper -->
			<div id="pagination-wrapper">
				@include('partials.sales.pagination', ['sales' => $sales])
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/sales/index.js') }}"></script>
@endsection
