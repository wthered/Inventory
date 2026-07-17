@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/stocks/returns/show.css') }}">
@endsection

@section('content')
    <div class="main-container">

        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Λεπτομέρειες Επιστροφής #{{ $return->return_number }}</h1>
                <p class="page-subtitle">Δείτε την αναλυτική κατάσταση και τα προϊόντα της επιστροφής.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('inventory.returns.index') }}" class="btn btn-secondary">← Πίσω στη Λίστα</a>
                @if($return->status->value === 'pending')
                    <a href="{{ route('inventory.returns.edit', $return->id) }}" class="btn btn-primary">Επεξεργασία</a>
                @endif
            </div>
        </div>

        <div class="filter-card" style="margin-bottom: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #1e293b; font-size: 1.1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                Γενικές Πληροφορίες
            </h3>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Κωδικός Επιστροφής</span>
                    <span class="detail-value">{{ $return->return_number }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">RMA Number</span>
                    <span class="detail-value">{{ $return->rma_number ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Ημερομηνία Επιστροφής</span>
                    <span class="detail-value">{{ $return->return_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Κατάσταση</span>
                    <div>
                        @if($return->status->value === 'approved')
                            <span class="badge badge-status-approved">Εγκρίθηκε</span>
                        @elseif($return->status->value === 'pending')
                            <span class="badge badge-status-pending">Εκκρεμεί</span>
                        @else
                            <span class="badge badge-status-rejected">Απορρίφθηκε</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-grid" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                <div class="detail-item">
                    <span class="detail-label">Προέλευση (Σχετικό Παραστατικό)</span>
                    <span class="detail-value">
                        @if($return->returnable)
                            {{ class_basename($return->returnable_type) }} #{{ $return->returnable_id }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Μεταφορική (Carrier)</span>
                    <span class="detail-value">{{ $return->carrier ?? 'Δεν ορίστηκε' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Αριθμός Αποστολής (Tracking)</span>
                    <span class="detail-value">
                        @if($return->tracking_number)
                            <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: monospace;">
                                {{ $return->tracking_number }}
                            </code>
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Καταχωρήθηκε Από</span>
                    <span class="detail-value">{{ $return->creator->account->fullName ?? '-' }}</span>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; color: #1e293b; font-size: 1.2rem;">Προϊόντα Επιστροφής</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Προϊόν</th>
                    <th>Ποσότητα</th>
                    <th>Κόστος Μονάδας</th>
                    <th>Κατάσταση Ποιότητας</th>
                    <th>Επαναφορά στο Απόθεμα;</th>
                    <th>Ημ. Επαναφοράς</th>
                    <th>Σημειώσεις Επιθεώρησης</th>
                </tr>
                </thead>
                <tbody>
                @forelse($return->items as $item)
                    <tr>
                        <td>
                            <div class="product-name" style="font-weight: 600; color: #0f172a;">
                                {{ $item->product->name ?? 'Άγνωστο Προϊόν' }}
                            </div>
                            <div class="product-sku" style="font-size: 0.8rem; color: #64748b; font-family: monospace;">
                                SKU: {{ $item->product->sku ?? '-' }}
                            </div>
                        </td>
                        <td class="fw-bold">{{ $item->quantity }} τεμ.</td>
                        <td>{{ $item->unit_cost ? number_format($item->unit_cost, 2) . ' €' : '-' }}</td>
                        <td>
                                <span class="badge-quality quality-{{ strtolower($item->quality_status) }}">
                                    @if($item->quality_status === 'new') Νέο
                                    @elseif($item->quality_status === 'damaged') Κατεστραμμένο
                                    @else {{ $item->quality_status }}
                                    @endif
                                </span>
                        </td>
                        <td>
                            @if($item->is_restockable)
                                <span style="color: #10b981; font-weight: 600;">✔ Ναι</span>
                            @else
                                <span style="color: #ef4444; font-weight: 600;">✘ Όχι</span>
                            @endif
                        </td>
                        <td>
                            @if($item->restocked_at)
                                <span style="font-size: 0.85rem; color: #475569;">
                                        {{ \Carbon\Carbon::parse($item->restocked_at)->format('d/m/Y H:i') }}
                                    </span>
                            @else
                                <span style="color: #94a3b8; font-style: italic; font-size: 0.85rem;">Δεν επαναφέρθηκε</span>
                            @endif
                        </td>
                        <td style="max-width: 250px; white-space: normal; color: #64748b; font-size: 0.85rem;">
                            {{ $item->inspection_notes ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-table-state">Δεν υπάρχουν προϊόντα καταχωρημένα σε αυτή την επιστροφή.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection