@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/transfers/edit.css') }}">
@endsection

@section('content')
    <div class="main-container">

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Επεξεργασία Μεταφοράς #{{ $transfer->transfer_number }}</h1>
                <p class="page-subtitle">Τροποποίηση στοιχείων και ποσοτήτων της εκκρεμούς μεταφοράς</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.transfers.index') }}" class="btn btn-secondary">
                    Ακύρωση
                </a>
            </div>
        </div>

        <form action="{{ route('inventory.transfers.update', $transfer) }}" method="POST" class="edit-transfer-form">
            @csrf
            @method('PUT')

            <div class="filter-card route-summary-card">
                <div class="route-info-group">
                    <span class="route-meta-label">Από Αποθήκη</span>
                    <span class="route-meta-value">{{ $transfer->sourceWarehouse->name }}</span>
                </div>
                <div class="route-meta-separator">&rarr;</div>
                <div class="route-info-group">
                    <span class="route-meta-label">Προς Αποθήκη</span>
                    <span class="route-meta-value">{{ $transfer->targetWarehouse->name }}</span>
                </div>
            </div>

            <div class="table-container m-bottom">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Προϊόν (Product)</th>
                        <th>Batch Number</th>
                        <th class="text-right" style="width: 150px;">Ποσότητα</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transfer->items as $index => $item)
                        <tr>
                            <td>
                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                <div class="product-title">{{ $item->product->name ?? 'Unknown Product' }}</div>
                                <small class="text-muted">SKU: {{ $item->product->sku ?? '-' }}</small>
                            </td>
                            <td>
                                <code class="batch-display">{{ $item->batch_number ?? 'No Batch' }}</code>
                            </td>
                            <td class="text-right">
                                <input type="number"
                                       name="items[{{ $index }}][quantity]"
                                       class="form-control text-right edit-qty-input"
                                       value="{{ old("items.{$index}.quantity", $item->quantity_requested) }}"
                                       min="1"
                                       required>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="filter-card">
                <div class="form-group">
                    <label for="notes" class="form-label">Σημειώσεις / Λόγος Μεταφοράς</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-control">{{ old('notes', $transfer->notes) }}</textarea>
                </div>
            </div>

            <div class="form-actions-container">
                <a href="{{ route('inventory.transfers.index') }}" class="btn btn-secondary">Επιστροφή</a>
                <button type="submit" class="btn btn-primary">Αποθήκευση Αλλαγών</button>
            </div>
        </form>

    </div>
@endsection