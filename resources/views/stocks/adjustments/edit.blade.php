@extends('templates.general')

@section('title')
    Edit Adjustment #{{ $adjustment->id }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/adjustments/edit.css') }}">
@endsection

@section('content')
    <div class="main-container">

        {{-- Page Header --}}
        <div class="page-header-container">
            <div class="header-titles">
                <h1 class="page-title">Επεξεργασία Προσαρμογής #{{ $adjustment->id }}</h1>
                <p class="page-subtitle">
                    Τροποποίηση στοιχείων του εγγράφου <strong>{{ $adjustment->adjustment_number }}</strong>
                    για την αποθήκη: <span class="badge bg-primary fs-6">{{ $adjustment->warehouse->name }}</span>
                </p>
            </div>

            <div class="header-actions">
                <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-secondary">
                    ← Ακύρωση & Επιστροφή
                </a>
            </div>
        </div>

        {{-- Validation Errors Alert --}}
        @if ($errors->any())
            <div class="alert"
                 style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                <div class="fw-bold">Παρακαλώ διορθώστε τα παρακάτω σφάλματα:</div>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Edit Form --}}
        <div class="form-container">
            <form action="{{ route('inventory.adjustments.update', $adjustment->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Στοιχεία Κεφαλίδας (Warehouse & Date) --}}
                <div class="form-grid">
                    {{-- Warehouse Dropdown --}}
                    <div class="form-group">
                        <label for="warehouse_id" class="form-label">Αποθήκη</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $adjustment->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Adjustment Date --}}
                    <div class="form-group">
                        <label for="adjustment_date" class="form-label">Ημερομηνία Προσαρμογής</label>
                        <input type="date" name="adjustment_date" id="adjustment_date" class="form-input"
                               value="{{ old('adjustment_date', $adjustment->adjustment_date->format('Y-m-d')) }}"
                               required>
                        @error('adjustment_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Υπεύθυνη Αποθήκη</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: #e9ecef;"><i class="bi bi-house-door"></i></span>
                        <input type="text" class="form-input fw-bold text-primary" value="{{ $manager ? $manager->firstName." ".$manager->lastName : 'Δεν ορίστηκε' }}" readonly disabled style="background-color: #f8f9fa;">
                    </div>
                    {{-- Κρυφό input για να περνάει το warehouse_id στο request αν χρειάζεται --}}
                    <input type="hidden" name="warehouse_id" value="{{ $adjustment->warehouse_id }}">
                </div>

                {{-- Τίτλος Ενότητας Γραμμών & Κουμπί Προσθήκης --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid #eaecf0; padding-bottom: 0.5rem;">
                    <h3 style="margin: 0; color: #4e73df; font-size: 1.1rem;">
                        Γραμμές Προϊόντων Μεταβολής
                    </h3>
                    <button type="button" id="add-item-btn" class="btn btn-primary"
                            style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                        ➕ Προσθήκη Γραμμής
                    </button>
                </div>

                {{-- Container για τις γραμμές (Διαβάζεται από την JS) --}}
                <div id="items-container">
                    @foreach($adjustment->items as $index => $item)

                        <div class="item-row-card" data-index="{{ $item->id }}">

                            <div class="item-row-title"
                                 style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    📦 Γραμμή #{{ $index + 1 }}: {{ $item->product->name ?? 'Άγνωστο Προϊόν' }}
                                    <span style="font-size: 0.8rem; font-weight: normal; color: #64748b;">(SKU: {{ $item->product->sku ?? '-' }})</span>
                                </div>
                                <button type="button" class="btn btn-link remove-item-btn"
                                        style="color: #dc3545; padding: 0; text-decoration: none;"
                                        title="Αφαίρεση Γραμμής">
                                    ❌ Αφαίρεση
                                </button>
                            </div>

                            <div class="form-grid">
                                {{-- 1. Category Selector --}}
                                <div class="form-group">
                                    <label class="form-label" for="category">Κατηγορία</label>
                                    <select class="form-select category-select" data-item-id="{{ $item->id }}"
                                            id="category">
                                        <option value="">Επιλέξτε Κατηγορία...</option>

                                        @foreach($categories as $parent)
                                            @if($parent->children && $parent->children->isNotEmpty())
                                                {{-- Αν η κατηγορία έχει υποκατηγορίες, τις ομαδοποιούμε --}}
                                                <optgroup label="{{ $parent->name }}">
                                                    @foreach($parent->children as $child)
                                                        <option value="{{ $child->id }}" {{ data_get($item, 'product.category_id') == $child->id ? 'selected' : '' }}>
                                                            {{ $child->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                {{-- Αν η κατηγορία δεν έχει παιδιά, εμφανίζεται ως απλή επιλογή --}}
                                                <option value="{{ $parent->id }}" {{ data_get($item, 'product.category_id') == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->name }}
                                                </option>
                                            @endif
                                        @endforeach

                                    </select>
                                </div>

                                {{-- 2. Brand Selector --}}
                                <div class="form-group">
                                    <label class="form-label" for="brand">Μάρκα (Brand)</label>
                                    <select class="form-select brand-select" id="brand" data-item-id="{{ $item->id }}" {{ !data_get($item, 'product.category_id') ? 'disabled' : '' }}>
                                        <option value="">Επιλέξτε Μάρκα...</option>
                                        @if($item->product && $item->product->brand)
                                            <option value="{{ $item->product->brand_id }}" selected>{{ $item->product->brand->name }}</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- 3. Product Search String + Selector --}}
                                <div class="form-group">
                                    <label class="form-label" for="product">Αναζήτηση Προϊόντος</label>
                                    <input type="text" class="form-input product-search-input"
                                           data-item-id="{{ $item->id }}" placeholder="Πληκτρολογήστε Όνομα ή SKU..."
                                           id="product">

                                    <select name="items[{{ $item->id }}][product_id]"
                                            class="form-select product-ajax-select" data-item-id="{{ $item->id }}"
                                            required style="margin-top: 5px;">
                                        @if($item->product)
                                            <option value="{{ $item->product_id }}" selected>
                                                {{ $item->product->name }} (SKU: {{ $item->product->sku }})
                                            </option>
                                        @else
                                            <option value="">Αναζητήστε παραπάνω...</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- Location Selector με 5 Cascade Επιπέδα --}}
                                <div class="form-group warehouse-cascade-container" data-item-id="{{ $item->id }}" style="margin-bottom: 1rem;">

                                    <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Θέση (Location)</label>

                                    {{-- Το κρυφό input που κρατάει το ID για το Request --}}
                                    <input type="hidden"
                                           name="items[{{ $item->id }}][location_id]"
                                           id="location_hidden_{{ $item->id }}"
                                           value="{{ old('items.'.$item->id.'.location_id', $item->location_id) }}"
                                           required>

                                    {{-- Τα 5 select - Θα συμπληρωθούν αυτόματα με HTML από το AJAX response --}}
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 0.25rem;">
                                        <select class="form-select level-select" data-level="0"
                                                style="flex: 1; min-width: 75px; padding: 0.375rem 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px;">
                                            <option value="">Λωρίδα</option>
                                        </select>
                                        <select class="form-select level-select" data-level="1"
                                                style="flex: 1; min-width: 75px; padding: 0.375rem 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px;"
                                                disabled>
                                            <option value="">Ράφι</option>
                                        </select>
                                        <select class="form-select level-select" data-level="2"
                                                style="flex: 1; min-width: 75px; padding: 0.375rem 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px;"
                                                disabled>
                                            <option value="">Κολώνα</option>
                                        </select>
                                        <select class="form-select level-select" data-level="3"
                                                style="flex: 1; min-width: 75px; padding: 0.375rem 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px;"
                                                disabled>
                                            <option value="">Ύψος</option>
                                        </select>
                                        <select class="form-select level-select" data-level="4"
                                                style="flex: 1; min-width: 75px; padding: 0.375rem 0.5rem; font-size: 0.9rem; border: 1px solid #ccc; border-radius: 4px;"
                                                disabled>
                                            <option value="">Θέση</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Reason Dropdown --}}
                                <div class="form-group">
                                    <label class="form-label">Αιτιολογία</label>
                                    <select name="items[{{ $item->id }}][reason]" id="reason_{{ $item->id }}"
                                            class="form-select" required>
                                        <option value="" disabled>Επιλέξτε Αιτιολογία...</option>

                                        @foreach($reasons as $groupLabel => $options)
                                            <optgroup label="{{ $groupLabel }}">
                                                @foreach($options as $value => $label)
                                                    <option value="{{ $value }}" {{ old("items.{$item->id}.reason", $item->reason->value) == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Type --}}
                                <div class="form-group">
                                    <label class="form-label">Τύπος Κίνησης</label>
                                    <select name="items[{{ $item->id }}][type]" class="form-select" required>
                                        @foreach($types::cases() as $case)
                                            <option value="{{ $case->value }}" {{ old("items.{$item->id}.type", $item->type->value) == $case->value ? 'selected' : ''  }}>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Quantity --}}
                                <div class="form-group">
                                    <label class="form-label">Ποσότητα Μεταβολής</label>
                                    <input type="number" name="items[{{ $item->id }}][quantity]" class="form-input" min="1" value="{{ old("items.{$item->id}.quantity", abs($item->quantity)) }}" required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- HTML Template για τις νέες δυναμικές γραμμές --}}
                @include('partials.adjustments.edit')

                {{-- Global Notes --}}
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="notes" class="form-label">Σημειώσεις / Παρατηρήσεις Εγγράφου</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4"
                                  placeholder="Προσθέστε σχόλια σχετικά με την αλλαγή...">{{ old('notes', $adjustment->notes) }}</textarea>
                        @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Form Actions Buttons --}}
                <div class="form-actions-bar">
                    <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-secondary">
                        Ακύρωση
                    </a>
                    <button type="submit" class="btn btn-success">
                        💾 Αποθήκευση Αλλαγών
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/stocks/adjustments/edit.js') }}"></script>
    <script src="{{ asset('js/stocks/adjustments/search/product.js') }}"></script>
    <script src="{{ asset('js/stocks/adjustments/locations.js') }}"></script>
@endsection
