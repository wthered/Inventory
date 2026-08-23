@extends('templates.general')

@section('title', 'Employee Profile: ' . $employee->full_name)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/employees/show.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-id-card icon-accent"></i> {{ $employee->full_name }}
				</h1>
				<p class="subtitle">Employee Code: <strong>{{ $employee->employee_code }}</strong></p>
			</div>

			<div class="header-actions">
				<a href="{{ route('hr.employees.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
				<a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn-primary">
					<i class="fas fa-edit"></i> Edit Profile
				</a>
			</div>
		</div>

		<!-- Main Profile Layout -->
		<div class="profile-layout">

			<!-- Left Column: Primary Overview Card -->
			<div class="profile-sidebar">
				<div class="info-card align-center">
					<div class="avatar-circle">
						<span>{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</span>
					</div>
					<h2 class="profile-name">{{ $employee->full_name }}</h2>
					<p class="profile-role">{{ optional($employee->position)->title ?? 'No Position Assigned' }}</p>

					<div class="status-badge-wrapper">
						@if($employee->is_active)
							<span class="badge badge-success"><i class="fas fa-check-circle"></i> Active Employee</span>
						@else
							<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Inactive</span>
						@endif
					</div>

					<hr class="card-divider"/>

					<div class="quick-details">
						<div class="detail-item">
							<i class="fas fa-building"></i>
							<div>
								<span class="item-label">Department</span>
								<strong>{{ optional($employee->department)->name ?? '-' }}</strong>
							</div>
						</div>
						<div class="detail-item">
							<i class="fas fa-warehouse"></i>
							<div>
								<span class="item-label">Assigned Warehouse</span>
								<strong>{{ optional($employee->warehouse)->name ?? '-' }}</strong>
							</div>
						</div>
						<div class="detail-item">
							<i class="fas fa-user-tag"></i>
							<div>
								<span class="item-label">System User Account</span>
								<strong>{{ optional($employee->user)->email ?? 'None Linked' }}</strong>
							</div>
						</div>
						<div class="detail-item">
							<i class="fas fa-calendar-alt"></i>
							<div>
								<span class="item-label">Hire Date</span>
								<strong>{{ optional($employee->hire_date)->format('d/m/Y') ?? '-' }}</strong>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Right Column: Detailed Sections & Related Data -->
			<div class="profile-content">

				<!-- Personal & Tax Information -->
				<div class="info-card">
					<h3 class="card-title"><i class="fas fa-user-shield"></i> Personal & Tax Details</h3>
					<div class="info-grid">
						<div class="info-group">
							<span class="info-label">Phone Number</span>
							<span class="info-value">{{ $employee->phone ?? '-' }}</span>
						</div>
						<div class="info-group">
							<span class="info-label">AFM (Tax ID)</span>
							<span class="info-value">{{ optional($employee->detail)->afm ?? '-' }}</span>
						</div>
						<div class="info-group">
							<span class="info-label">AMKA (Social Security)</span>
							<span class="info-value">{{ optional($employee->detail)->social_security ?? '-' }}</span>
						</div>
						<div class="info-group">
							<span class="info-label">ID Card Number</span>
							<span class="info-value">{{ optional($employee->detail)->id_card_number ?? '-' }}</span>
						</div>
						<div class="info-group">
							<span class="info-label">Date of Birth</span>
							<span
								class="info-value">{{ optional(optional($employee->detail)->birth_date)->format('d/m/Y') ?? '-' }}</span>
						</div>
						<div class="info-group full-width">
							<span class="info-label">Address</span>
							<span class="info-value">
								{{ optional($employee->detail)->address ?? '-' }}
								@if(optional($employee->detail)->city)
									, {{ $employee->detail->city }}
								@endif
								@if(optional($employee->detail)->postal_code)
									({{ $employee->detail->postal_code }})
								@endif
							</span>
						</div>
						<div class="info-group full-width">
							<span class="info-label">IBAN Account</span>
							<span class="info-value code-font">{{ optional($employee->detail)->iban ?? '-' }}</span>
						</div>
					</div>
				</div>

				<!-- Emergency Contact -->
				<div class="info-card">
					<h3 class="card-title"><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
					<div class="info-grid">
						<div class="info-group">
							<span class="info-label">Contact Person</span>
							<span
								class="info-value">{{ optional($employee->detail)->emergency_contact_name ?? '-' }}</span>
						</div>
						<div class="info-group">
							<span class="info-label">Contact Phone</span>
							<span
								class="info-value">{{ optional($employee->detail)->emergency_contact_phone ?? '-' }}</span>
						</div>
					</div>
				</div>

				<!-- Recent Leave Requests -->
				<div class="info-card">
					<h3 class="card-title"><i class="fas fa-plane-departure"></i> Recent Leave Requests</h3>
					@if($employee->leaveRequests && $employee->leaveRequests->count() > 0)
						<table class="data-table">
							<thead>
							<tr>
								<th>Type</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Days</th>
								<th>Status</th>
							</tr>
							</thead>
							<tbody>
							@foreach($employee->leaveRequests->take(5) as $leave)
								<tr>
									<td>{{ $leave->leave_type->value ?? $leave->leave_type }}</td>
									<td>{{ optional($leave->start_date)->format('d/m/Y') }}</td>
									<td>{{ optional($leave->end_date)->format('d/m/Y') }}</td>
									<td>{{ $leave->total_days }}</td>
									<td>
											<span
												class="badge status-{{ strtolower($leave->status->value ?? $leave->status) }}">
												{{ $leave->status->value ?? $leave->status }}
											</span>
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					@else
						<p class="empty-state-text">No leave requests logged for this employee.</p>
					@endif
				</div>

				<!-- Recent Attendance -->
				<div class="info-card">
					<h3 class="card-title"><i class="fas fa-clock"></i> Recent Attendance Records</h3>
					@if($employee->attendances->isNotEmpty())
						<table class="data-table">
							<thead>
							<tr>
								<th>Date</th>
								<th>Check In</th>
								<th>Check Out</th>
								<th>Overtime</th>
							</tr>
							</thead>
							<tbody>
							@foreach($employee->attendances->sortByDesc('work_date')->take(5) as $attendance)
								<tr>
									<td>{{ optional($attendance->work_date)->format('d/m/Y') }}</td>
									<td>{{ optional($attendance->check_in)->format('H:i') }}</td>
									<td>{{ optional($attendance->check_out)->format('H:i') ?? '-' }}</td>
									<td>{{ $attendance->overtime_hours > 0 ? $attendance->overtime_hours . ' hrs' : '-' }}</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					@else
						<p class="empty-state-text">No attendance entries recorded yet.</p>
					@endif
				</div>

			</div>
		</div>

	</div>
@endsection
