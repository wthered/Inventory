@if($leaveRequests->isNotEmpty())
	<table class="data-table">
		<thead>
		<tr>
			<th>Type</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Days</th>
			<th>Status</th>
			<th>Reason</th>
		</tr>
		</thead>
		<tbody>
		@foreach($leaveRequests as $leave)
			<tr>
				<td>{{ $leave->leave_type->label() }}</td>
				<td>{{ optional($leave->start_date)->format('d/m/Y') }}</td>
				<td>{{ optional($leave->end_date)->format('d/m/Y') }}</td>
				<td>{{ $leave->total_days }}</td>
				<td>
					<span class="badge status-{{ strtolower($leave->status->value) }}">
						{{ $leave->status->label() }}
					</span>
				</td>
				<td>
					{{ $leave->reason }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<div class="pagination-container">
		{{ $leaveRequests->links('pagination::simple') }}
	</div>
@else
	<p class="empty-state-text">No leave requests logged matching your criteria.</p>
@endif
