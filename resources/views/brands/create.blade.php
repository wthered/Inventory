@extends('templates.general')

@section('title', 'Δημιουργία Νέου Brand')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/brands/create.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-plus-circle icon-accent"></i> Νέο Brand
                </h1>
                <p class="subtitle">Προσθέστε ένα νέο brand/κατασκευαστή στον κατάλογο του συστήματος.</p>
            </div>

            <div>
                <a href="{{ route('inventory.brands.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Επιστροφή
                </a>
            </div>
        </div>

        {{-- Global Validation Errors Alert --}}
        @if ($errors->any())
            <div class="alert"
                 style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--color-error); color: var(--color-error);">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Παρακαλούμε ελέγξτε τα σφάλματα στη φόρμα πριν την υποβολή.</span>
            </div>
        @endif

        {{-- Main Form Card --}}
        <div class="form-card">
            <form action="{{ route('inventory.brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">

                    {{-- Name Field --}}
                    <div class="form-group full-width">
                        <label for="name" class="form-label">
                            Όνομα Brand <span class="required">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="π.χ. Apple, Nike, Sony"
                               required>
                        @error('name')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Website Field --}}
                    <div class="form-group">
                        <label for="website" class="form-label">Ιστοσελίδα (Website)</label>
                        <input type="url"
                               name="website"
                               id="website"
                               class="form-control @error('website') is-invalid @enderror"
                               value="{{ old('website') }}"
                               placeholder="https://example.com">
                        @error('website')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Logo File Upload --}}
                    <div class="form-group">
                        <label for="logo" class="form-label">Λογότυπο (Logo)</label>
                        <input type="file"
                               name="logo"
                               id="logo"
                               accept="image/*"
                               class="form-control @error('logo') is-invalid @enderror">
                        @error('logo')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Cascading Categories Selects --}}
                    <div class="form-group full-width">
                        <label class="form-label">Συνδεδεμένες Κατηγορίες</label>

                        <div class="category-picker-widget">
                            <div class="category-controls-row">
                                {{-- 1st Level: Parent Category Dropdown --}}
                                <div style="flex: 1;">
                                    <label for="parent_filter"
                                           style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Γονική
                                        Κατηγορία</label>
                                    <select id="parent_filter" class="form-control">
                                        <option value="">-- Επιλέξτε Γονική Κατηγορία --</option>
                                        @foreach($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- 2nd Level: Subcategory Select Dropdown (Populated via AJAX) --}}
                                <div style="flex: 1;">
                                    <label for="subcategory_select"
                                           style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Υποκατηγορία</label>
                                    <select id="subcategory_select" name="category_id"
                                            class="form-control @error('category_id') is-invalid @enderror" disabled>
                                        <option value="">-- Επιλέξτε πρώτα γονική κατηγορία --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @error('category_id')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('categories')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Description Field --}}
                    <div class="form-group full-width">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea name="description"
                                  id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Προσθέστε μια σύντομη περιγραφή για το brand...">{{ old('description') }}</textarea>
                        @error('description')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Is Active Status --}}
                    <div class="form-group full-width">
                        <label class="checkbox-group">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                    {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <span style="font-size: 0.875rem; color: var(--color-text-primary); font-weight: 500;">
                                Ενεργό Brand (Εμφάνιση στο σύστημα)
                            </span>
                        </label>
                    </div>

                </div>

                {{-- Form Actions --}}
                <div class="form-actions">
                    <a href="{{ route('inventory.brands.index') }}" class="btn-secondary">Ακύρωση</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Αποθήκευση Brand
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/brands/create.js') }}"></script>
@endsection