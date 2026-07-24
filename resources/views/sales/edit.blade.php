@extends('templates.general')

@section('title', 'Επεξεργασία Παραγγελίας Πώλησης #' . ($sale->order_number ?? $sale->id))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/sales/edit.css') }}"/>
@endsection

@section('content')
    <div class="main-container">
        <!-- Header Block -->
        <div class="page-header">
            <div class="header-title">
                <h1>Επεξεργασία Παραγγελίας</h1>
                <p class="subtitle">Τροποποίηση στοιχείων για την
                    παραγγελία {{ $sale->order_number ?? '#' . $sale->id }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Ακύρωση / Επιστροφή
                </a>
            </div>
        </div>

        {{-- Display Global Validation Errors if any --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="margin-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventory.sales.update', $sale->id) }}" method="POST" class="edit-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <!-- LEFT COLUMN: Main Form Cards -->
                <div class="form-main">
                    <!-- Core Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-file-invoice"></i> Βασικά Στοιχεία Παραγγελίας</h2>
                        </div>
                        <div class="card-body">
                            <div class="input-row">
                                <div class="form-group">
                                    <label for="customer_id">Πελάτης:</label>
                                    <select name="customer_id" id="customer_id" class="form-select" disabled>
                                        {{-- Κλειδωμένο κατά το Edit όπως στον Controller σου --}}
                                        <option value="{{ $sale->customer_id }}">
                                            {{ $sale->customer->name ?? 'Unknown Customer' }}
                                        </option>
                                    </select>
                                    <small class="text-muted">Ο πελάτης δεν μπορεί να αλλάξει μετά τη
                                        δημιουργία.</small>
                                </div>

                                <div class="form-group">
                                    <label for="warehouse_id">Αποθήκη Εξυπηρέτησης:</label>
                                    <select name="warehouse_id" id="warehouse_id"
                                            class="form-select @error('warehouse_id') is-invalid @enderror">
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $sale->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id') <span class="error-text">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="input-row">
                                <div class="form-group">
                                    <label for="order_date">Ημερομηνία Παραγγελίας:</label>
                                    <input type="date"
                                           name="order_date"
                                           id="order_date"
                                           class="form-control @error('order_date') is-invalid @enderror"
                                           value="{{ old('order_date', $sale->order_date ? $sale->order_date->format('Y-m-d') : '') }}">
                                    @error('order_date') <span class="error-text">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="created_by">Καταχωρήθηκε από (Υπάλληλος):</label>
                                    <select name="created_by" id="created_by" class="form-select" disabled>
                                        {{-- Read-only βάσει της λογικής του Controller --}}
                                        <option value="">
                                            {{ $sale->creator->account->fullName }} (ID)
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Card (Static View inside Edit) -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-boxes"></i> Προϊόντα Παραγγελίας</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="edit-table">
                                <thead>
                                <tr>
                                    <th>Προϊόν</th>
                                    <th class="text-center">Ποσότητα</th>
                                    <th class="text-right">Τιμή Μονάδας</th>
                                    <th class="text-right">Σύνολο Γραμμής</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sale->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name ?? 'Unknown' }}</strong>
                                            <span class="item-meta"><br>SKU: {{ $item->product->sku ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center font-numeric">{{ $item->quantity_ordered }}</td>
                                        <td class="text-right font-numeric">
                                            €{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right font-numeric">
                                            <strong>€{{ number_format($item->total_ordered_price, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Sidebar Form Cards -->
                <div class="form-sidebar">
                    <!-- Notes & Submit Card -->
                    <div class="card HTML-card-accent">
                        <div class="card-header">
                            <h3><i class="fas fa-comment-alt"></i> Σημειώσεις / Οδηγίες</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea name="notes" id="notes" rows="6" class="form-control"
                                          placeholder="Προσθέστε παρατηρήσεις ή οδηγίες παράδοσης...">{{ old('notes', $sale->notes) }}</textarea>
                            </div>

                            <hr class="form-divider">

                            <div class="sidebar-totals">
                                <div class="total-row">
                                    <span>Συνολικό Ποσό (Grand Total):</span>
                                    <span class="font-numeric price-tag">€{{ number_format($sale->grand_total, 2) }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Αποθήκευση Αλλαγών
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection