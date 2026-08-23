@forelse($customers as $customer)
	<tr>
		<td class="font-mono">{{ $customer->code }}</td>
		<td>
			<div class="cell-primary font-weight-600">{{ $customer->name }}</div>
			@if($customer->company_name)
				<div class="cell-secondary">{{ $customer->company_name }}</div>
			@endif
		</td>
		<td>
			<div>{{ $customer->phone }}</div>
			@if($customer->email)
				<div class="cell-secondary">{{ $customer->email }}</div>
			@endif
		</td>
		<td>
			@if($customer->city?->name || $customer->country?->name)
				{{ implode(', ', array_filter([
					$customer->city?->name,
					$customer->city?->state,
					$customer->country?->name
				])) }}
			@else
				<span class="text-muted">—</span>
			@endif
		</td>
		<td>
			@if($customer->is_active)
				<span class="badge badge-success">Active</span>
			@else
				<span class="badge badge-secondary">Inactive</span>
			@endif
		</td>
		<td class="text-right actions-cell">
			<div class="action-buttons-group">
				@can('customer.view')
					<a href="{{ route('inventory.customers.show', $customer->id) }}" class="btn-action btn-show" title="View Details">
						<i class="fas fa-eye"></i>
					</a>
				@endcan
				@can('customer.update')
					<a href="{{ route('inventory.customers.edit', $customer->id) }}" class="btn-action btn-edit" title="Edit Customer">
						<i class="fas fa-edit"></i>
					</a>
				@endcan
				@can('customer.delete')
					<form action="{{ route('inventory.customers.destroy', $customer->id) }}" method="POST" class="inline-form" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον πελάτη;');">
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
		<td colspan="6" class="empty-state">
			<i class="fas fa-user-slash empty-icon"></i>
			<p>No customers found matching your criteria.</p>
		</td>
	</tr>
@endforelse
