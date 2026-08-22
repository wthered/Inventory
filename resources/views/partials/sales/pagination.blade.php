@if($sales->hasPages())
	<div class="pagination-footer">
		<div class="pagination-info">
			Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} entries
		</div>
		<div class="pagination-links">
			{{ $sales->withQueryString()->links('pagination::simple') }}
		</div>
	</div>
@endif