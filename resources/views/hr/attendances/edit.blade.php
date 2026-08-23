@extends('templates.general')

@section('title', 'Edit Attendance Log')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/attendances/edit.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-clock icon-accent"></i> Edit Attendance Log
				</h1>
				<p class="subtitle">Modify clock-in/out timestamps and attendance status for #{{ $attendance->id }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.attendances.index') }}" class="btn-secondary">
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
		<form action="{{ route('hr.attendances.update', $attendance->id) }}" method="POST" class="form-card">
			@csrf
			@method('PUT')

			<!-- Section: Attendance Details -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-user-clock"></i> Attendance Details</h2>

				<div class="form-grid">

					<!-- Employee Selection -->
					<div class="form-group full-width">
						<label for="employee_id" class="form-label required">Employee</label>
						<select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
							<option value="">-- Select Employee --</option>
							@foreach($employees as $employee)
								<option value="{{ $employee->id }}" {{ old('employee_id', $attendance->employee_id) == $employee->id ? 'selected' : '' }}>
									{{ $employee->last_name }} {{ $employee->first_name }} ({{ $employee->employee_code }})
								</option>
							@endforeach
						</select>
						@error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Attendance Date -->
					<div class="form-group">
						<label for="date" class="form-label required">Date</label>
						<input type="date"
						       id="date"
						       name="date"
						       value="{{ old('date', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')) }}"
						       class="form-control @error('date') is-invalid @enderror"
						       required>
						@error('date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Status Selection -->
					<div class="form-group">
						<label for="status" class="form-label required">Status</label>
						<select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
							<option value="present" {{ old('status', $attendance->status) === 'present' ? 'selected' : '' }}>Present</option>
							<option value="late" {{ old('status', $attendance->status) === 'late' ? 'selected' : '' }}>Late</option>
							<option value="absent" {{ old('status', $attendance->status) === 'absent' ? 'selected' : '' }}>Absent</option>
							<option value="half_day" {{ old('status', $attendance->status) === 'half_day' ? 'selected' : '' }}>Half Day</option>
						</select>
						@error('status') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Clock In Time -->
					<div class="form-group">
						<label for="clock_in" class="form-label">Clock In</label>
						<input type="time"
						       id="clock_in"
						       name="clock_in"
						       value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
						       class="form-control @error('clock_in') is-invalid @enderror">
						@error('clock_in') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Clock Out Time -->
					<div class="form-group">
						<label for="clock_out" class="form-label">Clock Out</label>
						<input type="time"
						       id="clock_out"
						       name="clock_out"
						       value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
						       class="form-control @error('clock_out') is-invalid @enderror">
						@error('clock_out') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Remarks / Notes -->
					<div class="form-group full-width">
						<label for="notes" class="form-label">Notes / Remarks</label>
						<textarea id="notes"
						          name="notes"
						          rows="3"
						          class="form-control @error('notes') is-invalid @enderror"
						          placeholder="Provide optional details or reasons for manually adjusting this record">{{ old('notes', $attendance->notes) }}</textarea>
						@error('notes') <span class="form-error">{{ $message }}</span> @enderror
					</div>

				</div>
			</div>

			<!-- Actions Bar -->
			<div class="form-actions">
				<a href="{{ route('hr.attendances.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-save"></i> Update Log
				</button>
			</div>
		</form>

	</div>
@endsection
