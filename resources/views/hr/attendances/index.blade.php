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
			<form action="{{ route('hr.attendances.index') }}" method="GET" class="toolbar-form"
			      id="attendance-filter-bar">

				<!-- Date Filter -->
				<div class="filter-item">
					<label for="att_date_start" class="filter-label">Start Date</label>
					<input type="date"
					       id="att_date_start"
					       name="date_start"
					       min="2021-08-30"
					       max="{{ today()->format('Y-m-d') }}"
					       value="{{ request()->input('date_start', date('Y-m-d')) }}"
					       class="form-control filter-input">
					<span class="error-text" id="error-att_date_start"></span>
				</div>

				<div class="filter-item">
					<label for="att_date_final" class="filter-label">Final Date</label>
					<input type="date"
					       id="att_date_final"
					       name="date_final"
					       min="2021-08-31"
					       max="{{ today()->format('Y-m-d') }}"
					       value="{{ date('Y-m-d') }}"
					       class="form-control filter-input">
					<span class="error-text" id="error-att_date_final"></span>
				</div>

				<!-- Employee Search / Dropdown -->
				<div class="filter-item">
					<label for="employee_id" class="filter-label">Employee</label>
					<select name="employee_id" id="employee_id" class="form-control select-filter filter-input">
						<option value="">All Employees</option>
						@foreach($employeeGroups as $warehouseName => $employees)
							<optgroup label="{{ $warehouseName }}">
								@foreach($employees as $employee)
									<option value="{{ $employee->id }}">
										{{ $employee->user?->account->full_name }} ({{ $employee->employee_code }})
									</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
					<span class="error-text" id="error-employee_id"></span>
				</div>

				<!-- Status Dropdown -->
				<div class="filter-item">
					<label for="att_status" class="filter-label">Status</label>
					<select name="status" id="att_status" class="form-control select-filter filter-input">
						<option value="">All Statuses</option>
						@foreach($statuses::cases() as $status)
							<option
								value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
								{{ $status->label() }}
							</option>
						@endforeach
					</select>
					<span class="error-text" id="error-att_status"></span>
				</div>

				<!-- Filter Actions -->
				<div class="filter-actions">
					<button type="submit" class="btn-secondary">
						<i class="fas fa-filter"></i> Filter
					</button>

					@if(request()->hasAny(['date_start', 'date_final', 'employee_id', 'status']))
						<a href="{{ route('hr.attendances.index') }}" class="btn-reset" title="Clear Filters">
							<i class="fas fa-times"></i> Clear
						</a>
					@endif
				</div>
			</form>
		</div>

		<!-- Attendance Data Table Container -->
		<div class="table-card">
			<div class="table-wrapper">
				<table class="data-table" id="attendance-table-container">
					<thead>
					<tr>
						<th>Date</th>
						<th>Employee</th>
						<th>Clock In</th>
						<th>Clock Out</th>
						<th>Overtime Hours</th>
						<th>Status</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($attendances as $attendance)
						<tr>
							<td class="td-id">
								{{ $attendance->work_date->format('d-m-Y') }}
							</td>
							<td class="td-name">
								{{ $attendance->employee->user->account->full_name }}
								<small class="text-muted d-block">
									{{ $attendance->employee->employee_code }}
								</small>
							</td>
							<td>
								<span class="time-badge">
									<i class="far fa-clock icon-in"></i>
									{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '--:--' }}
								</span>
							</td>
							<td>
								<span class="time-badge">
									<i class="far fa-clock icon-out"></i>
									{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '--:--' }}
								</span>
							</td>
							<td>
								<span class="font-mono">
									{{ $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . ' hrs' : '-' }}
								</span>
							</td>
							<td>
								<span class="font-mono">
									{{ $attendance->status->label() }}
								</span>
							</td>
							<td class="actions-cell text-right">
								<div class="action-buttons-group">
									@can('attendance.update')
										<a href="{{ route('hr.attendances.edit', ['employee' => $attendance->employee, 'date' => $attendance->work_date]) }}"
										   class="btn-action btn-edit" title="Edit Log">
											<i class="fas fa-pencil-alt"></i>
										</a>
									@endcan
									@can('attendance.delete')
										<form
											action="{{ route('hr.attendances.destroy', ['employee' => $attendance->employee]) }}"
											method="POST" class="inline-form"
											onsubmit="return confirm('Are you sure you want to delete this log?');">
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

@section('scripts')
	<script src="{{ asset('js/employees/attendances/filter.js') }}"></script>
@endsection
