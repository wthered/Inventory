<!-- Create / Edit Leave Request Modal -->
<div class="modal-backdrop" id="leave-modal-backdrop" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="leave-modal-title"><i class="fas fa-plane-departure"></i> Request Leave</h3>
				<button type="button" class="modal-close" id="btn-close-leave-modal">&times;</button>
			</div>

			<form id="leave-modal-form" action="{{ route('hr.leaves.store') }}" method="POST">
				@csrf
				<input type="hidden" name="_method" id="leave-form-method" value="POST">
				<input type="hidden" name="employee_id" value="{{ $employee->id }}">

				<div class="modal-body">
					<div class="form-group">
						<label for="modal_leave_type" class="form-label required">Type</label>
						<select name="type" id="modal_leave_type" class="form-select" required>
							<option value="">Select Type</option>
							@foreach($types::cases() as $type)
								<option value="{{ $type->value }}">{{ $type->label() }}</option>
							@endforeach
						</select>
						<span class="error-text" id="error-modal_type"></span>
					</div>

					<div class="form-group">
						<label for="modal_leave_start_date" class="form-label required">Start Date</label>
						<input type="date" name="start_date" id="modal_leave_start_date" class="form-control"
						       min="{{ today()->format('Y-m-d') }}" value="{{ today()->format('Y-m-d') }}"
						       required
						>
						<span class="error-text" id="error-modal_start_date"></span>
					</div>

					<div class="form-group">
						<label for="modal_leave_end_date" class="form-label required">End Date</label>
						<input type="date" name="end_date" id="modal_leave_end_date" class="form-control"
						       max="{{ today()->addDay()->format('Y-m-d') }}"
						       value="{{ today()->addDay()->format('Y-m-d') }}"
						       required>
						<span class="error-text" id="error-modal_end_date"></span>
					</div>

					<div class="form-group">
						<label for="modal_leave_reason" class="form-label">Reason</label>
						<textarea name="reason" id="modal_leave_reason" class="form-control" rows="3"></textarea>
						<span class="error-text" id="error-modal_reason"></span>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn-secondary" id="btn-cancel-leave-modal">Cancel</button>
					<button type="submit" class="btn-primary">Save Request</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Create Attendance Record Modal -->
<div class="modal-backdrop" id="attendance-modal-backdrop" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"><i class="fas fa-clock"></i> Create Attendance Record</h3>
				<button type="button" class="modal-close" id="btn-close-attendance-modal">&times;</button>
			</div>

			<form id="attendance-modal-form" action="{{ route('hr.attendances.store') }}" method="POST">
				@csrf

				<div class="modal-body">
					<div class="form-group mb-3">
						<label for="modal_att_status" class="form-label required">Status</label>
						<select name="status" id="modal_att_status" class="form-select" required>
							@foreach($statuses::cases() as $status)
								<option value="{{ $status->value }}">{{ $status->label() }}</option>
							@endforeach
						</select>
						<span class="error-text" id="error-modal_att_status"></span>
					</div>

					<div class="form-group mb-3">
						<label for="modal_att_date" class="form-label required">Date</label>
						<input type="date" name="date" id="modal_att_date" class="form-control"
						       min="{{ $employee->hire_date->format('Y-m-d') }}"
						       max="{{ today()->format('Y-m-d') }}"
						       value="{{ old('date',today()->format('Y-m-d')) }}"
						       required>
						<span class="error-text" id="error-modal_att_date"></span>
					</div>

					<div class="form-group mb-3">
						<label for="modal_att_check_in" class="form-label">Check In Time</label>
						<input type="time" name="clock_in" id="modal_att_check_in" class="form-control" step="1">
						<span class="error-text" id="error-modal_check_in"></span>
					</div>

					<div class="form-group mb-3">
						<label for="modal_att_check_out" class="form-label">Check Out Time</label>
						<input type="time" name="clock_out" id="modal_att_check_out" class="form-control" step="1">
						<span class="error-text" id="error-modal_check_out"></span>
					</div>

					<div class="form-group mb-3">
						<label for="modal_att_notes" class="form-label">Notes</label>
						<textarea name="notes" id="modal_att_notes" class="form-control" rows="3"
						          placeholder="Optional notes..."></textarea>
						<span class="error-text" id="error-modal_notes"></span>
					</div>
				</div>

				<input type="hidden" value="{{ $employee->id }}" name="employee_id">

				<div class="modal-footer">
					<button type="button" class="btn-secondary" id="btn-cancel-attendance-modal">Cancel</button>
					<button type="submit" class="btn-primary">Create Attendance</button>
				</div>
			</form>
		</div>
	</div>
</div>
