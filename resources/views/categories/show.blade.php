@extends('templates.general')

@section('title', 'Category Details: ' . $category->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/categories/index.css') }}">
@endsection

@section('content')
    <div class="page-container">

        <!-- Header Controls -->
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-folder-open icon-accent"></i>
                    {{ $category->name }}
                </h1>
                <p class="subtitle">
                    {{ $category->parent_id ? 'Subcategory of ' . ($category->parent->name ?? '#'.$category->parent_id) : 'Root Category' }}
                </p>
            </div>

            <div class="action-buttons-group" style="display: flex; gap: var(--space-xs); align-items: center;">
                <a href="{{ route('inventory.categories.index') }}" class="btn-primary btn-sm"
                   style="background-color: var(--color-border); color: var(--color-text);">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                @can('category.update')
                    <a href="{{ route('inventory.categories.edit', $category->id) }}" class="btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Category
                    </a>
                @endcan
                @can('category.delete')
                    <form action="{{ route('inventory.categories.destroy', $category->id) }}" method="POST"
                          class="inline-form"
                          onsubmit="return confirm('Are you sure you want to delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-primary btn-sm"
                                style="background-color: var(--color-error); border-color: var(--color-error);">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Description Block -->
        <div class="table-card" style="padding: var(--space-md); margin-bottom: var(--space-md);">
            <h3 style="color: var(--color-text-muted); font-size: 0.75rem; text-transform: uppercase; margin-bottom: var(--space-xs);">
                Description</h3>
            <p style="color: var(--color-text-secondary); margin: 0;">{{ $category->description ?? 'No description provided for this category.' }}</p>
        </div>

        <!-- Subcategories List -->
        @if($category->children->count() > 0)
            <div class="header-title-group" style="margin-top: var(--space-md);">
                <h2><i class="fas fa-tags icon-accent" style="font-size: 1.25rem;"></i> Subcategories</h2>
            </div>
            <div class="table-card" style="margin-bottom: var(--space-md);">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-right" style="width: 150px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($category->children as $child)
                            <tr>
                                <td class="td-id">#{{ $child->id }}</td>
                                <td class="td-name"><span class="tree-line">└─</span> {{ $child->name }}</td>
                                <td class="td-description">{{ Str::limit($child->description ?? 'No description.', 60) }}</td>
                                <td class="text-right">
                                    <div class="action-buttons-group">
                                        <a href="{{ route('inventory.categories.show', $child->id) }}"
                                           class="btn-action btn-show" title="View"><i class="fas fa-eye"></i></a>
                                        @can('category.update')
                                            <a href="{{ route('inventory.categories.edit', $child->id) }}"
                                               class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('category.delete')
                                            <form action="{{ route('inventory.categories.destroy', $child->id) }}"
                                                  method="POST" class="inline-form"
                                                  onsubmit="return confirm('Delete this subcategory?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete" title="Delete"><i
                                                            class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Associated Products Section -->
        <div class="header-title-group" style="margin-top: var(--space-md);">
            <h2><i class="fas fa-boxes icon-accent" style="font-size: 1.25rem;"></i> Products in this Category</h2>
        </div>

        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Stock</th>
                        <th class="text-right" style="width: 120px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="td-id">#{{ $product->id }}</td>
                            <td class="td-name"
                                style="font-family: monospace; color: var(--color-text-muted);">{{ $product->sku ?? 'N/A' }}</td>
                            <td class="td-name">{{ $product->name }}</td>
                            <td>
                                <span class="badge-count">{{ $product->current_stock ?? 0 }}</span>
                            </td>
                            <td class="text-right">
                                <div class="action-buttons-group">
                                    <a href="{{ route('inventory.products.show', $product->id) }}"
                                       class="btn-action btn-show" title="View Product"><i class="fas fa-eye"></i></a>
                                    @can('product.update')
                                        <a href="{{ route('inventory.products.edit', $product->id) }}"
                                           class="btn-action btn-edit" title="Edit Product"><i class="fas fa-edit"></i></a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty-state">
                                <i class="fas fa-box-open empty-icon"></i>
                                <p>No products are currently assigned to this category.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="table-pagination">
                    {{ $products->links('pagination::simple') }}
                </div>
            @endif
        </div>
    </div>
@endsection