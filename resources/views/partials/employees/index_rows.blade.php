@forelse($employees as $employee)
	<tr>
		<td class="font-mono td-id">#{{ $employee->employee_code ?? $employee->id }}</td>
		<td>
			<div class="td-name">
				{{ $employee->user->account->full_name }}
			</div>
			@if($employee->job_title)
				<div class="td-description">{{ $employee->job_title }}</div>
			@endif
		</td>
		<td>
			<div>{{ $employee->user->account->phone }}</div>
			@if($employee->user->email)
				<div class="td-description">{{ $employee->email }}</div>
			@endif
		</td>
		<td>
			@if($employee->department)
				<span class="badge-count">{{ $employee->department->name }}</span>
			@else
				<span class="td-description">—</span>
			@endif
		</td>
		<td>
			@if($employee->user->account->is_active)
				<span class="badge-count"
				      style="color: var(--color-accent); border-color: var(--color-accent);">Active</span>
			@else
				<span class="badge-count" style="color: var(--color-text-muted);">Inactive</span>
			@endif
		</td>
		<td class="text-right actions-cell">
			<div class="action-buttons-group">
				@can('employee.view')
					<a href="{{ route('hr.employees.show', $employee->id) }}"
					   class="btn-action btn-show"
					   title="View Details">
						<i class="fas fa-eye"></i>
					</a>
				@endcan

				@can('employee.update')
					<a href="{{ route('hr.employees.edit', $employee->id) }}"
					   class="btn-action btn-edit"
					   title="Edit Employee">
						<i class="fas fa-edit"></i>
					</a>
				@endcan

				@can('employee.delete')
					<form action="{{ route('hr.employees.destroy', $employee->id) }}"
					      method="POST"
					      class="inline-form"
					      onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον υπάλληλο;');">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn-action btn-delete" title="Διαγραφή">
							<i class="fas fa-trash-alt"></i>
						</button>
					</form>
				@endcan
			</div>
		</td>
	</tr>
@empty
	<tr>
		<td colspan="6" class="table-empty-state">
			<i class="fas fa-user-slash empty-icon"></i>
			<p>No employees found matching your filter criteria.</p>
		</td>
	</tr>
@endforelse
