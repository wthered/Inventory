@php use App\Enums\WarehouseType;use App\Models\Warehouse; @endphp
@extends('templates.general')

@section('title', 'Warehouses Management')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/warehouses/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@endsection

@section('content')
    <div class="warehouse-container">
        <header class="warehouse-header">
            <div class="header-main">
                <h1 class="warehouse-title">
                    <span class="warehouse-icon">🏢</span>
                    Warehouse Management
                </h1>
                <p class="warehouse-subtitle">Manage storage locations and inventory distribution</p>
            </div>
            <div class="header-actions">
                @can('create', Warehouse::class)
                    <a href="{{ route('inventory.warehouses.create') }}" class="btn-primary">
                        <span class="btn-icon-plus">+</span> Add Warehouse
                    </a>
                @endcan
            </div>
        </header>

        <div class="card filters-card">
            <div class="card-header">
                <h3 class="card-title">🔍 Filter Warehouses</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.warehouses.index') }}" method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Name, code, city, country...">
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label">Warehouse Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">All Types</option>
                                @foreach(WarehouseType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                    Only
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive Only
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions-bar">
                        <a href="{{ route('inventory.warehouses.index') }}" class="btn-outline">Reset</a>
                        <button type="submit" class="btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header table-header-flex">
                <h3 class="card-title">Warehouse Records ({{ $warehouses->total() }})</h3>
                <div class="table-actions">
                </div>
            </div>
            <div class="card-body no-padding">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Manager</th>
                            <th>Type</th>
                            <th class="text-right">Total Locations (slots)</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td>
                                    <span class="code-value">{{ $warehouse->code }}</span>
                                </td>
                                <td>
                                    <div class="warehouse-name-cell">
                                        <a href="{{ route('inventory.warehouses.show', $warehouse->id) }}" class="record-link">
                                            {{ $warehouse->name }}
                                        </a>
                                        @if($warehouse->isPrimary)
                                            <span class="inline-badge-primary" title="Primary Warehouse">⭐ Primary</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="location-text">📍 {{ $warehouse->city }}, {{ $warehouse->country }}</span>
                                </td>
                                <td>
                                    <span class="manager-text">{{ $warehouse->manager?->account?->full_name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="type-text">{{ $warehouse->type->label() }}</span>
                                </td>
                                <td class="text-right font-numeric">
                                    <div class="capacity-cell-wrapper">
                                        <span class="capacity-numbers">
                                            📍 <strong>{{ $warehouse->locations_count ?? $warehouse->locations()->count() }}</strong>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $warehouse->is_active ? 'active' : 'inactive' }}">
                                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="actions-cell-group">
                                        <a href="{{ route('inventory.warehouses.show', $warehouse->id) }}" class="btn-icon" title="View Details">👁️</a>
                                        @can('update', $warehouse)
                                            <a href="{{ route('inventory.warehouses.edit', $warehouse->id) }}" class="btn-icon edit-icon" title="Edit">✏️</a>
                                        @endcan
                                        @can('delete', $warehouse)
                                            <button type="button" class="btn-icon delete-icon" title="Delete" onclick="openDeleteModal({{ $warehouse->id }}, '{{ addslashes($warehouse->name) }}')">
                                                🗑️
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center no-records">
                                    No warehouse locations matched your filtering criteria.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pagination-wrapper">
            {{ $warehouses->links('vendor.pagination.default_custom') }}
        </div>

        <div class="quick-links-grid">
            <div class="card report-card-hint">
                <div class="card-body">
                    <h4>📊 Warehouse Performance Reports</h4>
                    <p>Analyze layout efficiency, capacity occupancy records, and log activities.</p>
                    <a href="#" class="quick-link-action">Generate Analytics Overview →</a>
                </div>
            </div>
        </div>

        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Confirm Deletion</h3>
                    <button class="modal-close" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to completely drop <strong id=\"deleteWarehouseName\"></strong> from
                        database management systems?</p>
                    <div class="warning-box">
                        ⚠️ Danger: This computational drop process is completely absolute. All structural assignments
                        and related localized stock maps are altered irrevocably.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-outline" onclick="closeModal()">Cancel Procedure</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="application/javascript" src="{{ asset('js/warehouses/index.js') }}"></script>
@endpush