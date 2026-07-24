@extends('templates.general')

@section('title', 'Προβολή Brand: ' . $brand->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/brands/index.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Page Header & Actions --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-trademark icon-accent"></i> {{ $brand->name }}
                </h1>
                <p class="subtitle">Αναλυτικά στοιχεία και συνδεδεμένες κατηγορίες του κατασκευαστή.</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.brands.index') }}" class="btn-action btn-edit"
                   style="width: auto; padding: 0 var(--space-md);" title="Επιστροφή στη Λίστα">
                    <i class="fas fa-arrow-left"></i> Επιστροφή
                </a>

                <a href="{{ route('inventory.brands.edit', $brand->id) }}" class="btn-primary btn-sm"
                   style="height: 32px;">
                    <i class="fas fa-pencil-alt"></i> Επεξεργασία
                </a>
            </div>
        </div>

        {{-- Main Entity Details Area --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-lg);">

            {{-- Left Card: Brand General Info --}}
            <div class="table-card"
                 style="padding: var(--space-lg); display: flex; flex-direction: column; gap: var(--space-md);">
                <div style="display: flex; align-items: center; gap: var(--space-md); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md);">
                    {{-- Logo Showcase (Fallback αν δεν υπάρχει) --}}
                    <div style="width: 64px; height: 64px; background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        @if($brand->logo)
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        @else
                            <i class="fas fa-image" style="font-size: 1.5rem; color: var(--color-text-muted);"></i>
                        @endif
                    </div>
                    <div>
                        <h3 style="color: var(--color-text-primary); font-weight: 600;">{{ $brand->name }}</h3>
                        <span class="td-description">Slug: <code>{{ $brand->slug }}</code></span>
                    </div>
                </div>

                {{-- Info Fields --}}
                <div style="display: flex; flex-direction: column; gap: var(--space-sm); font-size: 0.9rem;">
                    <div>
                        <span style="color: var(--color-text-muted); font-weight: 500;">Κατάσταση:</span>
                        @if($brand->is_active)
                            <span style="color: var(--color-accent); font-weight: 600; margin-left: var(--space-xs);">
                            <i class="fas fa-check-circle"></i> Ενεργό
                        </span>
                        @else
                            <span style="color: var(--color-error); font-weight: 600; margin-left: var(--space-xs);">
                            <i class="fas fa-times-circle"></i> Ανενεργό
                        </span>
                        @endif
                    </div>

                    @if($brand->website)
                        <div>
                            <span style="color: var(--color-text-muted); font-weight: 500;">Ιστοσελίδα:</span>
                            <a href="{{ $brand->website }}" target="_blank"
                               style="color: var(--color-primary); text-decoration: none; margin-left: var(--space-xs);">
                                {{ $brand->website }} <i class="fas fa-external-link-alt"
                                                         style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    @endif

                    <div>
                        <span style="color: var(--color-text-muted); font-weight: 500;">Ημ. Δημιουργίας:</span>
                        <span style="color: var(--color-text-secondary); margin-left: var(--space-xs);">
                        {{ $brand->created_at ? $brand->created_at->format('d/m/Y H:i') : '—' }}
                    </span>
                    </div>
                </div>
            </div>

            {{-- Right Card: Description --}}
            <div class="table-card" style="padding: var(--space-lg);">
                <h4 style="color: var(--color-text-primary); font-weight: 600; margin-bottom: var(--space-sm); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-sm);">
                    Περιγραφή
                </h4>
                <p style="color: var(--color-text-secondary); font-size: 0.925rem; line-height: 1.6;">
                    {{ $brand->description ?? 'Δεν υπάρχει διαθέσιμη περιγραφή για το συγκεκριμένο brand.' }}
                </p>
            </div>
        </div>

        {{-- Connected Categories Section (Many-to-Many Rel) --}}
        <div class="table-card" style="padding: var(--space-lg);">
            <h4 style="color: var(--color-text-primary); font-weight: 600; margin-bottom: var(--space-md);">
                <i class="fas fa-tags icon-accent" style="font-size: 1rem;"></i> Συνδεδεμένες Κατηγορίες
                ({{ $brand->categories->count() }})
            </h4>

            <div style="display: flex; flex-wrap: wrap; gap: var(--space-sm);">
                @forelse($brand->categories as $category)
                    <a href="{{ route('inventory.categories.show', ['category' => $category->id]) }}"
                       class="badge-count"
                       style="padding: 0.4rem 0.8rem; font-size: 0.825rem;">
                        {{ $category->name }}
                    </a>
                @empty
                    <p class="td-description" style="font-style: italic;">Το συγκεκριμένο brand δεν έχει αντιστοιχιστεί
                        σε καμία κατηγορία ακόμα.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection