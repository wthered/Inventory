@extends('templates.general')

@section('title', 'Product #' . $product->id)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/products/show.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/products/transfer_modal.css') }}"/>
@endsection

@section('content')
    <div class="product-container">

        <!-- Product Header -->
        <div class="product-header">
            <div class="breadcrumb">
                <a href="{{ route('inventory.products.index') }}">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
            <div class="product-actions">
                <a href="{{ route('inventory.products.edit', ['product' => $product->id]) }}"
                   class="btn-icon"
                   title="Edit Product">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="#" class="btn-icon" title="Print Barcode">
                    <i class="fas fa-barcode"></i>
                </a>

                <!-- Options Dropdown -->
                <div class="dropdown">
                    <a href="#" class="btn-icon dropdown-toggle" id="moreOptionsButton" title="More Options">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="moreOptionsButton">
                        <a href="{{ route('inventory.product.clone', ['product' => $product->id]) }}"
                           class="dropdown-item">
                            <i class="fas fa-copy"></i> Duplicate Product
                        </a>
                        <a href="{{ route('inventory.product.history', ['product' => $product->id]) }}"
                           class="dropdown-item">
                            <i class="fas fa-history"></i> View History
                        </a>
                        <a href="#" class="dropdown-item action-archive">
                            <i class="fas fa-archive"></i> Archive
                        </a>
                        <a href="#" class="dropdown-item action-delete danger-text">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Main Grid (Two Columns Only) -->
        <div class="product-main">
            <!-- Left Column: Media Gallery -->
            <div class="product-images">
                <div class="image-main">
                    <img src="{{ $active_image['image_location'] }}" alt="Product" id="mainImage">
                    <div class="image-badges">
                        <span class="badge badge-success">In Stock</span>
                        <span class="badge badge-warning">Low Stock</span>
                    </div>
                </div>
                <div class="image-thumbnails">
                    @foreach($product->images as $image)
                        <img src="{{ $image->image_location }}"
                             alt="Thumbnail"
                             class="thumbnail {{ $image->is_default ? 'active' : '' }}">
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Primary Details -->
            <div class="product-details">
                <div class="product-title-section">
                    <h1 class="product-title">{{ $product->name }}</h1>

                    <!-- Metadata Grid -->
                    <div class="product-meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value">{{ $product->sku }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Barcode:</span>
                            <span class="meta-value">{{ $product->barcode }}</span>
                        </div>
                    </div>

                    <!-- Pricing Info -->
                    <div class="product-pricing">
                        <div class="price-group">
                            <label>Cost Price</label>
                            <span class="price price-cost">&euro;{{ $product->cost_price }}</span>
                        </div>
                        <div class="price-group">
                            <label>Selling Price</label>
                            <span class="price price-selling">&euro;{{ $product->selling_price }}</span>
                        </div>
                        <div class="price-group">
                            <label>Profit Margin</label>
                            <span class="price price-profit">&euro;{{ $profit['absolute'] }} ({{ $profit['relative'] }}%)</span>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="product-info-grid">
                        <div class="info-item">
                            <i class="fas fa-tag"></i>
                            <div>
                                <label>Category</label>
                                @if(!is_null($categories['parent']))
                                    <span>{{ $categories['parent']->name }} &gt; {{ $categories['child']->name }}</span>
                                @else
                                    <span>{{ $categories['child']->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-certificate"></i>
                            <div>
                                <label>Brand</label>
                                <span>{{ $product->brand['name'] }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-box"></i>
                            <div>
                                <label>Unit</label>
                                <span>{{ $product->unit->name }} ({{ $product->unit->value }})</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-weight"></i>
                            <div>
                                <label>Weight</label>
                                <span>{{ $product->specifications['weight'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="product-description">
                        <h3>Description</h3>
                        <p>{{ $product->description }}</p>
                    </div>

                    <!-- Dynamic Specifications -->
                    <div class="product-specifications">
                        <h3>Specifications</h3>
                        <div class="spec-grid">
                            @if(!empty($product->specifications))
                                @foreach($product->specifications as $name => $specification)
                                    <div class="spec-item">
                                        <span class="spec-label">{{ $name }}</span>
                                        <span class="spec-value">{{ $specification }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Status Summary Cards -->
        <div class="section-container">
            <h2 class="section-title">
                <i class="fas fa-warehouse"></i> Inventory Status
            </h2>
            <div class="inventory-cards">
                <div class="inventory-card">
                    <div class="card-icon total"><i class="fas fa-boxes"></i></div>
                    <div class="card-content">
                        <label>Total Stock</label>
                        <span class="card-value">{{ number_format($product->current_stock, 0, '', '.') }} pcs</span>
                    </div>
                </div>
                <div class="inventory-card">
                    <div class="card-icon available"><i class="fas fa-check-circle"></i></div>
                    <div class="card-content">
                        <label>Available</label>
                        <span class="card-value">{{ number_format($stock['available'], 0, '', '.') }} pcs</span>
                    </div>
                </div>
                <div class="inventory-card">
                    <div class="card-icon reserved"><i class="fas fa-lock"></i></div>
                    <div class="card-content">
                        <label>Reserved</label>
                        <span class="card-value">{{ number_format($stock['reserved'], 0, '', '.') }} pcs</span>
                    </div>
                </div>
                <div class="inventory-card">
                    <div class="card-icon reorder"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-content">
                        <label>Reorder Point</label>
                        <span class="card-value">{{ number_format($product->reorder_point, 0, '', '.') }} pcs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Levels Table -->
        <div class="section-container">
            <h2 class="section-title">
                <i class="fas fa-map-marker-alt"></i> Stock by Warehouse
            </h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Warehouse Name</th>
                        <th>Warehouse Address</th>
                        <th>Warehouse Location</th>
                        <th>Available</th>
                        <th>Reserved</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($product->inventories as $inventory)
                        <tr>
                            <td>
                                <div class="warehouse-info">
                                    <i class="fas fa-warehouse"></i>
                                    <a href="{{ route('inventory.warehouses.show', ['warehouse' => $inventory['warehouse_id']]) }}"
                                       class="warehouse-link">
                                        {{ $inventory['warehouse']['name'] }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <p>{{ $inventory['warehouse']['city'] }}</p>
                                <p>{{ $inventory['warehouse']['address'] }}</p>
                            </td>
                            <td title="{{ $inventory['location']['description'] }}">{{ $inventory['location']['name'] }}</td>
                            <td title="Batch Number {{ $inventory->batch_number }}">{{ $inventory['available_quantity'] }} {{ $product->unit }}</td>
                            <td>{{ $inventory['reserved_quantity'] }} {{ $product->unit }}</td>
                            <td><strong>{{ $inventory['quantity'] }} {{ $product->unit }}</strong></td>
                            <td>
                                <span class="status-badge status-{{ $statuses[$inventory->warehouse_id][$inventory->location_id]['tier_label'] }}"
                                      title="{{ $statuses[$inventory->warehouse_id][$inventory->location_id]['suggested_action'] }}">{{ $statuses[$inventory->warehouse_id][$inventory->location_id]['tier_label'] }}</span>
                            </td>
                            <td class="actions">
                                <button class="btn-sm btn-primary transfer-btn"
                                        title="Transfer"
                                        data-product="{{ $product->id }}"
                                        data-warehouse="{{ $inventory['warehouse_id'] }}"
                                        data-location="{{ $inventory['location_id'] }}">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>

                                <button class="btn-sm btn-secondary btn-adjust"
                                        title="Adjust"
                                        data-product="{{ $product->id }}"
                                        data-warehouse="{{ $inventory->warehouse_id }}"
                                        data-location="{{ $inventory->location_id }}"
                                        data-inventory="{{ $inventory->id }}"
                                        data-current-qty="{{ $inventory->quantity }}"
                                        data-max-quantity="{{ $product->max_stock_level }}">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="section-container">
            <h2 class="section-title">
                <i class="fas fa-history"></i> Recent Transactions
            </h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>User</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($product->transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                    <span class="transaction-type type-{{ $transaction->type->value ?? $transaction->type }}">
                                        {{ $transaction->type->value ?? $transaction->type }}
                                    </span>
                            </td>
                            <td>
                                <div class="reference-wrapper">
                                        <span class="ref-pill {{ $transaction->reference_display['class'] }}">
                                            <i class="fas {{ $transaction->reference_display['icon'] }}"></i>
                                            {{ $transaction->reference_display['label'] }}
                                        </span>
                                </div>
                            </td>
                            <td>
                                <div class="warehouse-info">
                                    @if(($transaction->type->value ?? $transaction->type) === 'transfer')
                                        <span class="text-muted"><i class="fas fa-exchange-alt"></i> {{ $transaction->notes }}</span>
                                    @else
                                        <a href="{{ route('inventory.warehouses.show', ['warehouse' => $transaction->warehouse_id]) }}"
                                           class="warehouse-link">
                                            <i class="fas fa-warehouse"></i> {{ $transaction->warehouse->name ?? 'Unknown' }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="{{ in_array($transaction->type->value ?? $transaction->type, ['in', 'return']) ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->type->value === 'out' ? '-' : '+' }}{{ number_format($transaction->quantity, 2) }} {{ $product->unit ?? 'pcs' }}
                            </td>
                            <td>
                                <div class="user-info-cell">
                                    @if($transaction->creator && $transaction->creator->account && $transaction->creator->account->avatar)
                                        <img src="{{ $transaction->creator->account->avatar }}"
                                             alt="{{ $transaction->creator->account->first_name }}"
                                             class="user-avatar-sm">
                                    @else
                                        <div class="user-avatar-placeholder">
                                            {{ strtoupper(substr($transaction->creator->account->first_name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="user-name-stack">
                                            <span class="user-name-full">
                                                {{ $transaction->creator->account->first_name ?? 'System' }}
                                                {{ $transaction->creator->account->last_name ?? '' }}
                                            </span>
                                        <span class="user-role-label">{{ $transaction->creator->main_role_name }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Suppliers Cards -->
        <div class="section-container">
            <h2 class="section-title">
                <i class="fas fa-truck"></i> Suppliers
            </h2>
            <div class="suppliers-grid">
                @foreach($product->suppliers as $supplier)
                    <div class="supplier-card">
                        <div class="supplier-header">
                            <h4>{{ $supplier['company_name'] }}</h4>
                            @if(!empty($supplier->pivot['is_preferred']))
                                <span class="badge badge-primary">Preferred</span>
                            @endif
                        </div>
                        <div class="supplier-details">
                            <div class="detail-row">
                                <span class="label">Supplier SKU:</span>
                                <span class="value">{{ $supplier['code'] }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Price:</span>
                                <span class="value">&euro;{{ number_format($supplier['pivot']['price'], 2) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Lead Time:</span>
                                <span class="value">{{ $supplier['pivot']['lead_time_days'] }} days</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">MOQ:</span>
                                <span class="value">{{ $supplier['pivot']['moq'] }} units</span>
                            </div>
                        </div>
                        <a href="{{ route('inventory.suppliers.show', ['supplier' => $supplier['id']]) }}"
                           class="btn-secondary btn-full text-center"
                           style="display: block; text-decoration: none;">
                            <i class="fas fa-external-link-alt"></i> Contact Supplier Page
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Product History Section -->
        <div class="section-container">
            <h2 class="section-title">
                <i class="fas fa-history"></i> Product History
            </h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($product->history as $entry)
                        <tr>
                            <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $entry->user->name ?? 'System' }}</td>
                            <td>
                                <span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($entry->action)) }}</span>
                            </td>
                            <td>
                                @if($entry->details)
                                    <small class="text-muted">
                                        @foreach($entry->details as $key => $value)
                                            <strong>{{ ucfirst($key) }}
                                                :</strong> {{ is_array($value) ? json_encode($value) : $value }}
                                            @if(!$loop->last)
                                                |
                                            @endif
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">No extra details</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No history records found for this product.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div id="transferModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Transfer Product</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="form-group">
                            <label for="productInfo">Product</label>
                            <input type="text" id="productInfo" class="form-control" readonly>
                            <input type="hidden" id="productId">
                        </div>

                        <!-- Source Warehouse & Location -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sourceWarehouse">Source Warehouse</label>
                                <select id="sourceWarehouse" class="form-control" required>
                                    <option value="">Select Source Warehouse</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sourceZone">Zone</label>
                                <select id="sourceZone" class="form-control" required>
                                    <option value="">Select Zone</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sourceAisle">Aisle</label>
                                <select id="sourceAisle" class="form-control" required>
                                    <option value="">Select Aisle</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sourceRack">Rack</label>
                                <select id="sourceRack" class="form-control" required>
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sourceShelf">Shelf</label>
                                <select id="sourceShelf" class="form-control" required>
                                    <option value="">Select Shelf</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sourceBin">Bin/Position</label>
                                <select id="sourceBin" class="form-control" required>
                                    <option value="">Select Bin</option>
                                </select>
                            </div>
                        </div>

                        <!-- Destination Warehouse & Location -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="destinationWarehouse">Target Warehouse</label>
                                <select id="destinationWarehouse" class="form-control" required>
                                    <option value="">Select Destination Warehouse</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="destinationZone">Zone</label>
                                <select id="destinationZone" class="form-control" required>
                                    <option value="">Select Zone</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="destinationAisle">Aisle</label>
                                <select id="destinationAisle" class="form-control" required>
                                    <option value="">Select Aisle</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="destinationRack">Rack</label>
                                <select id="destinationRack" class="form-control" required>
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="destinationShelf">Shelf</label>
                                <select id="destinationShelf" class="form-control" required>
                                    <option value="">Select Shelf</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="destinationBin">Bin/Position</label>
                                <select id="destinationBin" class="form-control" required>
                                    <option value="">Select Bin</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="transferQuantity">Quantity to Transfer</label>
                            <div class="quantity-controls">
                                <input type="number" id="transferQuantity" class="form-control" min="1" value="1"
                                       required>
                                <div class="available-quantity">
                                    Available: <span id="availableQuantity">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="transferNotes">Transfer Notes (Optional)</label>
                            <textarea id="transferNotes" class="form-control" rows="3"
                                      placeholder="Reason for transfer, special handling instructions..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="cancelTransfer">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="confirmTransfer">Transfer Products
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div id="adjustmentModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Adjust Stock</h2>
                    <span class="close">&times;</span>
                </div>

                <form id="adjustmentForm" action="#" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" id="adjustProduct">
                    <input type="hidden" name="location_id" id="adjustLocation">
                    <input type="hidden" name="inventory_id" id="adjustInventory">

                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="adjustProductName">Product</label>
                                <input type="text" class="form-control" id="adjustProductName" readonly>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" class="form-control" id="adjustLocationName" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="adjustCurrentQty">Current Quantity</label>
                            <div class="quantity-controls">
                                <input type="number" class="form-control" id="adjustCurrentQty" readonly
                                       style="background-color: var(--color-bg-muted);">
                                <div class="available-quantity" id="adjustStockStatus"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="adjustmentType">Adjustment Type *</label>
                                <div class="adjustment-type-selector" id="adjustmentType" style="margin-top: 0.25rem;">
                                    <div class="type-radio-group">
                                        <input type="radio" id="typeIncrease" name="type" value="increase" checked
                                               class="type-radio">
                                        <label for="typeIncrease" class="type-label increase">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Increase Stock</span>
                                        </label>

                                        <input type="radio" id="typeDecrease" name="type" value="decrease"
                                               class="type-radio">
                                        <label for="typeDecrease" class="type-label decrease">
                                            <i class="fas fa-minus-circle"></i>
                                            <span>Decrease Stock</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="adjustQuantity">Quantity *</label>
                                <div class="quantity-controls">
                                    <input type="number"
                                           name="quantity"
                                           id="adjustQuantity"
                                           min="1"
                                           max="{{ !empty($product->max_stock_level) ? $product->max_stock_level - $product->current_stock : $product->current_stock }}"
                                           value="1"
                                           required
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="adjustmentReason">
                            <label for="adjustReason">Reason *</label>
                            <select name="reason" id="adjustReason" class="form-control" required>
                                @foreach($reasons as $group => $options)
                                    <optgroup label="{{ $group }}">
                                        @foreach($options as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="adjustNotes">Notes</label>
                            <textarea name="notes" id="adjustNotes" rows="3" class="form-control"
                                      placeholder="Additional details about this adjustment..."></textarea>
                        </div>

                        <div class="location-section">
                            <h3>Adjustment Preview</h3>
                            <div class="preview-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="preview-item">
                                    <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 4px;">
                                        Current
                                    </div>
                                    <div id="previewCurrent"
                                         style="font-size: 24px; font-weight: 600; color: var(--color-text-primary);">0
                                    </div>
                                </div>
                                <div class="preview-item">
                                    <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 4px;">
                                        Change
                                    </div>
                                    <div id="previewChange"
                                         style="font-size: 24px; font-weight: 600; color: var(--color-status-success);">
                                        +0
                                    </div>
                                </div>
                                <div class="preview-item" style="grid-column: span 2;">
                                    <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 4px;">
                                        New Quantity
                                    </div>
                                    <div id="previewNew"
                                         style="font-size: 32px; font-weight: 700; color: var(--color-brand-primary);">0
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="errors">&nbsp;</div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="adjustCancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary adjustment-btn">
                            <i class="fas fa-check"></i> Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script type="application/javascript" src="{{ asset('js/products/show.js') }}"></script>
    <script type="application/javascript" src="{{ asset('js/products/transfer_modal.js') }}"></script>
    <script type="application/javascript" src="{{ asset('js/products/adjustment_modal.js') }}"></script>
@endsection