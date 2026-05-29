<div class="location-card {{ count($location->items) > 0 ? 'is-occupied' : 'is-empty' }}">
	<div class="loc-card-header">
		<div class="loc-title">
			<span class="loc-code">{{ $location->code }}</span>
			<span class="loc-label">{{ $location->displayName }}</span>
		</div>
		<span class="status-dot"></span>
	</div>

	<div class="loc-card-body">
		@if(count($location->items) > 0)
			<div class="stored-items-stack">
				@foreach($location->items as $item)
					<div class="item-entry">
						<span class="qty-pill">{{ $item['quantity'] }}</span>
						<div class="item-details">
							<span class="item-name">{{ $item['product_name'] }}</span>
							@if($item['batch'])
								<span class="item-batch">#{{ $item['batch'] }}</span>
							@endif
						</div>
					</div>
				@endforeach
			</div>
		@else
			<div class="empty-state-msg">Ready for storage</div>
		@endif
	</div>
	<div class="loc-card-footer">
		<span class="coords-info">📍 {{ $location->coordinates }}</span>
	</div>
</div>