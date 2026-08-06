@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/adjustments/index.css') }}">
@endsection

@section('content')
    <div class="main-container">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Προσαρμογές Αποθεμάτων</h1>
                <p class="page-subtitle">Διαχειριστείτε τις χειροκίνητες διορθώσεις και μεταβολές αποθέματος ανά
                    αποθήκη.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary">
                    ➕ Νέα Προσαρμογή
                </a>
            </div>
        </div>

        {{-- Success Messages --}}
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close-btn"
                        style="background: none; border: none; cursor: pointer; font-size: 1.2rem;"
                        onclick="document.getElementById('success-alert').remove()">&times;
                </button>
            </div>
        @endif

        {{-- Filters Card --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('inventory.adjustments.index') }}" class="filter-form">
                <div class="form-group">
                    <label for="search" class="form-label">Αναζήτηση Προϊόντος</label>
                    <input type="text" name="search" id="search" class="form-input" value="{{ request('search') }}"
                           placeholder="Όνομα ή SKU...">
                </div>

                <div class="form-group">
                    <label for="reason" class="form-label">Αιτιολογία</label>
                    <select name="reason" id="reason" class="form-select">
                        <option value="">Όλες οι αιτιολογίες</option>
                        @foreach($reasons::forDropdown() as $groupLabel => $reasons)
                            <optgroup label="{{ $groupLabel }}">
                                @foreach($reasons as $value => $label)
                                    <option value="{{ $value }}" {{ request('reason') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="date" class="form-label">Ημερομηνία</label>
                    <input type="date" name="date" id="date" class="form-input" value="{{ request('date') }}">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">🔍 Φιλτράρισμα</button>
                    <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-link"
                       style="font-size: 0.9rem;">Καθαρισμός</a>
                </div>
            </form>
        </div>

        {{-- Data Table Container --}}
        <div class="table-container">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Κωδικός (ID)</th>
                    <th>Ημερομηνία</th>
                    <th>Προϊόντα & Γραμμές</th>
                    <th>Συνολική Μεταβολή</th>
                    <th>Υπάλληλος</th>
                    <th class="text-right">Ενέργειες</th>
                </tr>
                </thead>
                <tbody id="adjustments">
                @forelse($adjustments as $adjustment)
                    @include('common.adjustments.index_row', ['adjustment' => $adjustment])
                @empty
                    <tr>
                        <td colspan="6" class="empty-table-state">Δεν βρέθηκαν καταγραφές προσαρμογών που να πληρούν τα
                            κριτήρια.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Pagination Footer --}}
            @if($adjustments->hasPages())
                <div id="pagination">
                    <div class="pagination-footer"
                         style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border-top: 1px solid #e3e6f0;">
                        <div class="pagination-info" style="font-size: 0.85rem; color: #64748b;">
                            Εμφάνιση {{ $adjustments->firstItem() }} έως {{ $adjustments->lastItem() }}
                            από {{ $adjustments->total() }} καταγραφές
                        </div>
                        <div class="pagination-links">
                            {{ $adjustments->withQueryString()->links('pagination::simple') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script type="application/javascript" src="{{ asset('js/stocks/adjustments/index.js') }}"></script>
@endsection