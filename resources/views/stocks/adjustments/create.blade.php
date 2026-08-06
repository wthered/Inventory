@extends('templates.general')

@section('title', 'Create Stock Adjustment')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/stocks/adjustments/create.css') }}">
@endsection

@section('content')
    <div class="page-container">
        <div class="page-header">
            <div class="header-title-group">
                <h1><i class="fas fa-sliders-h icon-accent"></i> Create Stock Adjustment</h1>
                <p class="subtitle">Reconcile inventory variances and log physical stock adjustments.</p>
            </div>
            <div class="action-buttons-group">
                <a href="{{ route('inventory.adjustments.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"
                 style="background-color: #f8d7da; color: #842029; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li class="error">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventory.adjustments.store') }}" method="POST" id="adjustment-form">
            @csrf

            <!-- Metadata Section -->
            <div class="card meta-card">
                <div class="form-row">
                    <div class="form-group">
                        <label for="warehouse_id" class="form-label required">Warehouse</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                            <option value="" disabled selected>Select Target Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="adjustment_date" class="form-label required">Adjustment Date</label>
                        <input type="datetime-local"
                               name="adjustment_date"
                               id="adjustment_date"
                               class="form-control"
                               value="{{ now()->format('Y-m-d\TH:i') }}"
                               required>
                    </div>

                    <div class="form-group full-width">
                        <label for="notes" class="form-label">Notes / Reason Overview</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"
                                  placeholder="Provide general context for this stock adjustment..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Adjustment Items Section -->
            <div class="table-card margin-top">
                <div class="card-header-bar">
                    <span class="card-title"><i class="fas fa-boxes"></i> Adjusted Products</span>
                    <button type="button" class="btn-primary btn-sm" id="add-product-btn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <div class="table-wrapper">
                    <table class="data-table" id="items-table">
                        <thead>
                        <tr>
                            <th style="width: 22%;">Product</th>
                            <th style="width: 20%;">Warehouse Location</th>
                            <th style="width: 15%;">Adjustment Type</th>
                            <th style="width: 12%;">Quantity Change</th>
                            <th style="width: 23%;">Reason</th>
                            <th style="width: 8%;" class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody id="adjustment-rows">
                        <!-- Default empty state -->
                        <tr id="empty-row">
                            <td colspan="5" class="table-empty-state">
                                <i class="fas fa-box-open empty-icon"></i>
                                <p>No products added to this adjustment yet.</p>
                                <button type="button" class="btn-secondary btn-sm" id="trigger-first-add">Add First
                                    Product
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-actions text-right margin-top">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Adjustment
                </button>
            </div>
        </form>
    </div>

    <!-- Hidden Template for Dynamic JS Item Injection -->
    <template id="adjustment-row-template">
        <tr class="adjustment-item-row" data-item-id="INDEX">
            <td>
                <div class="product-search-container">
                    <div class="input-with-icon">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                               class="form-control product-search-input"
                               data-item-id="INDEX"
                               placeholder="Search by Name, SKU, or Barcode..."
                               autocomplete="off">
                    </div>

                    <input type="hidden"
                           name="items[INDEX][product_id]"
                           class="product-id-hidden"
                           data-item-id="INDEX"
                           required>

                    <div class="product-dropdown-results hidden" data-item-id="INDEX"></div>
                </div>
            </td>
            <td>
                <!-- 5 Location Dropdowns + Error Feedback -->
                <div class="location-select-group" data-item-id="INDEX">
                    <div class="location-dropdowns-grid">
                        <select class="form-control loc-select loc-zone" data-item-id="INDEX" disabled>
                            <option value="" disabled selected>Zone</option>
                        </select>
                        <select class="form-control loc-select loc-aisle" data-item-id="INDEX" disabled>
                            <option value="" disabled selected>Aisle</option>
                        </select>
                        <select class="form-control loc-select loc-rack" data-item-id="INDEX" disabled>
                            <option value="" disabled selected>Rack</option>
                        </select>
                        <select class="form-control loc-select loc-shelf" data-item-id="INDEX" disabled>
                            <option value="" disabled selected>Shelf</option>
                        </select>
                        <select class="form-control loc-select loc-bin" data-item-id="INDEX" disabled>
                            <option value="" disabled selected>Bin</option>
                        </select>
                    </div>

                    <!-- Hidden location ID posted with form -->
                    <input type="hidden"
                           name="items[INDEX][location_id]"
                           class="location-id-hidden"
                           data-item-id="INDEX">

                    <!-- Dynamic Error Display -->
                    <span class="location-error-msg hidden"></span>
                </div>
            </td>
            <td>
                <select name="items[INDEX][type]" class="form-control type-select" required>
                    <option value="" disabled>Τύπος μετακίνησης</option>
                    @foreach($typeGroup::cases() as $type)
                        <option value="{{ $type->value }}">
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number"
                       name="items[INDEX][quantity]"
                       class="form-control qty-input"
                       min="1"
                       value="1"
                       required>
            </td>
            <td>
                <select name="items[INDEX][reason]" class="form-control reason-select" required>
                    @foreach($adjustmentReasons::forDropdown() as $groupLabel => $reasons)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($reasons as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </td>
            <td class="text-right actions-cell">
                <button type="button" class="btn-action btn-delete remove-row-btn" title="Remove Item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('scripts')
    <script src="{{ asset('js/stocks/adjustments/create.js') }}"></script>
    <script src="{{ asset('js/stocks/adjustments/search/create.js') }}"></script>
@endsection