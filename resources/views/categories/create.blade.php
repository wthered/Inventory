@extends('templates.general')

@section('title', 'Create New Category Structure')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/categories/create.css') }}">
@endsection

@section('content')
    <div class="page-container">

        <!-- Header Controls -->
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-folder-plus icon-accent"></i>
                    Create Category Structure
                </h1>
                <p class="subtitle">Establish a new relational division matrix node within the system layout</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.categories.index') }}" class="btn-primary btn-sm"
                   style="background-color: var(--color-border);">
                    <i class="fas fa-arrow-left"></i> Cancel & Return
                </a>
            </div>
        </div>

        <!-- Form Card Container -->
        <div class="form-card">
            <form action="{{ route('inventory.categories.store') }}" method="POST">
                @csrf

                <!-- Category Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
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
                            <option value="{{ $parent->id }}" {{ old('parent_id', request('parent_id')) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="validation-error-space">
                        @error('parent_id')
                        <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Description Block -->
                <div class="form-group">
                    <label for="description" class="form-label">Description / Internal Context Notes</label>
                    <textarea name="description"
                              id="description"
                              rows="4"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Describe the target inventory range for this structure division...">{{ old('description') }}</textarea>
                    <div class="validation-error-space">
                        @error('description')
                        <span class="text-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Action Button Footers -->
                <div class="form-actions-row">
                    <a href="{{ route('inventory.categories.index') }}" class="btn-action"
                       style="padding: 0 var(--space-md); width: auto; color: var(--color-text-muted);"
                       title="Discard Changes">
                        Discard
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus"></i> Initialize Category Node
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection