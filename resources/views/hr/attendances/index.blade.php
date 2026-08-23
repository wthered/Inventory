@extends('templates.general')

@section('title', 'Attendance Records')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/hr/attendances/index.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-clock icon-accent"></i> Attendance Records
				</h1>
				<p class="subtitle">Track daily clock-ins, clock-outs, and work hours for employees</p>
			</div>

			@can('attendance.create')
				<a href="{{ route('hr.attendances.create') }}" class="btn-primary">
					<i class="fas fa-business-time"></i> Log Attendance
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
			<form action="{{ route('hr.attendances.index') }}" method="GET" class="toolbar-form" id="attendance-filter-form">

				<!-- Date Filter -->
				<div class="filter-item">
					<label for="date" class="filter-label">Date</label>
					<input type="date"
					       id="date"
					       name="date"
					       value="{{ request('date', date('Y-m-d')) }}"
					       class="form-control">
				</div>

				<!-- Employee Search / Dropdown -->
				<div class="filter-item">
					<label for="employee_id" class="filter-label">Employee</label>
					<select name="employee_id" id="employee_id" class="form-control select-filter">
						<option value="">All Employees</option>
						@foreach($employees as $employee)
							<option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
								{{ $employee->last_name }} {{ $employee->first_name }} ({{ $employee->employee_code }})
							</option>
						@endforeach
					</select>
				</div>

				<!-- Status Dropdown -->
				<div class="filter-item">
					<label for="status" class="filter-label">Status</label>
					<select name="status" id="status" class="form-control select-filter">
						<option value="">All Statuses</option>
						<option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
						<option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
						<option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
						<option value="half_day" {{ request('status') === 'half_day' ? 'selected' : '' }}>Half Day</option>
					</select>
				</div>

				<!-- Filter Actions -->
				<div class="filter-actions">
					<button type="submit" class="btn-secondary">
						<i class="fas fa-filter"></i> Filter
					</button>

					@if(request()->hasAny(['date', 'employee_id', 'status']))
						<a href="{{ route('hr.attendances.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Attendance Data Table -->
		<div class="table-card">
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th>Date</th>
						<th>Employee</th>
						<th>Clock In</th>
						<th>Clock Out</th>
						<th>Total Hours</th>
						<th>Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($attendances as $attendance)
						<tr>
							<td class="td-id">
								{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}
							</td>
							<td class="td-name">
								{{ $attendance->employee->last_name }} {{ $attendance->employee->first_name }}
								<small class="text-muted d-block">{{ $attendance->employee->employee_code }}</small>
							</td>
							<td>
								<span class="time-badge">
									<i class="far fa-clock icon-in"></i>
									{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--' }}
								</span>
							</td>
							<td>
								<span class="time-badge">
									<i class="far fa-clock icon-out"></i>
									{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}
								</span>
							</td>
							<td>
								<span class="font-mono">
									{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' hrs' : '-' }}
								</span>
							</td>
							<td>
								@switch($attendance->status)
									@case('present')
										<span class="badge badge-success"><i class="fas fa-check"></i> Present</span>
										@break
									@case('late')
										<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Late</span>
										@break
									@case('absent')
										<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>
										@break
									@case('half_day')
										<span class="badge badge-info"><i class="fas fa-adjust"></i> Half Day</span>
										@break
									@default
										<span class="badge badge-secondary">{{ ucfirst($attendance->status) }}</span>
								@endswitch
							</td>
							<td class="actions-cell text-right">
								<div class="action-buttons-group">
									@can('attendance.update')
										<a href="{{ route('hr.attendances.edit', $attendance->id) }}" class="btn-action btn-edit" title="Edit Log">
											<i class="fas fa-pencil-alt"></i>
										</a>
									@endcan
									@can('attendance.delete')
										<form action="{{ route('hr.attendances.destroy', $attendance->id) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this log?');">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn-action btn-delete" title="Delete Log">
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
								<i class="fas fa-calendar-times empty-icon"></i>
								<p>No attendance records found for the selected criteria.</p>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			@if($attendances->hasPages())
				<div class="table-pagination">
					{{ $attendances->links('pagination::simple') }}
				</div>
			@endif
		</div>

	</div>
@endsection
