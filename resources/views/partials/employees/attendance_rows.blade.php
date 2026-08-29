@if($attendances->isNotEmpty())
	<table class="data-table">
		<thead>
		<tr>
			<th>Date</th>
			<th>Check In</th>
			<th>Check Out</th>
			<th>Status</th>
			<th class="text-right">Actions</th>
		</tr>
		</thead>
		<tbody>
		@foreach($attendances as $attendance)
			@php
				$formattedDate = optional($attendance->work_date)->format('d-m-Y');
			@endphp
			<tr>
				<td>{{ optional($attendance->work_date)->format('d/m/Y') }}</td>
				<td>{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-' }}</td>
				<td>{{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-' }}</td>
				<td>
					<span
						class="badge badge-status-{{ strtolower($attendance->status->value ?? $attendance->status) }}">
						{{ $attendance->status->label() ?? $attendance->status }}
					</span>
				</td>
				<td class="actions-cell text-right">
					<div class="action-buttons-group">
						@can('attendance.view')
							<a href="{{ route('hr.attendances.show', ['employee' => $attendance->employee_id, 'date' => $formattedDate]) }}"
							   class="btn-action btn-view" title="View Details">
								<i class="fas fa-eye"></i>
							</a>
						@endcan

						@can('attendance.update')
							<a href="{{ route('hr.attendances.edit', ['employee' => $attendance->employee_id, 'date' => $formattedDate]) }}"
							   class="btn-action btn-edit" title="Edit Log">
								<i class="fas fa-pencil-alt"></i>
							</a>
						@endcan

						@can('attendance.delete')
							<form
								action="{{ route('hr.attendances.destroy', ['employee' => $attendance->employee_id, 'date' => $formattedDate]) }}"
								method="POST" class="inline-form"
								onsubmit="return confirm('Are you sure you want to delete this record?');">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-action btn-delete" title="Delete Log">
									<i class="fas fa-trash-alt"></i>
								</button>
							</form>
						@endcan
					</div>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<div class="pagination-container">
		{{ $attendances->appends(request()->query())->links('pagination::simple') }}
	</div>
@else
	<p class="empty-state-text">No attendance records logged matching your criteria.</p>
@endif
