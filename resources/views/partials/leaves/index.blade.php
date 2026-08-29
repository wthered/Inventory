<!-- Leave Requests Table -->
<div class="table-card">
	<div class="table-wrapper">
		<table class="data-table" id="leaves-table">
			<thead>
			<tr>
				<th>Employee</th>
				<th>Type</th>
				<th>Start Date</th>
				<th>End Date</th>
				<th>Days</th>
				<th>Status</th>
				<th class="text-right">Actions</th>
			</tr>
			</thead>
			<tbody>
			@forelse($leaveRequests as $leave)
				<tr>
					<td class="td-name">
						{{ $leave->employee->user->account->full_name }}
						<small class="text-muted d-block">{{ $leave->employee->employee_code }}</small>
					</td>
					<td>
						<span class="type-pill">
							{{ $leave->leave_type->label() ?? 'N/A' }}
						</span>
					</td>
					<td class="td-id">
						{{ $leave->start_date->format('M d, Y') }}
					</td>
					<td class="td-id">
						{{ $leave->end_date->format('M d, Y') }}
					</td>
					<td>
						<span class="font-mono">
							{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}
						</span>
					</td>
					<td>
						<span class="badge {{ $leave->status->badgeClass() }}">
							<i class="{{ $leave->status->icon() }}"></i> {{ $leave->status->label() }}
						</span>
					</td>
					<td class="actions-cell text-right">
						<div class="action-buttons-group">
							<a href="{{ route('hr.leaves.show', $leave->id) }}" class="btn-action btn-view"
							   title="View Request">
								<i class="fas fa-eye"></i>
							</a>
							@can('leave_request.update')
								@if($leave->status->value === 'pending')
									<a href="{{ route('hr.leaves.edit', $leave->id) }}" class="btn-action btn-edit"
									   title="Edit Request">
										<i class="fas fa-pencil-alt"></i>
									</a>
								@endif
							@endcan
							@can('leave_request.delete')
								<form action="{{ route('hr.leaves.destroy', $leave->id) }}"
								      method="POST" class="inline-form"
								      onsubmit="return confirm('Are you sure you want to delete this leave request?');">
									@csrf
									@method('DELETE')
									<button type="submit" class="btn-action btn-delete" title="Delete Request">
										<i class="fas fa-trash-alt"></i>
									</button>
								</form>
							@endcan
						</div>
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="7" class="table-empty-state">
						<div class="empty-state-container">
							<i class="fas fa-plane-slash empty-icon"></i>
							<p>No leave requests found matching the current criteria.</p>
						</div>
					</td>
				</tr>
			@endforelse

			</tbody>
		</table>
	</div>

	<!-- Pagination -->
	@if($leaveRequests->hasPages())
		<div class="table-pagination" id="pagination">
			{{ $leaveRequests->links('pagination::simple') }}
		</div>
	@endif
</div>
