@extends('templates.general')

@section('content')
    <div class="transfer-container">
        <div class="page-header">
            <h1 class="page-title">Create Stock Transfer</h1>
            <a href="{{ route('transfers.index') }}" class="btn-link">&larr; Back to List</a>
        </div>

        <form action="{{ route('transfers.store') }}" method="POST" id="transfer-form">
            @csrf

            <div class="card header-card">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Source Warehouse</label>
                        <select name="from_warehouse_id" id="from_warehouse_id" required>
                            <option value="">Select Origin</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Destination Warehouse</label>
                        <select name="to_warehouse_id" id="to_warehouse_id" required>
                            <option value="">Select Destination</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Transfer Date</label>
                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="card items-card">
                <div class="card-header">
                    <h3>Transfer Items</h3>
                    <button type="button" class="btn-secondary" id="add-row">+ Add Product</button>
                </div>

                <table class="items-table" id="items-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th width="150">Current Stock</th>
                        <th width="150">Transfer Qty</th>
                        <th width="50"></th>
                    </tr>
                    </thead>
                    <tbody id="items-body">
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Process Transfer</button>
            </div>
        </form>
    </div>

    <template id="row-template">
        <tr>
            <td>
                <select name="items[{index}][product_id]" class="product-select" required>
                    <option value="">Search Product...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="0">
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="stock-preview" readonly value="0">
            </td>
            <td>
                <input type="number" name="items[{index}][quantity]" class="qty-input" min="1" required>
            </td>
            <td>
                <button type="button" class="btn-remove">&times;</button>
            </td>
        </tr>
    </template>
@endsection