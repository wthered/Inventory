@extends('templates.general')

@section('title', 'Attendance Record #' . $attendance->id)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/attendances/show.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-clock icon-accent"></i> Attendance Record
				</h1>
				<p class="subtitle">Record #{{ $attendance->id }} &bull; {{ \Carbon\Carbon::parse($attendance->date)->format('F d, Y') }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.attendances.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
				@can('attendance.update')
					<a href="{{ route('hr.attendances.edit', $attendance->id) }}" class="btn-primary">
						<i class="fas fa-edit"></i> Edit Log
					</a>
				@endcan
			</div>
		</div>

		<!-- Details Overview Card -->
		<div class="details-card">

			<!-- Header Bar & Status Badge -->
			<div class="details-header-bar">
				<div class="user-meta">
					<div class="avatar-circle">
						{{ strtoupper(substr($attendance->employee->first_name, 0, 1) . substr($attendance->employee->last_name, 0, 1)) }}
					</div>
					<div class="user-info">
						<h3>{{ $attendance->employee->first_name }} {{ $attendance->employee->last_name }}</h3>
						<span class="user-role">{{ $attendance->employee->position->title ?? 'No Position Assigned' }} ({{ $attendance->employee->employee_code }})</span>
					</div>
				</div>

				<div class="status-indicator">
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
				</div>
			</div>

			<!-- Section 1: Time & Hours Breakdown -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-business-time"></i> Time Tracking Details</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Date</span>
						<span class="info-value text-highlight">{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Clock In Time</span>
						<span class="info-value font-mono">
							<i class="far fa-clock icon-in"></i>
							{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s') : '--:--:--' }}
						</span>
					</div>
					<div class="info-item">
						<span class="info-label">Clock Out Time</span>
						<span class="info-value font-mono">
							<i class="far fa-clock icon-out"></i>
							{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s') : '--:--:--' }}
						</span>
					</div>
					<div class="info-item">
						<span class="info-label">Total Calculated Hours</span>
						<span class="info-value font-mono">
							{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' hours' : '-' }}
						</span>
					</div>
					<div class="info-item full-width">
						<span class="info-label">Notes / Remarks</span>
						<span class="info-value">{{ $attendance->notes ?? 'No remarks provided for this entry.' }}</span>
					</div>
				</div>
			</div>

			<hr class="info-divider">

			<!-- Section 2: Employee Context -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-id-badge"></i> Employee Context</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Department</span>
						<span class="info-value">{{ $attendance->employee->department->name ?? '-' }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Assigned Warehouse</span>
						<span class="info-value">{{ $attendance->employee->warehouse->name ?? '-' }}</span>
					</div>
				</div>
			</div>

		</div>

	</div>
@endsection
