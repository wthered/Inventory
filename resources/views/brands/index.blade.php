@extends('templates.general') {{-- Το κεντρικό template της εφαρμογής σας --}}

@section('title', 'Διαχείριση Brands')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brands/index.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-trademark icon-accent"></i> Brands
                </h1>
                <p class="subtitle">Διαχείριση και επισκόπηση των κατασκευαστών/brands των προϊόντων σας.</p>
            </div>

            <div>
                <a href="{{ route('inventory.brands.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Νέο Brand
                </a>
            </div>
        </div>

        {{-- System Feedback --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Σύνθετη Μπάρα Φίλτρων (Parent & Child Selects) --}}
        <div class="table-filters-bar"
             style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-md); margin-bottom: var(--space-md); box-shadow: var(--shadow-sm);">
            <form action="{{ route('inventory.brands.index') }}" method="GET"
                  style="display: flex; gap: var(--space-md); flex-wrap: wrap; align-items: center;">

                {{-- Text Search --}}
                <div style="flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: var(--space-xs);">
                    <label for="search"
                           style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Αναζήτηση
                        Brand</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Πληκτρολογήστε όνομα..."
                           style="width: 100%; background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.5rem 0.75rem; color: var(--color-text-primary); font-size: 0.875rem;">
                </div>

                {{-- Select 1: Parent Category --}}
                <div style="width: 220px; display: flex; flex-direction: column; gap: var(--space-xs);">
                    <label for="parent_category_id"
                           style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Γονική
                        Κατηγορία</label>
                    <select name="parent_category_id" id="parent_category_id"
                            style="width: 100%; background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.5rem 0.75rem; color: var(--color-text-primary); font-size: 0.875rem; cursor: pointer;">
                        <option value="">Όλες οι γονικές κατηγορίες</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ request('parent_category_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Select 2: Child Category (Dependent Dropdown) --}}

                <div style="width: 220px; display: flex; flex-direction: column; gap: var(--space-xs);">
                    <label for="category_id"
                           style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;">Υποκατηγορία</label>
                    <select name="category_id" id="category_id"
                            style="width: 100%; background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.5rem 0.75rem; color: var(--color-text-primary); font-size: 0.875rem; cursor: pointer;" {{ $subcategories->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Όλες οι υποκατηγορίες</option>
                        @foreach($subcategories as $id => $name)
                            <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div style="display: flex; gap: var(--space-sm); align-self: flex-end; margin-top: auto;">
                    <button type="submit" class="btn-primary btn-sm" style="height: 35px;">
                        <i class="fas fa-search"></i> Φιλτράρισμα
                    </button>
                    @if(request()->has('search') || request()->has('parent_category_id') || request()->has('category_id'))
                        <a href="{{ route('inventory.brands.index') }}" class="btn-action btn-edit"
                           style="width: auto; padding: 0 0.75rem; font-size: 0.8125rem; height: 35px; display: flex; align-items: center;"
                           title="Καθαρισμός">
                            Καθαρισμός
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Data Table Structure --}}
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 30%">Όνομα Brand</th>
                        <th style="width: 45%">Συνδεδεμένες Κατηγορίες</th>
                        <th class="text-right" style="width: 15%">Ενέργειες</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($brands as $brand)
                        @include('common.brands.index_row', ['brand' => $brand])
                    @empty
                        <tr>
                            <td colspan="4" class="table-empty-state">
                                <i class="fas fa-folder-open empty-icon"></i>
                                <p>Δεν βρέθηκαν καταχωρημένα brands με τα συγκεκριμένα κριτήρια.</p>
                                <a href="{{ route('inventory.brands.create') }}" class="btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Προσθήκη νέου Brand
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($brands->hasPages())
                <div class="table-pagination" id="pagination">
                    {{ $brands->links('pagination::simple') }}
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/brands/index.js') }}"></script>
@endsection