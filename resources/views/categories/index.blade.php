@extends('templates.general')

@section('title', 'Categories Management')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/categories/index.css') }}">
@endsection

@section('content')
    <div class="page-container">

        <div class="page-header">
            <div class="header-title-group">
                <h1><i class="fas fa-tags icon-accent"></i> Categories Management</h1>
                <p class="subtitle">Manage your inventory structure via hierarchical root and child categories.</p>
            </div>

            @can('category.create')
                <a href="{{ route('inventory.categories.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="width: 50px;"></th> {{-- Για το chevron icon --}}
                        <th style="width: 80px;">ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products Count</th>
                        <th class="text-right" style="width: 150px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <!-- Root Category Row -->
                        <tr class="root-row {{ $category->children->count() > 0 ? 'has-children' : '' }}"
                            data-id="{{ $category->id }}">
                            <td class="td-toggle">
                                @if($category->children->count() > 0)
                                    <i class="fas fa-chevron-right toggle-icon"></i>
                                @endif
                            </td>
                            <td class="td-id">#{{ $category->id }}</td>
                            <td class="td-name">{{ $category->name }}</td>
                            <td class="td-description">
                                {{ Str::limit($category->description ?? 'No description provided.', 60) }}
                            </td>
                            <td>
                                <span class="badge-count">{{ $category->products_count }}</span>
                            </td>
                            <td class="text-right">
                                <div class="action-buttons-group">
                                    @can('category.view')
                                        <a href="{{ route('inventory.categories.show', $category->id) }}"
                                           class="btn-action btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('category.update')
                                        <a href="{{ route('inventory.categories.edit', $category->id) }}"
                                           class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                    @endcan

                                    @can('category.delete')
                                        <form action="{{ route('inventory.categories.destroy', $category->id) }}"
                                              method="POST" class="inline-form"
                                              onsubmit="return confirm('Delete this root category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete"><i
                                                        class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Child Categories Rows -->
                        @foreach($category->children as $child)
                            <tr class="child-row child-of-{{ $category->id }} hidden" data-parent="{{ $category->id }}">
                                <td></td> {{-- Κενό κάτω από το chevron --}}
                                <td class="td-id">#{{ $child->id }}</td>
                                <td class="td-name child-indent">
                                    <span class="tree-line">└─</span> {{ $child->name }}
                                </td>
                                <td class="td-description">
                                    {{ Str::limit($child->description ?? 'No description provided.', 60) }}
                                </td>
                                <td>
                                    <span class="badge-count">{{ $child->products_count }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="action-buttons-group">
                                        @can('category.view')
                                            <a href="{{ route('inventory.categories.show', $child->id) }}"
                                               class="btn-action btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan

                                        @can('category.update')
                                            <a href="{{ route('inventory.categories.edit', $category->id) }}"
                                               class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                        @endcan

                                        @can('category.delete')
                                            <form action="{{ route('inventory.categories.destroy', $child->id) }}"
                                                  method="POST" class="inline-form"
                                                  onsubmit="return confirm('Delete this subcategory?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete"><i
                                                            class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="table-empty-state">
                                <i class="fas fa-folder-open empty-icon"></i>
                                <p>No categories found in the database.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="table-pagination">
                    {{ $categories->links('pagination::simple') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/categories/index.js') }}"></script>
@endsection