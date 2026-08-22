<tr>
	<td>
		<strong>
			<a href="{{ route('inventory.sales.show', $sale->id) }}" class="order-link">
				{{ $sale->order_number ?? '#' . $sale->id }}
			</a>
		</strong>
	</td>

	{{-- Customer Name Column --}}
	<td>{{ $sale->customer->name ?? 'N/A' }}</td>

	{{-- Fixed: Displaying assigned execution facility warehouse location name --}}
	<td>{{ $sale->warehouse->name ?? 'N/A' }}</td>

	{{-- Fixed: Pointing explicitly to your casted order_date model property --}}
	<td>{{ $sale->order_date ? $sale->order_date->format('Y-m-d') : 'N/A' }}</td>

	<td>
		{{-- Fixed: Resolving label methods safely through casted status Enum instance --}}
		@if($sale->status_id)
			<span class="badge"
			      style="background-color: {{ $sale->status_id->color() }}26; color: {{ $sale->status_id->color() }};">
                                        {{ $sale->status_id->label() }}
                                    </span>
		@else
			<span class="badge text-muted">Unknown</span>
		@endif
	</td>

	<td>
		{{-- Payment Status Column --}}
		@if($sale->payment_status_id)
			<span class="badge"
			      style="background-color: {{ $sale->payment_status_id->hexColor() }}26; color: {{ $sale->payment_status_id->hexColor() }};">
                                        {{ $sale->payment_status_id->label() }}
                                    </span>
		@else
			<span class="badge text-muted">Unknown</span>
		@endif
	</td>

	<td class="text-right table-actions">
		<a href="{{ route('inventory.sales.show', $sale->id) }}" class="btn-icon"
		   title="View Details">
			<i class="fas fa-eye"></i>
		</a>

		{{-- Fixed: Evaluating custom eligibility states on casted status values safely --}}
		@if($sale->status_id && method_exists($sale->status_id, 'isEditable') && $sale->status_id->isEditable())
			<a href="{{ route('inventory.sales.edit', $sale->id) }}"
			   class="btn-icon edit-btn" title="Edit">
				<i class="fas fa-edit"></i>
			</a>
			<form action="{{ route('inventory.sales.destroy', $sale->id) }}" method="POST"
			      class="inline-form delete-form">
				@csrf
				@method('DELETE')
				<button type="submit" class="btn-icon delete-btn" title="Cancel/Delete"
				        onclick="return confirm('Are you sure you want to cancel this sales order?')">
					<i class="fas fa-trash-alt"></i>
				</button>
			</form>
		@endif
	</td>
</tr>