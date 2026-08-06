@extends('templates.general')

@section('title', 'Customers Directory')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/customers/index.css') }}"/>
@endsection

@section('content')
    <div class="page-container">

        <!-- Header Section -->
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="fas fa-users icon-accent"></i> Customer Management
                </h1>
                <p class="subtitle">View, search, and manage your customer accounts</p>
            </div>

            @can('customer.create')
                <a href="{{ route('inventory.customers.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Customer
                </a>
            @endcan
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Filters & Search Toolbar -->
        <div class="toolbar-card">
            <form action="{{ route('inventory.customers.index') }}" method="GET" class="toolbar-form">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name, email, phone, code..."
                           aria-label="Search customers"
                           class="form-control">
                </div>

                <div class="filter-group">
                    <select name="status" class="form-control select-filter" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>

                    <button type="submit" class="btn-secondary">Filter</button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('inventory.customers.index') }}" class="btn-reset" title="Clear Filters">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Customers Data Table -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer / Company</th>
                        <th>Contact Info</th>
                        <th>City / Country</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="font-mono">{{ $customer->code }}</td>
                            <td>
                                <div class="cell-primary font-weight-600">
                                    {{ $customer->name }}
                                </div>
                                @if($customer->company_name)
                                    <div class="cell-secondary">{{ $customer->company_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $customer->phone }}</div>
                                @if($customer->email)
                                    <div class="cell-secondary">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td>
                                @if($customer->city || $customer->country)
                                    {{ implode(', ', array_filter([$customer->city, $customer->country])) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right actions-cell">
                                <div class="action-buttons-group">
                                    @can('customer.view')
                                        <a href="{{ route('inventory.customers.show', $customer->id) }}"
                                           class="btn-action btn-show"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('customer.update')
                                        <a href="{{ route('inventory.customers.edit', $customer->id) }}"
                                           class="btn-action btn-edit"
                                           title="Edit Customer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-user-slash empty-icon"></i>
                                <p>No customers found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="table-pagination">
                    {{ $customers->links('pagination::simple') }}
                </div>
            @endif
        </div>

    </div>
@endsection