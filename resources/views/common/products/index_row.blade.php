<div class="product-row">
	<div class="product-image">
		<img src="{{ $product->display_image }}" alt="{{ $product->name }}" />
	</div>
	<div class="product-name">{{ $product->name ?? 'Anonymous Product' }}</div>
	<div class="product-sku">{{ $product->sku ?? 'PR-001' }}</div>
	<div class="product-category">{{ $product->category->name ?? 'No Category Name' }}</div>
	<div class="product-brand">{{ $product->brand->name ?? 'No Brand' }}</div>
	<div class="product-stock">{{ $product->total_stock }}</div>
	<div class="product-price">€{{ number_format($product->selling_price ?? 0, 2) }}</div>
	<div class="product-actions">
		<a href="{{ route('inventory.products.show', ['product' => $product->id]) }}" class="btn small view"><i class="ri-eye-line"></i> View</a>
		<a href="{{ route('inventory.products.edit', ['product' => $product->id]) }}" class="btn small edit"><i class="ri-pencil-line"></i> Edit</a>
		<a href="{{ route('inventory.products.destroy', ['product' => $product->id]) }}" class="btn small delete">Delete</a>
	</div>
</div>