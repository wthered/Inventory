@extends('templates.general')

@section('title', 'Attendance for Employee #' . $employee->id)

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
				<p class="subtitle">Record for employee
					#{{ $employee->id }} &bull; {{ $attendance->work_date->format('F d, Y') }}</p>
			</div>

			<div class="action-buttons-group">
				<!-- 1. Pagination Group (Prev / Next side-by-side) -->
				@if($isPrevDisabled)
					<button class="btn-secondary disabled" disabled title="Previous work date is before hire date">
						<i class="fas fa-chevron-left"></i> Previous Work Date
					</button>
				@else
					<a href="{{ route('hr.attendances.show', ['employee' => $employee->id, 'date' => $prevWorkDate->format('Y-m-d')]) }}"
					   class="btn-secondary" title="{{ $prevWorkDate->format('l, j F Y') }}">
						<i class="fas fa-chevron-left"></i> Previous Work Date
					</a>
				@endif

				@if($isNextDisabled)
					<button class="btn-secondary disabled" disabled title="Next Work Date is in the future">
						Next Work Date <i class="fas fa-chevron-right"></i>
					</button>
				@else
					<a href="{{ route('hr.attendances.show', ['employee' => $employee->id, 'date' => $nextWorkDate->format('Y-m-d')]) }}"
					   class="btn-secondary" title="{{ $nextWorkDate->format('l, j F Y') }}">
						Next Work Date <i class="fas fa-chevron-right"></i>
					</a>
				@endif

				<!-- 2. Context Actions -->
				<a href="{{ route('hr.attendances.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>

				<!-- 3. Primary Mutation Action (Far Right) -->
				@can('attendance.update')
					<a href="{{ route('hr.attendances.edit', ['employee' => $employee->id, 'date' => $attendance->work_date->format('Y-m-d')]) }}"
					   class="btn-primary">
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
						<img src="{{ $employee->user->account->avatar_url }}"
						     alt="{{ $employee->user->account->full_name }} Avatar">
					</div>
					<div class="user-info">
						<h3>{{ $employee->user->account->full_name }}</h3>
						<span class="user-role">{{ $attendance->employee->position->title ?? 'No Position Assigned' }} ({{ $attendance->employee->employee_code }})</span>
					</div>
				</div>

				<div class="status-indicator">
					<span class="badge {{ $attendance->status->badgeClass() }}">
						<i class="{{ $attendance->status->icon() }}"></i> {{ $attendance->status->label() }}
					</span>
				</div>
			</div>

			<!-- Section 1: Time & Hours Breakdown -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-business-time"></i> Time Tracking Details</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Date</span>
						<span
							class="info-value text-highlight">{{ $attendance->work_date->format('M d, Y') }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Clock In Time</span>
						<span class="info-value font-mono">
							<i class="far fa-clock icon-in"></i>
							{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '--:--:--' }}
						</span>
					</div>
					<div class="info-item">
						<span class="info-label">Clock Out Time</span>
						<span class="info-value font-mono">
							<i class="far fa-clock icon-out"></i>
							{{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : '--:--:--' }}
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
						<span
							class="info-value">{{ $attendance->notes ?? 'No remarks provided for this entry.' }}</span>
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
