@extends('templates.general')

@section('title', 'Leave Request #' . $leaveRequest->id)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/leave-requests/show.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-plane-departure icon-accent"></i> Leave Request Details
				</h1>
				<p class="subtitle">Request #{{ $leaveRequest->id }} &bull;
					Submitted {{ $leaveRequest->created_at->format('M d, Y') }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.leave-requests.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
				@can('leave_request.update')
					@if($leaveRequest->status === 'pending')
						<a href="{{ route('hr.leave-requests.edit', $leaveRequest->id) }}" class="btn-primary">
							<i class="fas fa-edit"></i> Edit Request
						</a>
					@endif
				@endcan
			</div>
		</div>

		<!-- Details Overview Card -->
		<div class="details-card">

			<!-- Header Bar & Status Badge -->
			<div class="details-header-bar">
				<div class="user-meta">
					<div class="avatar-circle">
						{{ strtoupper(substr($leaveRequest->employee->first_name, 0, 1) . substr($leaveRequest->employee->last_name, 0, 1)) }}
					</div>
					<div class="user-info">
						<h3>{{ $leaveRequest->employee->first_name }} {{ $leaveRequest->employee->last_name }}</h3>
						<span class="user-role">{{ $leaveRequest->employee->position->title ?? 'No Position Assigned' }} ({{ $leaveRequest->employee->employee_code }})</span>
					</div>
				</div>

				<div class="status-indicator">
					@switch($leaveRequest->status)
						@case('approved')
							<span class="badge badge-success"><i class="fas fa-check-circle"></i> Approved</span>
							@break
						@case('pending')
							<span class="badge badge-warning"><i
									class="fas fa-hourglass-half"></i> Pending Approval</span>
							@break
						@case('rejected')
							<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>
							@break
						@case('cancelled')
							<span class="badge badge-secondary"><i class="fas fa-ban"></i> Cancelled</span>
							@break
						@default
							<span class="badge badge-secondary">{{ ucfirst($leaveRequest->status->label()) }}</span>
					@endswitch
				</div>
			</div>

			<!-- Section 1: Leave Information -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-calendar-alt"></i> Leave Duration & Type</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Leave Category</span>
						<span class="info-value text-highlight">{{ $leaveRequest->leaveType->name ?? 'N/A' }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Total Duration</span>
						<span
							class="info-value font-mono">{{ $leaveRequest->total_days }} {{ Str::plural('day', $leaveRequest->total_days) }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Start Date</span>
						<span
							class="info-value">{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">End Date</span>
						<span
							class="info-value">{{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}</span>
					</div>
					<div class="info-item full-width">
						<span class="info-label">Reason / Application Notes</span>
						<span class="info-value">{{ $leaveRequest->reason ?? 'No notes provided by employee.' }}</span>
					</div>
				</div>
			</div>

			<hr class="info-divider">

			<!-- Section 2: Department & Employee Info -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-id-badge"></i> Employee Info</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Department</span>
						<span class="info-value">{{ $leaveRequest->employee->department->name ?? '-' }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Employee Code</span>
						<span class="info-value font-mono">{{ $leaveRequest->employee->employee_code }}</span>
					</div>
				</div>
			</div>

			<!-- Optional Approval Details -->
			@if($leaveRequest->action_by || $leaveRequest->rejection_reason)
				<hr class="info-divider">
				<div class="info-section">
					<h2 class="info-section-title"><i class="fas fa-user-check"></i> Approval Information</h2>
					<div class="info-grid">
						@if($leaveRequest->actionBy)
							<div class="info-item">
								<span class="info-label">Processed By</span>
								<span class="info-value">{{ $leaveRequest->actionBy->name }}</span>
							</div>
						@endif
						@if($leaveRequest->action_at)
							<div class="info-item">
								<span class="info-label">Action Date</span>
								<span
									class="info-value">{{ \Carbon\Carbon::parse($leaveRequest->action_at)->format('M d, Y H:i') }}</span>
							</div>
						@endif
						@if($leaveRequest->rejection_reason)
							<div class="info-item full-width">
								<span class="info-label">Rejection Reason</span>
								<span class="info-value text-danger">{{ $leaveRequest->rejection_reason }}</span>
							</div>
						@endif
					</div>
				</div>
			@endif

		</div>

	</div>
@endsection
