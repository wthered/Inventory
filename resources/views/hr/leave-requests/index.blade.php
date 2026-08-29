@extends('templates.general')

@section('title', 'Leave Requests')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/hr/leave-requests/index.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-plane-departure icon-accent"></i> Leave Requests
				</h1>
				<p class="subtitle">Manage employee leave applications, approvals, and balances</p>
			</div>
		</div>

		<!-- System Feedback Alerts -->
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
			<form action="{{ route('hr.leaves.index') }}" method="GET" class="toolbar-form"
			      id="leave-filter-form">

				<!-- Employee Filter -->
				<div class="filter-item">
					<label for="employee_id" class="filter-label">Employee</label>
					<select name="employee_id" id="employee_id" class="form-control select-filter">
						<option value="">All Employees</option>
						@foreach($employeeGroups as $warehouseName => $group)
							<optgroup label="{{ $warehouseName }}">
								@foreach($group as $employee)
									<option
										value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
										{{ $employee->user?->account->full_name }} ({{ $employee->employee_code }})
									</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>

				<!-- Leave Type Filter -->
				<div class="filter-item">
					<label for="leave_type_id" class="filter-label">Leave Type</label>
					<select name="leave_type_id" id="leave_type_id" class="form-control select-filter">
						<option value="">All Leave Types</option>
						@foreach($types::cases() as $leave)
							<option value="{{ $leave->value }}">
								{{ $leave->label() }}
							</option>
						@endforeach
					</select>
				</div>

				<!-- Status Filter -->
				<div class="filter-item">
					<label for="status" class="filter-label">Status</label>
					<select name="status" id="leave_status" class="form-control select-filter">
						<option value="">All Statuses</option>
						@foreach($statuses::cases() as $status)
							<option value="{{ $status->value }}">{{ $status->label() }}</option>
						@endforeach
					</select>
				</div>

				<!-- Filter Actions -->
				<div class="filter-actions">
					<button type="submit" class="btn-secondary">
						<i class="fas fa-filter"></i> Filter
					</button>

					@if(request()->hasAny(['employee_id', 'leave_type_id', 'status']))
						<a href="{{ route('hr.leaves.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		@include('partials.leaves.index', ['leaveRequests' => $leaveRequests])

	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/employees/leaves/index.js') }}"></script>
@endsection
