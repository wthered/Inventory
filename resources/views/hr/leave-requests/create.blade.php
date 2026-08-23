@extends('templates.general')

@section('title', 'Request Leave')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/leave-requests/create.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-plane-departure icon-accent"></i> Submit Leave Request
				</h1>
				<p class="subtitle">Apply for time off or record leave on behalf of an employee</p>
			</div>

			<a href="{{ route('hr.leave-requests.index') }}" class="btn-secondary">
				<i class="fas fa-arrow-left"></i> Back to List
			</a>
		</div>

		<!-- Error / Alert Messages -->
		@if($errors->any())
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-triangle"></i>
				<div>
					<strong>Please review the errors below:</strong>
					<ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		@endif

		<!-- Form Card -->
		<form action="{{ route('hr.leave-requests.store') }}" method="POST" class="form-card">
			@csrf

			<!-- Section: Request Information -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-calendar-alt"></i> Leave Details</h2>

				<div class="form-grid">

					<!-- Employee Selection -->
					<div class="form-group full-width">
						<label for="employee_id" class="form-label required">Employee</label>
						<select name="employee_id" id="employee_id"
						        class="form-control @error('employee_id') is-invalid @enderror" required>
							<option value="">-- Select Employee --</option>
							@foreach($employees as $employee)
								<option
									value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
									{{ $employee->last_name }} {{ $employee->first_name }}
									({{ $employee->employee_code }})
								</option>
							@endforeach
						</select>
						@error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Leave Type -->
					<div class="form-group full-width">
						<label for="leave_type_id" class="form-label required">Leave Type</label>
						<select name="leave_type_id" id="leave_type_id"
						        class="form-control @error('leave_type_id') is-invalid @enderror" required>
							<option value="">-- Select Leave Category --</option>
							@foreach($leaveTypes::cases() as $type)
								<option value="{{ $type->value }}">
									{{ $type->label() }}
								</option>
							@endforeach
						</select>
						@error('leave_type_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Start Date -->
					<div class="form-group">
						<label for="start_date" class="form-label required">Start Date</label>
						<input type="date"
						       id="start_date"
						       name="start_date"
						       value="{{ old('start_date', date('Y-m-d')) }}"
						       class="form-control @error('start_date') is-invalid @enderror"
						       required>
						@error('start_date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- End Date -->
					<div class="form-group">
						<label for="end_date" class="form-label required">End Date</label>
						<input type="date"
						       id="end_date"
						       name="end_date"
						       value="{{ old('end_date', date('Y-m-d')) }}"
						       class="form-control @error('end_date') is-invalid @enderror"
						       required>
						@error('end_date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Reason / Notes -->
					<div class="form-group full-width">
						<label for="reason" class="form-label">Reason for Request</label>
						<textarea id="reason"
						          name="reason"
						          rows="3"
						          class="form-control @error('reason') is-invalid @enderror"
						          placeholder="Provide optional details or notes regarding this leave application">{{ old('reason') }}</textarea>
						@error('reason') <span class="form-error">{{ $message }}</span> @enderror
					</div>

				</div>
			</div>

			<!-- Actions Bar -->
			<div class="form-actions">
				<a href="{{ route('hr.leave-requests.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-paper-plane"></i> Submit Request
				</button>
			</div>
		</form>

	</div>
@endsection
