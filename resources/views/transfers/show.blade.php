@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/transfers/show.css') }}">
@endsection

@section('content')
    <div class="main-container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="page-header">
            <div class="header-titles">
                <div class="header-with-badge">
                    <h1 class="page-title">Transfer #{{ $transfer->transfer_number }}</h1>
                    <span class="badge" style="--badge-color: {{ $transfer->status_id->color() }};">
                        {{ $transfer->status_id->label() }}
                    </span>
                </div>
                <p class="page-subtitle">
                    Δημιουργήθηκε στις <strong>{{ $transfer->created_at->format('d M Y H:i') }}</strong>
                    από τον χρήστη <strong>{{ $transfer->creator->name ?? 'System' }}</strong>
                </p>
            </div>
            <div class="header-actions">
                @if($transfer->status_id === $status::PENDING)
                    <a href="{{ route('inventory.transfers.edit', $transfer) }}" class="btn btn-primary"
                       style="margin-right: 0.5rem; background-color: #2563eb; color: white; border-color: #2563eb;">
                        <i class="fas fa-edit"></i> Επεξεργασία
                    </a>
                @endif

                <a href="{{ route('inventory.transfers.index') }}" class="btn btn-secondary">
                    Πίσω στη Λίστα
                </a>
            </div>
        </div>

        <div class="transfer-route-panel">
            <div class="route-box source-box">
                <span class="route-label">Από Αποθήκη (Source)</span>
                <span class="route-warehouse-name">{{ $transfer->sourceWarehouse->name }}</span>
            </div>

            <div class="route-arrow-connector">
                &rarr;
            </div>

            <div class="route-box target-box">
                <span class="route-label">Προς Αποθήκη (Target)</span>
                <span class="route-warehouse-name">{{ $transfer->targetWarehouse->name }}</span>
            </div>
        </div>

        @if($transfer->notes)
            <div class="filter-card" style="margin-top: 1.5rem;">
                <h4 style="margin-top: 0; margin-bottom: 0.5rem; color: #4b5563;">Σημειώσεις / Λόγος Μεταφοράς</h4>
                <p style="margin: 0; line-height: 1.6; color: #1f2937; white-space: pre-line;">{{ $transfer->notes }}</p>
            </div>
        @endif

        <div class="table-container">
            <div class="table-header-title">
                <h3>Περιλαμβανόμενα Είδη</h3>
            </div>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Προϊόν (Product)</th>
                    <th>Batch Number</th>
                    <th class="text-right">Ποσότητα (Qty)</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transfer->items as $item)
                    <tr title="{{ $item->notes ?? 'No Notes' }}">
                        <td>
                            <div class="product-name">{{ $item->product->name ?? 'Unknown Product' }}</div>
                            <small class="text-muted">SKU: {{ $item->product->sku ?? '-' }}</small>
                        </td>
                        <td>
                            <code class="batch-code">{{ $item->batch_number ?? 'No Batch' }}</code>
                        </td>
                        <td class="text-right fw-bold">
                            {{ $item->quantity_requested }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-table-state">
                            Δεν βρέθηκαν προϊόντα σε αυτή τη μεταφορά.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection