@extends('templates.general')

@section('title', 'Employees Directory')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/hr/employees/index.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-id-card icon-accent"></i> Employee Management
				</h1>
				<p class="subtitle">View, search, and manage your workforce accounts</p>
			</div>

			@can('employee.create')
				<a href="{{ route('hr.employees.create') }}" class="btn-primary">
					<i class="fas fa-user-plus"></i> Add Employee
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
			<form action="{{ route('hr.employees.index') }}" method="GET" class="toolbar-form" id="search-form">
				<div class="search-box">
					<i class="fas fa-search search-icon"></i>
					<input type="text"
					       id="search-input"
					       name="search"
					       value="{{ request('search') }}"
					       placeholder="Search by name, email, phone, ID..."
					       aria-label="Search employees"
					       class="form-control"
					       autocomplete="off">
				</div>

				<div class="filter-group">
					<select name="warehouse_id" id="warehouse-select" class="form-control select-filter">
						<option value="">All Warehouses</option>
						@foreach($warehouses as $warehouse)
							<option value="{{ $warehouse->id }}" @selected(request('warehouse') == $warehouse->id)>
								{{ $warehouse->name }}
							</option>
						@endforeach
					</select>

					<select name="department_id" id="department-select" class="form-control select-filter">
						<option value="">All Departments</option>
						@foreach($departments as $department)
							<option
								value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
								{{ $department->name }}
							</option>
						@endforeach
					</select>

					<select name="position_id" id="position-select" class="form-control select-filter">
						<option value="">All Positions</option>
						@foreach($positions as $position)
							<option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>
								{{ $position->title }}
							</option>
						@endforeach
					</select>

					<select name="status" id="status-select" class="form-control select-filter">
						<option value="">All Statuses</option>
						@foreach($statuses::cases() as $status)
							<option value="{{ $status->value }}" @selected(request('status') == $status->value)>
								{{ $status->label() }}
							</option>
						@endforeach
					</select>

					@if(request()->hasAny(['search', 'department_id', 'warehouse_id', 'position_id', 'status']))
						<a href="{{ route('hr.employees.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Employees Data Table -->
		<div class="table-card">
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th>Employee ID</th>
						<th>Full Name</th>
						<th>Phone</th>
						<th>Department</th>
						<th>Warehouse</th>
						<th>Position</th>
						<th>Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody id="employees-table-body">
					@include('partials.employees.index_rows', ['employees' => $employees])
					</tbody>
				</table>
			</div>

			<!-- Pagination Container -->
			<div id="pagination-container">
				@if($employees->hasPages())
					<div class="table-pagination" id="pagination">
						{{ $employees->links('pagination::simple') }}
					</div>
				@endif
			</div>
		</div>

	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/employees/index.js') }}"></script>
@endsection
