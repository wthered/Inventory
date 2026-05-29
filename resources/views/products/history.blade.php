@extends('templates.general')

@section('title', 'Product History - ' . $product->name)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/products/history.css') }}"/>
@endsection

@section('content')
	<div class="history-container">
		<!-- Page Header -->
		<div class="history-header">
			<div class="breadcrumb">
				<a href="{{ route('inventory.products.index') }}"><i class="fas fa-arrow-left"></i> All Products</a>
				<span class="separator">/</span>
				<a href="{{ route('inventory.products.show', $product->id) }}">{{ $product->name }}</a>
				<span class="separator">/</span>
				<span class="current">History</span>
			</div>

			<div class="header-actions">
				<button class="btn btn-secondary" id="exportHistory">
					<i class="fas fa-download"></i> Export
				</button>
				<button class="btn btn-primary" id="filterToggle">
					<i class="fas fa-filter"></i> Filters
				</button>
			</div>
		</div>

		<!-- Product Summary -->
		<div class="product-summary">
			<div class="product-info">
				<div class="product-image">
					<img src="{{ $product->images->first()->image_location ?? '/images/placeholder-product.png' }}"
						 alt="{{ $product->name }}">
				</div>
				<div class="product-details">
					<h1>{{ $product->name }}</h1>
					<div class="product-meta">
                        <span class="meta-item">
                            <strong>SKU:</strong> {{ $product->sku }}
                        </span>
						<span class="meta-item">
                            <strong>Barcode:</strong> {{ $product->barcode }}
                        </span>
						<span class="meta-item">
                            <strong>Category:</strong> {{ $product->category->name }}
                        </span>
					</div>
				</div>
			</div>

			<div class="stock-summary">
				<div class="stock-item">
					<span class="label">Current Stock</span>
					<span class="value">{{ number_format($currentStock, 0) }}</span>
				</div>
				<div class="stock-item">
					<span class="label">Total Movements</span>
					<span class="value">{{ number_format($totalMovements, 0) }}</span>
				</div>
				<div class="stock-item">
					<span class="label">Period</span>
					<span class="value" id="periodDisplay">All Time</span>
				</div>
			</div>
		</div>

		<!-- Filters Section -->
		<div class="filters-section" id="filtersSection">
			<div class="filters-grid">
				<div class="filter-group">
					<label for="dateRange">Date Range</label>
					<select id="dateRange" class="form-control">
						<option value="all">All Time</option>
						<option value="today">Today</option>
						<option value="yesterday">Yesterday</option>
						<option value="week">This Week</option>
						<option value="month">This Month</option>
						<option value="quarter">This Quarter</option>
						<option value="year">This Year</option>
						<option value="custom">Custom Range</option>
					</select>
				</div>

				<div class="filter-group" id="customDateRange" style="display: none;">
					<label for="startDate">From</label>
					<input type="date" id="startDate" class="form-control">
					<label for="endDate">To</label>
					<input type="date" id="endDate" class="form-control">
				</div>

				<div class="filter-group">
					<label for="transactionType">Transaction Type</label>
					<select id="transactionType" class="form-control">
						<option value="all">All Types</option>
						<option value="in">Stock In</option>
						<option value="out">Stock Out</option>
						<option value="transfer">Transfer</option>
						<option value="adjustment">Adjustment</option>
					</select>
				</div>

				<div class="filter-group">
					<label for="warehouseFilter">Warehouse</label>
					<select id="warehouseFilter" class="form-control">
						<option value="all">All Warehouses</option>
						@foreach($warehouses as $warehouse)
							<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="filter-group">
					<label for="userFilter">User</label>
					<select id="userFilter" class="form-control">
						<option value="all">All Users</option>
						@foreach($users as $user)
							<option value="{{ $user->id }}">{{ $user->name }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="filter-actions">
				<button class="btn btn-secondary" id="clearFilters">
					<i class="fas fa-times"></i> Clear
				</button>
				<button class="btn btn-primary" id="applyFilters">
					<i class="fas fa-check"></i> Apply
				</button>
			</div>
		</div>

		<!-- Statistics Cards -->
		<div class="stats-cards">
			<div class="stat-card">
				<div class="stat-icon in">
					<i class="fas fa-arrow-down"></i>
				</div>
				<div class="stat-content">
					<span class="stat-value" id="totalIn">0</span>
					<span class="stat-label">Total In</span>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon out">
					<i class="fas fa-arrow-up"></i>
				</div>
				<div class="stat-content">
					<span class="stat-value" id="totalOut">0</span>
					<span class="stat-label">Total Out</span>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon transfer">
					<i class="fas fa-exchange-alt"></i>
				</div>
				<div class="stat-content">
					<span class="stat-value" id="totalTransfer">0</span>
					<span class="stat-label">Transfers</span>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon net">
					<i class="fas fa-balance-scale"></i>
				</div>
				<div class="stat-content">
					<span class="stat-value" id="netChange">0</span>
					<span class="stat-label">Net Change</span>
				</div>
			</div>
		</div>

		<!-- History Table -->
		<div class="history-section">
			<div class="section-header">
				<h2>
					<i class="fas fa-history"></i>
					Transaction History
				</h2>
				<div class="table-actions">
					<div class="search-box">
						<i class="fas fa-search"></i>
						<input type="text" id="searchHistory" placeholder="Search transactions...">
					</div>
					<span class="results-count" id="resultsCount">Showing all transactions</span>
				</div>
			</div>

			<div class="table-container">
				<table class="history-table" id="historyTable">
					<thead>
					<tr>
						<th data-sort="date">Date <i class="fas fa-sort"></i></th>
						<th data-sort="type">Type <i class="fas fa-sort"></i></th>
						<th data-sort="reference">Reference <i class="fas fa-sort"></i></th>
						<th data-sort="warehouse">Warehouse <i class="fas fa-sort"></i></th>
						<th data-sort="quantity">Quantity <i class="fas fa-sort"></i></th>
						<th data-sort="user">User <i class="fas fa-sort"></i></th>
						<th data-sort="notes">Notes <i class="fas fa-sort"></i></th>
						<th>Actions</th>
					</tr>
					</thead>
					<tbody id="historyTableBody">
					@foreach($transactions as $transaction)
						<tr class="transaction-row"
							data-type="{{ $transaction->type }}"
							data-warehouse="{{ $transaction->warehouse_id }}"
							data-user="{{ $transaction->created_by }}"
							data-date="{{ $transaction->created_at->format('Y-m-d') }}">
							<td>
								<div class="date-cell">
									<span class="date">{{ $transaction->created_at->format('M j, Y') }}</span>
									<span class="time">{{ $transaction->created_at->format('H:i') }}</span>
								</div>
							</td>
							<td>
                                <span class="transaction-type type-{{ $transaction->type }}">
                                    <i class="fas
                                        @if($transaction->type == 'in') fa-arrow-down
                                        @elseif($transaction->type == 'out') fa-arrow-up
                                        @elseif($transaction->type == 'transfer') fa-exchange-alt
                                        @else fa-adjust @endif
                                    "></i>
                                    {{ ucfirst($transaction->type) }}
                                </span>
							</td>
							<td>
								<span class="reference-number">{{ $transaction->reference_number }}</span>
							</td>
							<td>
								@if($transaction->warehouse)
									<div class="warehouse-info">
										<i class="fas fa-warehouse"></i>
										<span>{{ $transaction->warehouse->name }}</span>
									</div>
								@else
									<span class="text-muted">N/A</span>
								@endif
							</td>
							<td>
                                <span class="quantity
                                    @if($transaction->type == 'in') text-success
                                    @elseif($transaction->type == 'out') text-danger
                                    @else text-warning @endif">
                                    @if($transaction->type == 'in') + @elseif($transaction->type == 'out') - @endif
									{{ number_format($transaction->quantity, 0) }}
                                </span>
							</td>
							<td>
								<div class="user-info">
									<i class="fas fa-user"></i>
									<span>{{ $transaction->user->name ?? 'System' }}</span>
								</div>
							</td>
							<td>
                                <span class="notes" title="{{ $transaction->notes }}">
                                    {{ Str::limit($transaction->notes, 50) }}
                                </span>
							</td>
							<td>
								<div class="action-buttons">
									<button class="btn-icon view-details" title="View Details" data-id="{{ $transaction->id }}">
										<i class="fas fa-eye"></i>
									</button>
									@if(auth()->user()->can('update', $transaction))
										<button class="btn-icon edit-transaction" title="Edit" data-id="{{ $transaction->id }}">
											<i class="fas fa-edit"></i>
										</button>
									@endif
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<div class="pagination-container">
				<div class="pagination-info">
					Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of
					<span id="totalRecords">{{ $transactions->total() }}</span> entries
				</div>
				<div class="pagination-controls">
					{{ $transactions->links() }}
				</div>
			</div>
		</div>
	</div>

	<!-- Transaction Details Modal -->
	<div id="transactionModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<h2>Transaction Details</h2>
				<span class="close">&times;</span>
			</div>
			<div class="modal-body" id="transactionDetails">
				<!-- Details will be loaded via AJAX -->
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="application/javascript" src="{{ asset('js/products/history.js') }}"></script>
@endsection
