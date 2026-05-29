@extends('templates.general')

@section('title', $warehouse->name . ' - Warehouse Details')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/warehouses/show.css') }}">
@endsection

@section('content')
	<div class="warehouse-container">
		<header class="show-header">
			<div class="header-main">
				<a href="{{ route('inventory.warehouses.warehouse.index') }}" class="back-link">← Back to Warehouses</a>
				<div class="header-title">
					<h1>{{ $warehouse->name }}</h1>
					<div class="header-badges">
                    <span class="status-badge status-{{ $warehouse->is_active ? 'active' : 'inactive' }}">
                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                    </span>
						@if($warehouse->isPrimary)
							<span class="status-badge primary-badge">⭐ Primary</span>
						@endif
					</div>
				</div>
			</div>
			<div class="header-actions">
				<button class="btn-print" onclick="window.print()">🖨️ Print</button>
				<a href="{{ route('inventory.warehouses.warehouse.edit', $warehouse->id) }}" class="btn-outline">✏️ Edit</a>
				<button class="btn-primary" onclick="showReportModal()">📊 Generate Report</button>
			</div>
		</header>

		<div class="stats-grid">
			<div class="stat-box">
				<div class="stat-icon">📦</div>
				<div class="stat-content">
					<div class="stat-number">{{ $warehouse->totalItems ?? 0 }}</div>
					<div class="stat-label">Total Items</div>
				</div>
			</div>
			<div class="stat-box">
				<div class="stat-icon">💰</div>
				<div class="stat-content">
					<div class="stat-number">${{ number_format($totalValue ?? 0, 2) }}</div>
					<div class="stat-label">Total Value</div>
				</div>
			</div>
			<div class="stat-box">
				<div class="stat-icon">🔄</div>
				<div class="stat-content">
					<div class="stat-number">{{ $transfersCount ?? 0 }}</div>
					<div class="stat-label">Monthly Transfers</div>
				</div>
			</div>
			<div class="stat-box">
				<div class="stat-icon">👥</div>
				<div class="stat-content">
					<div class="stat-number">{{ $staffCount ?? 0 }}</div>
					<div class="stat-label">Assigned Staff</div>
				</div>
			</div>
			<div class="stat-box">
				<div class="stat-icon">📈</div>
				<div class="stat-content">
					<div class="stat-number {{ ($warehouse->occupancyPercentage ?? 0) > 85 ? 'text-error' : '' }}">
						{{ $warehouse->occupancyPercentage ?? 0 }}%
					</div>
					<div class="stat-label">Occupancy</div>
				</div>
			</div>
		</div>

		<div class="main-grid">
			<div class="info-column">
				<div class="card">
					<div class="card-header"><h3>🏢 Warehouse Details</h3></div>
					<div class="card-body">
						<div class="info-list">
							<div class="info-item">
								<span class="info-label">Identification Code</span>
								<span class="info-value code-value">{{ $warehouse->code }}</span>
							</div>
							<div class="info-item">
								<span class="info-label">Manager</span>
								<span class="info-value">
                                {{ $warehouse->manager?->account?->full_name ?? 'Not Assigned' }}
                            </span>
							</div>
							<div class="info-item">
								<span class="info-label">Warehouse Type</span>
								<span class="info-value">{{ $warehouse->type->label() }}</span>
							</div>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header"><h3>📏 Storage Capacity</h3></div>
					<div class="card-body">
						<div class="capacity-stats">
							<div class="capacity-item">
								<span class="capacity-label">Used Space</span>
								<span class="capacity-value">{{ number_format($warehouse->current_capacity, 2) }} m²</span>
							</div>
							<div class="capacity-item">
								<span class="capacity-label">Total Space</span>
								<span class="capacity-value">{{ number_format($warehouse->capacity, 2) }} m²</span>
							</div>
						</div>
						<div class="capacity-progress">
							<div class="progress-bar">
								<div class="progress-fill" style="width: {{ $warehouse->occupancyPercentage }}%"></div>
							</div>
							<div class="progress-labels">
								<span>0%</span>
								<span>{{ $warehouse->occupancyPercentage }}% Occupied</span>
								<span>100%</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="contact-column">
				<div class="card" style="height: calc(100% - var(--space-lg));">
					<div class="card-header"><h3>📍 Location & Contact</h3></div>
					<div class="card-body">
						<div class="address-block">
							<p><strong>Address:</strong> {{ $warehouse->address }}</p>
							<p>{{ $warehouse->city }}, {{ $warehouse->postal_code }}</p>
							<p>{{ $warehouse->country }}</p>
						</div>
						<div class="contact-list">
							<div class="info-item">
								<span class="info-label">Phone Number</span>
								<span class="info-value">{{ $warehouse->phone ?? 'N/A' }}</span>
							</div>
							<div class="info-item">
								<span class="info-label">Email Address</span>
								<span class="info-value">{{ $warehouse->email ?? 'N/A' }}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card storage-card">
			<div class="card-header">
				<div class="header-with-filter">
					<h3>🗺️ Warehouse Locations (Storage Map)</h3>
						<div class="filter-group">
							<select name="zone" id="filter-zone" class="form-select-sm cascade-filter" data-next="aisle">
								<option value="">All Zones</option>
								@foreach($filterOptions['zones'] as $option)
									<option value="{{ $option['value'] }}" {{ request('zone') == $option['value'] ? 'selected' : '' }}>
										{{ $option['text'] }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="filter-group">
							<select name="aisle" id="filter-aisle" class="form-select-sm cascade-filter" data-next="rack">
								<option value="">Aisle</option>
							</select>
						</div>
						<div class="filter-group">
							<select name="rack" id="filter-rack" class="form-select-sm cascade-filter" data-next="shelf">
								<option value="">Rack</option>
							</select>
						</div>
						<div class="filter-group">
							<select name="shelf" id="filter-shelf" class="form-select-sm cascade-filter">
								<option value="">Shelf</option>
							</select>
						</div>
						<input type="hidden" value="{{ $warehouse->id }}" name="warehouse_id" id="filter-warehouse">
				</div>
			</div>

			<div class="card-body">
				<div class="locations-grid" id="locations-grid">
					@foreach($locations as $location)
						@include('partials.location_card', ['location' => $location])
					@endforeach
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="application/javascript" src="{{ asset('js/warehouses/show.js') }}"></script>
	<script type="application/javascript" src="{{ asset('js/warehouses/filter.js') }}"></script>
@endsection