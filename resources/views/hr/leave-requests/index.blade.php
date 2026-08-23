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

			@can('leave_request.create')
				<a href="{{ route('hr.leave-requests.create') }}" class="btn-primary">
					<i class="fas fa-plus"></i> Request Leave
				</a>
			@endcan
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
			<form action="{{ route('hr.leave-requests.index') }}" method="GET" class="toolbar-form"
			      id="leave-filter-form">

				<!-- Employee Filter -->
				<div class="filter-item">
					<label for="employee_id" class="filter-label">Employee</label>
					<select name="employee_id" id="employee_id" class="form-control select-filter">
						<option value="">All Employees</option>
						@foreach($employees as $employee)
							<option
								value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
								{{ $employee->last_name }} {{ $employee->first_name }} ({{ $employee->employee_code }})
							</option>
						@endforeach
					</select>
				</div>

				<!-- Leave Type Filter -->
				<div class="filter-item">
					<label for="leave_type_id" class="filter-label">Leave Type</label>
					<select name="leave_type_id" id="leave_type_id" class="form-control select-filter">
						<option value="">All Leave Types</option>
						@foreach($leaveTypes::cases() as $leave)
							<option value="{{ $leave->value }}">
								{{ $leave->label() }}
							</option>
						@endforeach
					</select>
				</div>

				<!-- Status Filter -->
				<div class="filter-item">
					<label for="status" class="filter-label">Status</label>
					<select name="status" id="status" class="form-control select-filter">
						<option value="">All Statuses</option>
						<option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
						<option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved
						</option>
						<option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected
						</option>
						<option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
						</option>
					</select>
				</div>

				<!-- Filter Actions -->
				<div class="filter-actions">
					<button type="submit" class="btn-secondary">
						<i class="fas fa-filter"></i> Filter
					</button>

					@if(request()->hasAny(['employee_id', 'leave_type_id', 'status']))
						<a href="{{ route('hr.leave-requests.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Leave Requests Table -->
		<div class="table-card">
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th>Employee</th>
						<th>Type</th>
						<th>Start Date</th>
						<th>End Date</th>
						<th>Days</th>
						<th>Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($leaveRequests as $request)
						<tr>
							<td class="td-name">
								{{ $request->employee->last_name }} {{ $request->employee->first_name }}
								<small class="text-muted d-block">{{ $request->employee->employee_code }}</small>
							</td>
							<td>
								<span class="type-pill">
									{{ $request->leaveType->name ?? 'N/A' }}
								</span>
							</td>
							<td class="td-id">
								{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
							</td>
							<td class="td-id">
								{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
							</td>
							<td>
								<span class="font-mono">
									{{ $request->total_days }} {{ Str::plural('day', $request->total_days) }}
								</span>
							</td>
							<td>
								@switch($request->status)
									@case('approved')
										<span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>
										@break
									@case('pending')
										<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Pending</span>
										@break
									@case('rejected')
										<span class="badge badge-danger"><i class="fas fa-times"></i> Rejected</span>
										@break
									@case('cancelled')
										<span class="badge badge-secondary"><i class="fas fa-ban"></i> Cancelled</span>
										@break
									@default
										<span
											class="badge badge-secondary">{{ ucfirst($request->status->label()) }}</span>
								@endswitch
							</td>
							<td class="actions-cell text-right">
								<div class="action-buttons-group">
									<a href="{{ route('hr.leave-requests.show', $request->id) }}"
									   class="btn-action btn-view" title="View Request">
										<i class="fas fa-eye"></i>
									</a>
									@can('leave_request.update')
										@if($request->status === 'pending')
											<a href="{{ route('hr.leave-requests.edit', $request->id) }}"
											   class="btn-action btn-edit" title="Edit Request">
												<i class="fas fa-pencil-alt"></i>
											</a>
										@endif
									@endcan
									@can('leave_request.delete')
										<form action="{{ route('hr.leave-requests.destroy', $request->id) }}"
										      method="POST" class="inline-form"
										      onsubmit="return confirm('Are you sure you want to delete this leave request?');">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn-action btn-delete" title="Delete Request">
												<i class="fas fa-trash-alt"></i>
											</button>
										</form>
									@endcan
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="table-empty-state">
								<i class="fas fa-plane-slash empty-icon"></i>
								<p>No leave requests found matching the current criteria.</p>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			@if($leaveRequests->hasPages())
				<div class="table-pagination">
					{{ $leaveRequests->links('pagination::simple') }}
				</div>
			@endif
		</div>

	</div>
@endsection
