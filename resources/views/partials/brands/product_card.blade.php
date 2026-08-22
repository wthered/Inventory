<a href="#"
   class="product-autocomplete-item"
   data-id="{{ $product->id }}"
   data-name="{{ $product->name }}"
   data-sku="{{ $product->sku }}"
   data-price="{{ $product->selling_price }}">

    <div class="d-flex flex-column">
        <strong class="product-title">{{ $product->name }}</strong>
        <small class="text-muted">
            SKU: <span class="font-numeric">{{ $product->sku ?? 'N/A' }}</span>
        </small>
    </div>

    <div class="product-meta text-end">
        <span class="badge bg-primary rounded-pill font-numeric fs-6">
            €{{ number_format($product->selling_price, 2, ',', '.') }}
        </span>
    </div>
</a>