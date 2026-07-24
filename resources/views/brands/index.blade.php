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
                            style="width: 100%; background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.5rem 0.75rem; color: var(--color-text-primary); font-size: 0.875rem; cursor: pointer;" {{ !request('parent_category_id') ? 'disabled' : '' }}>
                        <option value="">Όλες οι υποκατηγορίες</option>
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
                        <tr>
                            <td class="td-id">#{{ $brand->id }}</td>
                            <td class="td-name">{{ $brand->name }}</td>
                            <td>
                                @forelse($brand->categories as $category)
                                    <a href="{{ route('inventory.categories.show', ['category' => $category->id]) }}"
                                       class="badge-count"
                                       style="margin-right: var(--space-xs); margin-bottom: var(--space-xs);">
                                        {{ $category->name }}
                                    </a>
                                @empty
                                    <span class="td-description">—</span>
                                @endforelse
                            </td>

                            <td class="text-right">
                                <div class="action-buttons-group">
                                    {{-- Κουμπί Προβολής (Show) --}}
                                    <a href="{{ route('inventory.brands.show', $brand->id) }}"
                                       class="btn-action btn-show" title="Προβολή">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Κουμπί Επεξεργασίας (Edit) --}}
                                    <a href="{{ route('inventory.brands.edit', $brand->id) }}"
                                       class="btn-action btn-edit" title="Επεξεργασία">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>

                                    {{-- Κουμπί Διαγραφής (Delete) --}}
                                    <form action="{{ route('inventory.brands.destroy', $brand->id) }}" method="POST"
                                          class="inline-form"
                                          onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το brand;');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Διαγραφή">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
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
                <div class="table-pagination">
                    {{ $brands->links('pagination::simple') }}
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('parent_category_id').addEventListener('change', function () {
            const parentId = this.value;
            const childrenSelect = document.getElementById('category_id');

            // Επαναφορά του child select
            childrenSelect.innerHTML = '<option value="">Όλες οι υποκατηγορίες</option>';

            if (!parentId) {
                childrenSelect.disabled = true;
                return;
            }

            // Κάνουμε fetch τις υποκατηγορίες από το endpoint σας
            fetch('/categories/children', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Grabs the CSRF token from the meta tag in your general template
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    category_id: parentId // sending the categoryID in the request body
                })
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            }).then(data => {
                data.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.id;
                    option.textContent = child.name;
                    childrenSelect.appendChild(option);
                });
                childrenSelect.disabled = false;
            }).catch(error => console.error('Error fetching subcategories:', error));
        });
    </script>
@endsection