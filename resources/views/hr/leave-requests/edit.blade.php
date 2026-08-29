@extends('templates.general')

@section('title', 'Edit Leave Request #' . $leaf->id)

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
				<p class="subtitle">Modify leave parameters or process status for Request #{{ $leaf->id }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.leaves.index') }}" class="btn-secondary">
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
		<form action="{{ route('hr.leaves.update', ['leaf' => $leaf->id]) }}" method="POST" class="form-card">
			@csrf
			@method('PUT')

			<!-- Section: Request Information -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-calendar-alt"></i> Leave Request Details</h2>

				<div class="form-grid">

					<!-- Employee Selection -->
					<div class="form-group full-width">
						<label for="employee_id" class="form-label required">Employee</label>
						<select name="employee_id" id="employee_id"
						        class="form-control @error('employee_id') is-invalid @enderror" required>
							<option value="" disabled>-- Select Employee --</option>
							@foreach($employees as $warehouseName => $group)
								<optgroup label="{{ $warehouseName }}">
									@foreach($group as $employee)
										<option
											value="{{ $employee->id }}" {{ old('employee_id', $leaf->employee_id) == $employee->id ? 'selected' : '' }}>
											{{ $employee->user?->account->full_name }} ({{ $employee->employee_code }})
										</option>
									@endforeach
								</optgroup>
							@endforeach
						</select>
						@error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Leave Type -->
					<div class="form-group">
						<label for="leave_type_id" class="form-label required">Leave Type</label>
						<select name="leave_type_id" id="leave_type_id"
						        class="form-control @error('leave_type_id') is-invalid @enderror" required>
							<option value="">-- Select Category --</option>
							@foreach($leaveTypes::cases() as $type)
								<option
									value="{{ $type->value }}" {{ old('leave_type_id', $leaf->leave_type->value) == $type->value ? 'selected' : '' }}>
									{{ $type->label() }}
								</option>
							@endforeach
						</select>
						@error('leave_type_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Status -->
					<div class="form-group">
						<label for="status" class="form-label required">Status</label>
						<select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
						        required>
							@foreach($leaveStatus::cases() as $status)
								<option
									value="{{ $status->value }}" {{ old('status', $leaf->status) === $status->value ? 'selected' : '' }}>
									{{ $status->label() }}
								</option>
							@endforeach
						</select>
						@error('status') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Start Date -->
					<div class="form-group">
						<label for="start_date" class="form-label required">Start Date</label>
						<input type="date"
						       id="start_date"
						       name="start_date"
						       value="{{ old('start_date', $leaf->start_date->format('Y-m-d')) }}"
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
						       value="{{ old('end_date', $leaf->end_date->format('Y-m-d')) }}"
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
						          placeholder="Details provided by employee">{{ old('reason', $leaf->reason) }}</textarea>
						@error('reason') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Rejection Reason (Conditional Context) -->
					<div class="form-group full-width">
						<label for="rejection_reason" class="form-label">Rejection Reason (If Applicable)</label>
						<textarea id="rejection_reason"
						          name="rejection_reason"
						          rows="2"
						          class="form-control @error('rejection_reason') is-invalid @enderror"
						          placeholder="State the reason if rejecting this request">{{ old('rejection_reason', $leaf->rejection_reason) }}</textarea>
						@error('rejection_reason') <span class="form-error">{{ $message }}</span> @enderror
					</div>

				</div>
			</div>

			<!-- Form Action Footer -->
			<div class="form-actions">
				<a href="{{ route('hr.leaves.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-save"></i> Save Changes
				</button>
			</div>
		</form>

	</div>
@endsection
