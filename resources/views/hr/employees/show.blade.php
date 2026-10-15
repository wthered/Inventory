@extends('templates.general')

@section('title', 'Employee Profile: ' . $employee->full_name)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/hr/employees/show.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-id-card icon-accent"></i> {{ $employee->user->account->full_name }}
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
						<img src="{{ $employee->user->account->avatar_url }}"
						     alt="{{ $employee->user->account->full_name }} Avatar"
						     class="avatar-img">
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
						<div class="detail-item">
							<i class="fas fa-calendar-alt"></i>
							<div>
								<span class="item-label">Termination Date</span>
								<strong>{{ optional($employee->termination_date)->format('d/m/Y') ?? '-' }}</strong>
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

				<!-- Leave Requests Section -->
				<div class="info-card">
					<div class="card-header-wrapper mb-3">
						<h3 class="card-title"><i class="fas fa-plane-departure"></i> Leave Requests</h3>
						<button type="button" class="btn-sm btn-primary" id="btn-open-leave-modal">
							<i class="fas fa-plus"></i> Request Leave
						</button>
					</div>

					<!-- Filter Bar Wrapper -->
					<div class="filter-bar mb-3" id="leave-filter-bar">
						<div class="filter-group">
							<label for="leave_type">Type</label>
							<select name="leave_type" id="leave_type" class="filter-input">
								<option value="">All Types</option>
								@foreach($types::cases() as $type)
									<option value="{{ $type->value }}">{{ $type->label() }}</option>
								@endforeach
							</select>
							<span class="error-text" id="error-leave_type"></span>
						</div>

						<div class="filter-group">
							<label for="leave_status">Status</label>
							<select name="leave_status" id="leave_status" class="filter-input">
								<option value="">All Statuses</option>
								@foreach($leaves::cases() as $status)
									<option value="{{ $status->value }}">{{ $status->label() }}</option>
								@endforeach
							</select>
							<span class="error-text" id="error-leave_status"></span>
						</div>

						<div class="filter-group">
							<label for="leave_date_from">From</label>
							<input type="date" name="leave_date_from" id="leave_date_from" class="filter-input"
							       value="{{ $employee->hire_date->format('Y-m-d') }}"
							>
							<span class="error-text" id="error-leave_date_from"></span>
						</div>

						<div class="filter-group">
							<label for="leave_date_to">To</label>
							<input type="date" name="leave_date_to" id="leave_date_to" class="filter-input"
							       value="{{ today()->format('Y-m-d') }}">
							<span class="error-text" id="error-leave_date_to"></span>
						</div>
					</div>

					<!-- Table Output Container -->
					<div id="leave-table-container">
						@include('partials.employees.leave_rows', ['leaveRequests' => $leaveRequests])
					</div>
				</div>

				<!-- Attendance Records Section -->
				<div class="info-card">
					<div class="card-header-wrapper mb-3">
						<h3 class="card-title"><i class="fas fa-clock"></i> Attendance Records</h3>
						<button type="button" class="btn-sm btn-primary" id="btn-open-attendance-modal">
							<i class="fas fa-plus"></i> Log Attendance
						</button>
					</div>

					<!-- Attendance Filter Bar Wrapper -->
					<div class="filter-bar mb-3" id="attendance-filter-bar">
						<!-- Hidden employee reference -->
						<input type="hidden" name="employee_id" id="employee_id" value="{{ $employee->id }}">

						<div class="filter-group">
							<label for="att_status">Status</label>
							<select name="att_status" id="att_status" class="filter-input">
								<option value="">All Statuses</option>
								@foreach($statuses::cases() as $status)
									<option value="{{ $status->value }}">
										{{ $status->label() }}
									</option>
								@endforeach
							</select>
							<span class="error-text" id="error-att_status"></span>
						</div>

						<div class="filter-group">
							<label for="att_date_start">From Date</label>
							<input type="date" name="att_date_start" id="att_date_start" class="filter-input"
							       value="{{ $employee->hire_date->format('Y-m-d') }}"
							       min="{{ $employee->hire_date->format('Y-m-d') }}"
							       max="{{ today()->format('Y-m-d') }}"
							>
							<span class="error-text" id="error-att_date_start"></span>
						</div>

						<div class="filter-group">
							<label for="att_date_final">To Date</label>
							<input type="date" name="att_date_final" id="att_date_final" class="filter-input"
							       value="{{ today()->format('Y-m-d') }}"
							       min="{{ $employee->hire_date->format('Y-m-d') }}"
							       max="{{ today()->format('Y-m-d') }}">
							<span class="error-text" id="error-att_date_final"></span>
						</div>
					</div>

					<!-- Attendance Table Output Container -->
					<div id="attendance-table-container">
						@include('partials.employees.attendance_rows', [
							'attendances' => $attendances,
						])
					</div>
				</div>

			</div>
		</div>

	</div>
	@include('partials.employees.show.modals', ['employee' => $employee])
@endsection

@section('scripts')
	<script src="{{ asset('js/employees/attendances/filter.js') }}"></script>
	<script src="{{ asset('js/employees/leaves/filter.js') }}"></script>
	<script src="{{ asset('js/employees/show/modals.js') }}"></script>
@endsection
