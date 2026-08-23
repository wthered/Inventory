@extends('templates.general')

@section('title', 'Edit Leave Request #' . $leaveRequest->id)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/leave-requests/edit.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-plane-departure icon-accent"></i> Edit Leave Request
				</h1>
				<p class="subtitle">Modify leave parameters or process status for Request #{{ $leaveRequest->id }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.leave-requests.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
			</div>
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
		<form action="{{ route('hr.leave-requests.update', $leaveRequest->id) }}" method="POST" class="form-card">
			@csrf
			@method('PUT')

			<!-- Section: Request Information -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-calendar-alt"></i> Leave Request Details</h2>

				<div class="form-grid">

					<!-- Employee Selection -->
					<div class="form-group full-width">
						<label for="employee_id" class="form-label required">Employee</label>
						<select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
							<option value="">-- Select Employee --</option>
							@foreach($employees as $employee)
								<option value="{{ $employee->id }}" {{ old('employee_id', $leaveRequest->employee_id) == $employee->id ? 'selected' : '' }}>
									{{ $employee->last_name }} {{ $employee->first_name }} ({{ $employee->employee_code }})
								</option>
							@endforeach
						</select>
						@error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Leave Type -->
					<div class="form-group">
						<label for="leave_type_id" class="form-label required">Leave Type</label>
						<select name="leave_type_id" id="leave_type_id" class="form-control @error('leave_type_id') is-invalid @enderror" required>
							<option value="">-- Select Category --</option>
							@foreach($leaveTypes as $type)
								<option value="{{ $type->id }}" {{ old('leave_type_id', $leaveRequest->leave_type_id) == $type->id ? 'selected' : '' }}>
									{{ $type->name }}
								</option>
							@endforeach
						</select>
						@error('leave_type_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Status -->
					<div class="form-group">
						<label for="status" class="form-label required">Status</label>
						<select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
							<option value="pending" {{ old('status', $leaveRequest->status) === 'pending' ? 'selected' : '' }}>Pending</option>
							<option value="approved" {{ old('status', $leaveRequest->status) === 'approved' ? 'selected' : '' }}>Approved</option>
							<option value="rejected" {{ old('status', $leaveRequest->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
							<option value="cancelled" {{ old('status', $leaveRequest->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
						</select>
						@error('status') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Start Date -->
					<div class="form-group">
						<label for="start_date" class="form-label required">Start Date</label>
						<input type="date"
						       id="start_date"
						       name="start_date"
						       value="{{ old('start_date', \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d')) }}"
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
						       value="{{ old('end_date', \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d')) }}"
						       class="form-control @error('end_date') is-invalid @enderror"
						       required>
						@error('end_date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Reason -->
					<div class="form-group full-width">
						<label for="reason" class="form-label">Reason / Description</label>
						<textarea id="reason"
						          name="reason"
						          rows="3"
						          class="form-control @error('reason') is-invalid @enderror"
						          placeholder="Details provided by employee">{{ old('reason', $leaveRequest->reason) }}</textarea>
						@error('reason') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Rejection Reason (Conditional Context) -->
					<div class="form-group full-width">
						<label for="rejection_reason" class="form-label">Rejection Reason (If Applicable)</label>
						<textarea id="rejection_reason"
						          name="rejection_reason"
						          rows="2"
						          class="form-control @error('rejection_reason') is-invalid @enderror"
						          placeholder="State the reason if rejecting this request">{{ old('rejection_reason', $leaveRequest->rejection_reason) }}</textarea>
						@error('rejection_reason') <span class="form-error">{{ $message }}</span> @enderror
					</div>

				</div>
			</div>

			<!-- Form Action Footer -->
			<div class="form-actions">
				<a href="{{ route('hr.leave-requests.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-save"></i> Save Changes
				</button>
			</div>
		</form>

	</div>
@endsection
