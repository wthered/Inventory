@extends('templates.general')

@section('title', 'Επεξεργασία Brand: ' . $brand->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/brands/index.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1 style="display: flex; align-items: center; gap: var(--space-sm); flex-wrap: wrap;">
                    <i class="fas fa-pencil-alt icon-accent"></i>
                    Επεξεργασία Brand: {{ $brand->name }}

                    @if($brand->trashed())
                        <span class="badge-trashed">
                            <i class="fas fa-trash-alt"></i> Διαγραμμένο / Αρχειοθετημένο
                        </span>
                    @endif
                </h1>
                <p class="subtitle">Τροποποιήστε τα στοιχεία του Brand και διαχειριστείτε τις συνδεδεμένες
                    κατηγορίες.</p>
            </div>
            <div>
                <a href="{{ route('inventory.brands.index') }}" class="btn-action btn-edit"
                   style="width: auto; padding: 0 var(--space-md); height: 35px; display: flex; align-items: center;">
                    <i class="fas fa-arrow-left"></i> Επιστροφή στη λίστα
                </a>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="table-card" style="padding: var(--space-xl); background-color: var(--color-surface);">
            <form action="{{ route('inventory.brands.update', $brand->id) }}" method="POST"
                  enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: var(--space-lg);">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-md);">
                    {{-- Brand Name --}}
                    <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
                        <label for="name"
                               style="font-size: 0.85rem; color: var(--color-text-secondary); font-weight: 600;">Όνομα
                            Brand <span style="color: var(--color-error);">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required
                               style="background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.625rem; color: var(--color-text-primary); font-size: 0.9rem;">
                        @error('name') <span
                                style="color: var(--color-error); font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>

                    {{-- Website --}}
                    <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
                        <label for="website"
                               style="font-size: 0.85rem; color: var(--color-text-secondary); font-weight: 600;">Ιστοσελίδα
                            (URL)</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $brand->website) }}"
                               placeholder="https://example.com"
                               style="background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.625rem; color: var(--color-text-primary); font-size: 0.9rem;">
                        @error('website') <span
                                style="color: var(--color-error); font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
                    <label for="description"
                           style="font-size: 0.85rem; color: var(--color-text-secondary); font-weight: 600;">Περιγραφή</label>
                    <textarea name="description" id="description" rows="4"
                              style="background-color: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.625rem; color: var(--color-text-primary); font-size: 0.9rem; resize: vertical;">{{ old('description', $brand->description) }}</textarea>
                    @error('description') <span
                            style="color: var(--color-error); font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-md); align-items: center;">
                    {{-- Logo File Input & Preview Box --}}
                    <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
                        <label for="logo"
                               style="font-size: 0.85rem; color: var(--color-text-secondary); font-weight: 600;">Λογότυπο
                            Brand</label>

                        <div style="display: flex; align-items: center; gap: var(--space-md); background-color: var(--color-bg-alt); padding: var(--space-sm); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                            {{-- Current Logo Preview --}}
                            <div style="width: 60px; height: 60px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); background-color: var(--color-surface); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}"
                                         alt="{{ $brand->name }} logo"
                                         style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <i class="fas fa-image style-placeholder"
                                       style="font-size: 1.5rem; color: var(--color-text-muted);"></i>
                                @endif
                            </div>

                            {{-- File Input Details --}}
                            <div style="display: flex; flex-direction: column; gap: 4px; flex-grow: 1;">
                                <input type="file" name="logo" id="logo" accept="image/*"
                                       style="color: var(--color-text-primary); font-size: 0.85rem; width: 100%;">
                                <span style="font-size: 0.75rem; color: var(--color-text-muted);">Επιτρεπόμενα αρχεία: JPG, PNG, WEBP (Max 2MB)</span>
                            </div>
                        </div>
                        @error('logo') <span
                                style="color: var(--color-error); font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Toggle --}}
                    <div style="display: flex; align-items: center; gap: var(--space-sm); margin-top: var(--space-md);">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; cursor: pointer;">
                        <label for="is_active"
                               style="font-size: 0.9rem; color: var(--color-text-primary); cursor: pointer; font-weight: 500;">Κατάσταση
                            (Ενεργό)</label>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--color-border); margin: var(--space-md) 0;">

                {{-- Many-to-Many Σύνδεση με Κατηγορίες --}}
                <div>
                    <h3 style="font-size: 1.1rem; color: var(--color-text-primary); font-weight: 600; margin-bottom: var(--space-sm);">
                        <i class="fas fa-tags icon-accent" style="font-size: 0.95rem;"></i> Αντιστοίχηση σε
                        Υποκατηγορίες
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: var(--space-md);">
                        Επιλέξτε τις υποκατηγορίες στις οποίες διατίθενται τα προϊόντα αυτού του κατασκευαστή.
                    </p>

                    {{-- Scrollable Grid με όλες τις Υποκατηγορίες ομαδοποιημένες ανά Parent --}}
                    <div style="max-height: 350px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-md); background-color: var(--color-bg-alt); display: flex; flex-direction: column; gap: var(--space-md);">
                        @foreach($parentCategories as $parent)
                            <div>
                                {{-- Parent Header --}}
                                <h4 style="font-size: 0.85rem; color: var(--color-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: var(--space-xs); border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 2px;">
                                    {{ $parent->name }}
                                </h4>

                                {{-- Children Checkboxes --}}
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: var(--space-sm); padding: var(--space-xs) 0;">
                                    @forelse($parent->children as $child)
                                        <label style="display: flex; align-items: center; gap: var(--space-sm); color: var(--color-text-secondary); font-size: 0.875rem; cursor: pointer; padding: var(--space-xs); border-radius: var(--radius-sm); transition: background-color 0.1s;">
                                            <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                                                   {{ in_array($child->id, old('categories', $brand->categories->pluck('id')->toArray())) ? 'checked' : '' }}
                                                   style="width: 15px; height: 15px; cursor: pointer;">
                                            <span>{{ $child->name }}</span>
                                        </label>
                                    @empty
                                        <span style="font-size: 0.8rem; color: var(--color-text-muted); font-style: italic; padding-left: var(--space-xs);">Καμία υποκατηγορία</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Form Actions Buttons --}}
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md); margin-top: var(--space-md); border-top: 1px solid var(--color-border); padding-top: var(--space-lg);">
                    <a href="{{ route('inventory.brands.index') }}" class="btn-action btn-edit"
                       style="width: auto; padding: 0 var(--space-md); height: 40px; display: flex; align-items: center;">
                        Ακύρωση
                    </a>
                    <button type="submit" class="btn-primary" style="height: 40px; padding: 0 var(--space-lg);">
                        <i class="fas fa-save"></i>
                        @if($brand->trashed())
                            Ενημέρωση & Διατήρηση σε Αρχειοθέτηση
                        @else
                            Αποθήκευση Αλλαγών
                        @endif
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection