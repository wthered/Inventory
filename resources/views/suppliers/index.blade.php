@extends('templates.general')

@section('title', 'Suppliers Directory')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/suppliers/index.css') }}"/>
@endsection

@section('content')
    <div class="page-container">

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-truck icon-accent"></i> Suppliers
                </h1>
                <p class="subtitle">Manage product suppliers, vendor contacts, and ordering details</p>
            </div>
            <a href="{{ route('inventory.suppliers.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i> Add New Supplier
            </a>
        </div>

        <!-- Feedback Message -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search / Filter Toolbar -->
        <div class="toolbar-card">
            <form method="GET" action="{{ route('inventory.suppliers.index') }}" class="search-form">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           class="search-input"
                           placeholder="Search suppliers by name, code, contact person, email, or phone..."
                           autocomplete="off">
                    @if(!empty($search))
                        <a href="{{ route('inventory.suppliers.index') }}" class="search-clear-btn"
                           title="Clear Search">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Data Table Container -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Supplier / Company</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="td-id">{{ $supplier->code }}</td>
                            <td class="td-name">
                                <strong>{{ $supplier->company_name ?? $supplier->name }}</strong>
                                @if($supplier->company_name && $supplier->name !== $supplier->company_name)
                                    <br><small class="td-description">{{ $supplier->name }}</small>
                                @endif
                            </td>
                            <td>{{ $supplier->contact_person ?? '—' }}</td>
                            <td>
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="supplier-contact-link">
                                        <i class="fas fa-envelope"></i> {{ $supplier->email }}
                                    </a>
                                @else
                                    <span class="td-description">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="supplier-contact-link">
                                    <i class="fas fa-phone"></i> {{ $supplier->phone }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('inventory.suppliers.show', $supplier->id) }}" class="badge-count"
                                   title="View associated products">
                                    {{ $supplier->products_count }} {{ Str::plural('product', $supplier->products_count) }}
                                </a>
                            </td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge-status badge-status-active">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge-status badge-status-inactive">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="action-buttons-group">
                                    <a href="{{ route('inventory.suppliers.show', $supplier->id) }}"
                                       class="btn-action btn-show"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.suppliers.edit', $supplier->id) }}"
                                       class="btn-action btn-edit"
                                       title="Edit Supplier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('inventory.suppliers.destroy', $supplier->id) }}"
                                          method="POST"
                                          class="inline-form"
                                          onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Delete Supplier">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-state">
                                <i class="fas fa-truck-loading empty-icon"></i>
                                <p>No suppliers found matching your search.</p>
                                <a href="{{ route('inventory.suppliers.create') }}" class="btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Supplier
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            @if($suppliers->hasPages())
                <div class="table-pagination">
                    {{ $suppliers->links('pagination::simple') }}
                </div>
            @endif
        </div>

    </div>
@endsection