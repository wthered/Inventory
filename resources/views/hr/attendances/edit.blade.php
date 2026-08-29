@extends('templates.general')

@section('title', 'Edit Attendance Log for ' . request('date'))

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
				<p class="subtitle">Modify clock-in/out timestamps and attendance status for #{{ $employee->id }}
					on {{ request('date') }}</p>
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
		<form action="{{ route('hr.attendances.update', ['employee' => $employee, 'date' => $date]) }}" method="POST"
		      class="form-card">
			@csrf
			@method('PUT')

			<!-- Section: Attendance Details -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-user-clock"></i> Attendance Details</h2>

				<div class="form-grid">

					<!-- Employee Selection -->
					<div class="form-group full-width">
						<label for="employee_id" class="form-label required">Employee</label>
						<select name="employee_id" id="employee_id"
						        class="form-control @error('employee_id') is-invalid @enderror" required>
							<option value="">-- Select Employee --</option>
							@foreach($employeeGroups as $warehouseName => $people)
								<optgroup label="{{ $warehouseName }}">
									@foreach($people as $person)
										<option value="{{ $person->id }}"
											@selected(intval(old('employee_id', $employee->id)) === intval($person->id))>
											{{ $person->user?->account->full_name }} ({{ $person->employee_code }})
										</option>
									@endforeach
								</optgroup>
							@endforeach
						</select>
						@error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Attendance Date -->
					<div class="form-group">
						<label for="date" class="form-label required">Date</label>
						<input type="date" id="date" name="date" value="{{ old('date', $date) }}"
						       class="form-control @error('date') is-invalid @enderror" required>
						@error('date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Clock In Time -->
					<div class="form-group">
						<label for="clock_in" class="form-label">Clock In</label>
						<input type="time"
						       id="clock_in"
						       name="check_in"
						       step="1"
						       value="{{ old('check_in', $attendance->check_in->format('H:i:s')) }}"
						       class="form-control @error('check_in') is-invalid @enderror">
						@error('clock_in') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Clock Out Time -->
					<div class="form-group">
						<label for="clock_out" class="form-label">Clock Out</label>
						<input type="time"
						       id="clock_out"
						       name="check_out"
						       step="1"
						       value="{{ old('check_out', $attendance->check_out->format('H:i:s')) }}"
						       class="form-control @error('check_out') is-invalid @enderror">
						@error('clock_out') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Attendance Status -->
					<div class="form-group">
						<label for="status" class="form-label required">Status</label>
						<select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
						        required>
							<option value="">-- Select Status --</option>
							@foreach($statuses::cases() as $status)
								<option value="{{ $status->value }}"
									@selected(old('status', $attendance->status->value) === $status->value)>
									{{ $status->label() }}
								</option>
							@endforeach
						</select>
						@error('status') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Remarks / Notes -->
					<div class="form-group full-width">
						<label for="notes" class="form-label">Notes / Remarks</label>
						<textarea id="notes"
						          name="notes"
						          rows="3"
						          class="form-control @error('notes') is-invalid @enderror"
						          placeholder="Provide optional details or reasons for manually adjusting this record">{{ old('notes') }}</textarea>
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
