@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/stocks/returns/index.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
@endsection

@section('content')
    <div class="main-container">

        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Επιστροφές Αποθεμάτων</h1>
                <p class="page-subtitle">Διαχειριστείτε τις επιστροφές προϊόντων.</p>
            </div>
            <a href="{{ route('inventory.returns.create') }}" class="btn btn-primary">+ Νέα Επιστροφή</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success" id="success-alert">
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close-btn"
                        onclick="document.getElementById('success-alert').remove()">&times;
                </button>
            </div>
        @endif

        <div class="filter-card">
            <form method="GET" action="{{ route('inventory.returns.index') }}" class="filter-form">
                <div class="form-group">
                    <label for="search" class="form-label">Αναζήτηση</label>
                    <input type="text" name="search" id="search" class="form-input" value="{{ request('search') }}"
                           placeholder="Κωδικός, SKU...">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Κατάσταση</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Όλες οι καταστάσεις</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="date" class="form-label">Ημερομηνία</label>
                    <input type="date" name="date" id="date" class="form-input"
                           value="{{ old('date', request('date')) }}">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary filter-action">Φιλτράρισμα</button>
                    <a href="{{ route('inventory.returns.index') }}" class="btn btn-link filter-action">Καθαρισμός</a>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID Επιστροφής</th>
                    <th>Ημερομηνία</th>
                    <th>Προϊόν</th>
                    <th>Ποσότητα</th>
                    <th>Κατάσταση</th>
                    <th>Υπάλληλος</th>
                    <th class="text-right">Ενέργειες</th>
                </tr>
                </thead>
                <tbody>
                @forelse($returns as $return)
                    <tr>
                        <td class="fw-bold">#{{ $return->id }}</td>
                        <td>{{ $return->created_at->format('d/m/Y H:i') }}</td>

                        <td>
                            @foreach($return->items as $item)
                                <div class="product-item-row" style="margin-bottom: 0.5rem;">
                                    <span class="product-name" style="display: block; font-weight: 500;">
                                        {{ $item->product->name ?? 'Άγνωστο Προϊόν' }}
                                    </span>
                                    <span class="product-sku" style="font-size: 0.8rem; color: #64748b;">
                                        SKU: {{ $item->product->sku ?? '-' }}
                                        @if($item->quality_status)
                                            <small class="text-muted">({{ $item->quality_status }})</small>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </td>

                        <td class="fw-bold">
                            @if($return->items->count() > 1)
                                {{ $return->items->sum('quantity') }} τεμ.
                                <div style="font-size: 0.75rem; font-weight: normal; color: #64748b;">
                                    ({{ $return->items->count() }} κωδικοί)
                                </div>
                            @else
                                {{ $return->items->first()->quantity ?? 0 }} τεμ.
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-status-{{ $return->status->value }}">{{ $return->status->label() }}</span>
                        </td>
                        <td>{{ $return->creator->account->fullName ?? 'Σύστημα' }}</td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <a href="{{ route('inventory.returns.show', $return->id) }}"
                                   class="action-link view-link">Προβολή</a>
                                @if($return->status->value === 'pending')
                                    <a href="{{ route('inventory.returns.edit', $return->id) }}"
                                       class="action-link edit-link">Επεξεργασία</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-table-state">Δεν βρέθηκαν καταγραφές επιστροφών.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            @if($returns->hasPages())
                <div class="pagination-footer">
                    <div class="pagination-info">
                        Εμφάνιση {{ $returns->firstItem() }} έως {{ $returns->lastItem() }} από {{ $returns->total() }}
                        επιστροφές
                    </div>
                    <div class="pagination-links">
                        {{ $returns->withQueryString()->links('pagination::simple') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection