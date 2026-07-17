@extends('templates.general')

@section('title', 'Edit Purchase Order #' . $order->po_number)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/purchases/edit.css') }}"/>
@endsection

@section('content')
    <div class="main-container">
        <!-- Header Block -->
        <div class="page-header">
            <div class="header-title">
                <div class="title-with-badge">
                    <h1>Edit Purchase Order {{ $order->po_number }}</h1>
                    <span class="badge badge-{{ mb_strtolower($order->status_id->name) }}">
                        {{ $order->status_id->label() }}
                    </span>
                </div>
                <p class="subtitle">Modifying order created by {{ $order->creator->name ?? 'System' }}
                    on {{ $order->created_at->format('Y-m-d H:i') }}</p>
            </div>

            <div class="header-actions">
                <a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Order
                </a>
                <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success margin-bottom-lg" style="display: flex; align-items: center; gap: 10px; background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <form action="{{ route('inventory.purchases.update', $order->id) }}" method="POST" id="edit-purchase-form">
            @csrf
            @method('PUT')

            <!-- Upper Form Grid -->
            <div class="form-grid">
                <!-- Primary Procurement Details -->
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Procurement Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="supplier_id" class="form-label">Supplier <span
                                        class="text-error">*</span></label>
                            <select name="supplier_id" id="supplier_id"
                                    class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $order->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="warehouse_id" class="form-label">Deliver To (Warehouse) <span
                                        class="text-error">*</span></label>
                            <select name="warehouse_id" id="warehouse_id"
                                    class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $order->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Schedule & Status -->
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Dates & Scheduling</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="order_date" class="form-label">Order Date <span
                                        class="text-error">*</span></label>
                            <input type="date" name="order_date" id="order_date"
                                   class="form-control @error('order_date') is-invalid @enderror"
                                   value="{{ old('order_date', $order->order_date ? $order->order_date->format('Y-m-d') : '') }}"
                                   required>
                            @error('order_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="expected_date" class="form-label">Expected Delivery Date</label>
                            <input type="date" name="expected_date" id="expected_date"
                                   class="form-control @error('expected_date') is-invalid @enderror"
                                   value="{{ old('expected_date', $order->expected_date ? $order->expected_date->format('Y-m-d') : '') }}">
                            @error('expected_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card for Line Items -->
            <div class="table-card margin-top-lg">
                <div class="card-header border-bottom d-flex-between">
                    <h3><i class="fas fa-boxes"></i> Line Items</h3>
                    <button type="button" class="btn btn-secondary btn-sm" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add Line Item
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="items-table">
                        <thead>
                        <tr>
                            <th style="min-width: 280px;">Product Details <span class="text-error">*</span></th>
                            <th style="width: 140px;">Batch Reference</th>
                            <th class="text-center" style="width: 110px;">Qty Ordered <span class="text-error">*</span>
                            </th>
                            <th class="text-right" style="width: 160px;">Unit Cost ($) <span class="text-error">*</span>
                            </th>
                            <th class="text-center" style="width: 130px;">Discount %</th>
                            <th class="text-right" style="width: 160px;">Total</th>
                            <th class="text-center" style="width: 70px;">Action</th>
                        </tr>
                        </thead>
                        <tbody id="items-tbody">
                        @forelse(old('items', $order->items) as $index => $item)
                            <tr class="item-row" data-index="{{ $index }}">
                                {{-- Column 1: Product Details (Cascading selectors) --}}
                                <td>
                                    <div class="cascade-selectors">
                                        {{-- Category Selection --}}
                                        <div class="form-group margin-bottom-xs">
                                            <select name="items[{{ $index }}][category_id]"
                                                    class="form-select category-select @error("items.{$index}.category_id") is-invalid @enderror"
                                                    required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    @php
                                                        $selectedCategory = old("items.{$index}.category_id", is_object($item) ? ($item->product->category_id ?? null) : ($item['category_id'] ?? null));
                                                    @endphp
                                                    <option value="{{ $category->id }}" {{ $selectedCategory == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("items.{$index}.category_id")
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Brand Selection --}}
                                        <div class="form-group margin-bottom-xs">
                                            <select name="items[{{ $index }}][brand_id]"
                                                    class="form-select brand-select @error("items.{$index}.brand_id") is-invalid @enderror"
                                                    {{ $item->product->category->brands->isEmpty() ? 'disabled' : '' }} required>
                                                <option value="">
                                                    {{ $selectedCategory ? 'Select Brand' : 'Select Category first' }}
                                                </option>
                                                @foreach($item->product->category->brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ $item->product->brand_id == $brand->id ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("items.{$index}.brand_id")
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Product Selection --}}
                                        <div class="form-group">
                                            <select name="items[{{ $index }}][product_id]"
                                                    class="form-select product-select @error("items.{$index}.product_id") is-invalid @enderror"
                                                    {{ empty($item->product_id) ? 'disabled' : '' }} required>
                                                <option value="{{ $item->product->id }}"
                                                        data-price="{{ $item->product->cost_price ?? $item->product->purchase_price ?? 0 }}"
                                                        {{ $item->product_id == $item->product->id ? 'selected' : '' }}>
                                                    [{{ $item->product->sku ?? 'No SKU' }}] {{ $item->product->name }}
                                                </option>
                                            </select>
                                            @error("items.{$index}.product_id")
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </td>

                                {{-- Column 2: Batch & Expiry --}}
                                <td>
                                    <div class="batch-inputs">
                                        <input type="text" name="items[{{ $index }}][batch_number]"
                                               class="form-control form-control-sm @error("items.{$index}.batch_number") is-invalid @enderror"
                                               placeholder="Batch #"
                                               value="{{ old("items.{$index}.batch_number", is_object($item) ? $item->batch_number : ($item['batch_number'] ?? '')) }}">
                                        @error("items.{$index}.batch_number")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror

                                        <input type="date" name="items[{{ $index }}][expiry_date]"
                                               class="form-control form-control-sm margin-top-xs @error("items.{$index}.expiry_date") is-invalid @enderror"
                                               title="Expiry Date"
                                               value="{{ old("items.{$index}.expiry_date", (is_object($item) && $item->expiry_date) ? $item->expiry_date->format('Y-m-d') : ($item['expiry_date'] ?? '')) }}">
                                        @error("items.{$index}.expiry_date")
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </td>

                                {{-- Column 3: Quantity ordered --}}
                                <td class="text-center">
                                    <input type="number" name="items[{{ $index }}][quantity_ordered]"
                                           class="form-control text-center qty-input @error("items.{$index}.quantity_ordered") is-invalid @enderror"
                                           min="1" step="any" required
                                           value="{{ old("items.{$index}.quantity_ordered", is_object($item) ? $item->quantity_ordered : ($item['quantity_ordered'] ?? 1)) }}">
                                    @error("items.{$index}.quantity_ordered")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </td>

                                {{-- Column 4: Unit Price --}}
                                <td class="text-right">
                                    <input type="number" name="items[{{ $index }}][unit_price]"
                                           class="form-control text-right price-input @error("items.{$index}.unit_price") is-invalid @enderror"
                                           min="0" step="0.01" required
                                           value="{{ old("items.{$index}.unit_price", is_object($item) ? $item->unit_price : ($item['unit_price'] ?? 0.00)) }}">
                                    @error("items.{$index}.unit_price")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </td>

                                {{-- Column 5: Discount percentage --}}
                                <td class="text-center">
                                    <input type="number" name="items[{{ $index }}][discount_rate]"
                                           class="form-control text-center discount-input @error("items.{$index}.discount_rate") is-invalid @enderror"
                                           min="0" max="100" step="0.01"
                                           value="{{ old("items.{$index}.discount_rate", is_object($item) ? $item->discount_rate : ($item['discount_rate'] ?? 0.00)) }}">
                                    @error("items.{$index}.discount_rate")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </td>

                                {{-- Column 6: Live Line Total --}}
                                <td class="text-right middle-align">
                                    <span class="row-total">$0.00</span>
                                </td>

                                {{-- Column 7: Remove Item Action --}}
                                <td class="text-center middle-align">
                                    <button type="button" class="btn-delete remove-row-btn" title="Remove Line Item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <!-- JS fallback will populate a default row on load if empty -->
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Aggregates Summary & Notes Grid -->
            <div class="bottom-grid margin-top-lg">
                <!-- Internal Notes -->
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-sticky-note"></i> Internal Notes</h3>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" id="notes" rows="6" class="form-control textarea-notes"
                                  placeholder="Type any internal operational notes or supplier communications here...">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>

                <!-- Live Calculated Summary -->
                <div class="info-card financial-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calculator"></i> Calculations Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="detail-row">
                            <span class="detail-label">Subtotal:</span>
                            <span class="detail-value" id="calc-subtotal">$0.00</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tax Amount (e.g. 24%):</span>
                            <span class="detail-value text-muted" id="calc-tax">+ $0.00</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Discount Amount:</span>
                            <span class="detail-value text-error" id="calc-discount">- $0.00</span>
                        </div>
                        <hr class="divider">
                        <div class="detail-row total-row">
                            <span class="detail-label">Grand Total:</span>
                            <span class="detail-value grand-total" id="calc-grand-total">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Submission Actions -->
            <div class="form-actions-bar margin-top-lg">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Save & Update Order
                </button>
                <a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn btn-secondary btn-lg">
                    Cancel Changes
                </a>
            </div>
        </form>
    </div>

    <!-- Template for adding dynamic new rows -->
    <template id="row-template">
        <tr class="item-row" data-index="__INDEX__">
            <!-- Product Details Cascading Column -->
            <td>
                <div class="cascade-selectors">
                    <!-- Category Selector -->
                    <div class="form-group margin-bottom-xs">
                        <select class="form-select category-select" name="items[__INDEX__][category_id]" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brand Selector -->
                    <div class="form-group margin-bottom-xs">
                        <select class="form-select brand-select" name="items[__INDEX__][brand_id]" disabled required>
                            <option value="">Select Category first</option>
                        </select>
                    </div>

                    <!-- Product Selector -->
                    <div class="form-group">
                        <select class="form-select product-select" name="items[__INDEX__][product_id]" disabled
                                required>
                            <option value="">Select Brand first</option>
                        </select>
                    </div>
                </div>
            </td>

            <!-- Batch Reference -->
            <td>
                <div class="batch-inputs">
                    <input type="text" name="items[__INDEX__][batch_number]" class="form-control form-control-sm"
                           placeholder="Batch #">
                    <input type="date" name="items[__INDEX__][expiry_date]"
                           class="form-control form-control-sm margin-top-xs" title="Expiry Date">
                </div>
            </td>

            <!-- Quantity -->
            <td>
                <input type="number" name="items[__INDEX__][quantity_ordered]"
                       class="form-control text-center qty-input" value="1" min="1" required>
            </td>

            <!-- Cost Price -->
            <td>
                <input type="number" name="items[__INDEX__][unit_price]" class="form-control text-right price-input"
                       step="0.01" value="0.00" required>
            </td>

            <!-- Discount -->
            <td>
                <input type="number" name="items[__INDEX__][discount_rate]"
                       class="form-control text-center discount-input" step="0.01" value="0.00" min="0" max="100"
                       required>
            </td>

            <!-- Live Subtotal Display -->
            <td class="text-right middle-align">
                <span class="row-total fw-bold">$0.00</span>
            </td>

            <!-- Delete Row Action -->
            <td class="text-center middle-align">
                <button type="button" class="btn-delete remove-row-btn" title="Remove Line Item">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('scripts')
    <script type="application/javascript" src="{{ asset('js/purchases/edit.js') }}"></script>
@endsection