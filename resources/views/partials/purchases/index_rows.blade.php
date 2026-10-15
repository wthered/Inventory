<table class="data-table">
	<thead>
	<tr>
		<th>Order Number</th>
		<th>Supplier</th>
		<th>Order Date</th>
		<th>Expected Date</th>
		<th>Total Cost</th>
		<th>Status</th>
		<th class="text-right">Actions</th>
	</tr>
	</thead>
	<tbody>
	@forelse($orders as $order)
		<tr>
			<td>
				<strong>
					<a href="{{ route('inventory.purchases.show', $order->id) }}" class="order-link">
						{{ $order->order_number }}
					</a>
				</strong>
			</td>
			<td>{{ $order->supplier->name ?? 'N/A' }}</td>
			<td>{{ $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A' }}</td>
			<td>{{ $order->expected_date ? $order->expected_date->format('Y-m-d') : 'N/A' }}</td>
			<td>${{ number_format($order->grand_total, 2) }}</td>
			<td>
                                <span class="badge"
                                      style="background-color: {{ $order->status_id->color() }}26; color: {{ $order->status_id->color() }};">
                                    {{ $order->status_id->label() }}
                                </span>
			</td>
			<td class="text-right table-actions">
				<a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn-icon"
				   title="View Details">
					<i class="fas fa-eye"></i>
				</a>
				{{-- Matches backing value checks against status Enum --}}
				@if($order->isEditable())
					<a href="{{ route('inventory.purchases.edit', $order->id) }}"
					   class="btn-icon edit-btn" title="Edit">
						<i class="fas fa-edit"></i>
					</a>
					<form action="{{ route('inventory.purchases.destroy', $order->id) }}" method="POST"
					      class="inline-form delete-form">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn-icon delete-btn" title="Delete"
						        onclick="return confirm('Are you sure you want to cancel this purchase order?')">
							<i class="fas fa-trash-alt"></i>
						</button>
					</form>
				@endif
			</td>
		</tr>
	@empty
		<tr>
			<td colspan="7" class="text-center no-data">
				<i class="fas fa-shopping-cart fa-2x"></i>
				<p>No purchase orders found matching your criteria.</p>
			</td>
		</tr>
	@endforelse
	</tbody>
</table>
