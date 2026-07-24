@extends('templates.general')

@section('title', 'Edit Category: ' . $category->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/categories/edit.css') }}">
@endsection

@section('content')
    <div class="page-container">

        <!-- Header Controls -->
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-edit icon-accent"></i>
                    Edit Category Settings
                </h1>
                <p class="subtitle">Modifying structure parameters for #{{ $category->id }} — {{ $category->name }}</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.categories.show', $category->id) }}" class="btn-primary btn-sm"
                   style="background-color: var(--color-border);">
                    <i class="fas fa-arrow-left"></i> Cancel & View
                </a>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="form-card">
            <form action="{{ route('inventory.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Category Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $category->name) }}"
                           placeholder="e.g., Electronics, Warehousing Hardware"
                           required>
                    <div class="validation-error-space">
                        @error('name')
                        <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Hierarchical Parent Assignment -->
                <div class="form-group">
                    <label for="parent_id" class="form-label">Parent Category Relation</label>
                    <select name="parent_id" id="parent_id"
                            class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">-- No Parent (Set as Root Category) --</option>
                        @foreach($parentCategories as $parent)
                            {{-- Prevent assigning itself or its children as a parent to avoid infinite loops --}}
                            @if($parent->id !== $category->id)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id')
                    <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- Description Block -->
                <div class="form-group">
                    <label for="description" class="form-label">Description / Internal Context Notes</label>
                    <textarea name="description"
                              id="description"
                              rows="4"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Describe the target inventory range for this structure division...">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                    <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>


                <!-- Brand Relationship Mapping -->
                <div class="form-group" style="margin-top: var(--space-md);">
                    <label class="form-label">Linked Brands Association</label>
                    <p class="subtitle"
                       style="margin-bottom: var(--space-xs); font-size: 0.8rem; color: var(--color-text-muted);">
                        Select which product manufacturing brands fall under this specific operational classification
                        node.
                    </p>

                    <!-- Απλό Vanilla Text Filter -->
                    <div style="margin-bottom: var(--space-sm);">
                        <input type="text"
                               id="brand-search-filter"
                               class="form-control"
                               placeholder="🔍 Αναζήτηση μάρκας (όνομα ή slug)..."
                               style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                    </div>

                    <div class="brands-grid-container" id="brands-grid">
                        @foreach($brands as $brand)
                            <label class="checkbox-item brand-item">
                                <input type="checkbox"
                                       name="brands[]"
                                       value="{{ $brand->id }}"
                                        {{ in_array($brand->id, old('brands', $linkedBrandIds)) ? 'checked' : '' }}>
                                <span class="brand-name" title="{{ $brand->slug }}">{{ $brand->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('brands')
                    <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
                @error('brands')
                <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror

                <!-- Action Button Footers -->
                <div class="form-actions-row">
                    <a href="{{ route('inventory.categories.index') }}" class="btn-action"
                       style="padding: 0 var(--space-md); width: auto; color: var(--color-text-muted);"
                       title="Discard Changes">
                        Discard
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save System Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/categories/edit.js') }}"></script>
@endsection