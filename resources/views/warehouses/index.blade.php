@extends('templates.general')

@section('title', 'Warehouses Management')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/warehouses/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/warehouses/pagination.css') }}" />
@endsection

@section('content')
    <div class="warehouse-container" id="box">
        <!-- Header -->
        <div class="warehouse-header">
            <div>
                <h1 class="warehouse-title">
                    <span class="warehouse-icon">🏢</span>
                    Warehouse Management
                </h1>
                <p class="warehouse-subtitle">Manage storage locations and inventory distribution</p>
            </div>
            <div class="header-actions">
                @can('create', \App\Models\Warehouse::class)
                    <a href="{{ route('inventory.warehouses.warehouse.create') }}" class="btn-primary">
                        <span class="btn-icon">+</span>
                        Add Warehouse
                    </a>
                @endcan
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="card filters-card">
            <div class="card-header">
                <h3 class="card-title">Filter Warehouses</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.warehouses.warehouse.index') }}" method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search" class="form-label">Search</label>
                            <div class="search-input-group">
                                <span class="search-icon">🔍</span>
                                <input type="text"
                                       id="search"
                                       name="search"
                                       class="form-control search-input"
                                       placeholder="Search by name, code, location..."
                                       value="{{ request('search') }}">
                                <button type="submit" class="search-button">Search</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sort" class="form-label">Sort By</label>
                            <select id="sort" name="sort" class="form-select">
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                                <option value="capacity" {{ request('sort') == 'capacity' ? 'selected' : '' }}>Capacity</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <div class="filter-buttons">
                                <button type="submit" class="btn-secondary">
                                    Apply Filters
                                </button>
                                <a href="{{ route('inventory.warehouses.warehouse.index') }}" class="btn-outline">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-info">
                        <span class="stat-label">Total Warehouses</span>
                        <span class="stat-value">4</span>
                    </div>
                    <div class="stat-icon">
                        <span>🏭</span>
                    </div>
                </div>
                <div class="stat-trend up">+2 this month</div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-info">
                        <span class="stat-label">Active Warehouses</span>
                        <span class="stat-value">{{ $warehouses->where('is_active', true)->count() }}</span>
                    </div>
                    <div class="stat-icon">
                        <span>✅</span>
                    </div>
                </div>
                <div class="stat-trend">Operational</div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-info">
                        <span class="stat-label">Total Capacity</span>
                        <span class="stat-value">{{ number_format($warehouses->sum('capacity')) }}</span>
                        <span class="stat-unit">sq.ft</span>
                    </div>
                    <div class="stat-icon">
                        <span>📏</span>
                    </div>
                </div>
                <div class="stat-trend">Available space</div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-info">
                        <span class="stat-label">Avg Occupancy</span>
                        <span class="stat-value">78%</span>
                    </div>
                    <div class="stat-icon">
                        <span>📊</span>
                    </div>
                </div>
                <div class="stat-trend down">-2% from last month</div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card">
            <div class="card-header table-header">
                <h3 class="card-title">Warehouse List</h3>
                <div class="table-actions">
                    <div class="bulk-actions">
                        <select id="bulkAction" class="form-select bulk-select">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button id="applyBulkAction" class="btn-outline">Apply</button>
                    </div>
                    <button class="btn-outline" onclick="exportTable()">
                        <span class="btn-icon">📥</span>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="warehouse-table">
                    <thead>
                    <tr>
                        <th class="checkbox-col">
                            <input type="checkbox" id="selectAll" class="table-checkbox">
                        </th>
                        <th>Warehouse</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Inventory</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr class="warehouse-row">
                            <td class="checkbox-col">
                                <input type="checkbox" value="{{ $warehouse->id }}" class="table-checkbox row-checkbox">
                            </td>
                            <td>
                                <div class="warehouse-info">
                                    <div class="warehouse-avatar">
                                        {{ substr($warehouse->name, 0, 2) }}
                                    </div>
                                    <div class="warehouse-details">
                                        <div class="warehouse-name">{{ $warehouse->name }}</div>
                                        <div class="warehouse-code">{{ $warehouse->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <div class="location-text">{{ $warehouse->city }}, {{ $warehouse->country }}</div>
                                    <div class="location-address">{{ Str::limit($warehouse->address, 30) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="capacity-container">
                                    <div class="capacity-bar">
                                        <div class="capacity-fill" style="width: {{ min(100, ($warehouse->current_capacity / max(1, $warehouse->capacity)) * 100) }}%"></div>
                                    </div>
                                    <div class="capacity-text">
                                        {{ number_format($warehouse->current_capacity) }} / {{ number_format($warehouse->capacity) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="inventory-count">
                                    <span class="count-badge">{{ $warehouse->inventory_items_count ?? 0 }}</span>
                                    <span class="count-label">items</span>
                                </div>
                            </td>
                            <td>
                            <span class="status-badge status-{{ $warehouse->deleted_at ? 'inactive' : 'active' }}">
                                {{ $warehouse->deleted_at ? 'Inactive' : 'Active' }}
                                @if($warehouse->under_maintenance)
                                    <span class="maintenance-indicator">⚠️</span>
                                @endif
                            </span>
                            </td>
                            <td>
                                <div class="timestamp">
                                    <div class="time-ago">{{ $warehouse->updated_at->diffForHumans() }}</div>
                                    <div class="date">{{ $warehouse->updated_at->format('M d, Y') }}</div>
                                </div>
                            </td>
                            <td class="actions-col">
                                <div class="action-buttons">
                                    <a href="{{ route('inventory.warehouses.warehouse.show', $warehouse) }}" class="action-btn view-btn" title="View">
                                        👁️
                                    </a>
                                    @can('update', $warehouse)
                                        <a href="{{ route('inventory.warehouses.warehouse.edit', $warehouse) }}" class="action-btn edit-btn" title="Edit">
                                            ✏️
                                        </a>
                                    @endcan
                                    @can('delete', $warehouse)
                                        <button class="action-btn delete-btn"
                                                title="Delete"
                                                onclick="showDeleteModal('{{ $warehouse->id }}', '{{ $warehouse->name }}')">
                                            🗑️
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="empty-content">
                                    <div class="empty-icon">🏢</div>
                                    <h3>No Warehouses Found</h3>
                                    <p>Get started by creating your first warehouse</p>
                                    @can('create', \App\Models\Warehouse::class)
                                        <a href="{{ route('inventory.warehouses.warehouse.create') }}" class="btn-primary">
                                            Create Warehouse
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($warehouses->hasPages())
                <div class="table-footer">
                    <div class="pagination-info">
                        Showing {{ $warehouses->firstItem() ?? 0 }} to {{ $warehouses->lastItem() ?? 0 }} of 5 warehouses
                    </div>
                    <div class="pagination-controls">
                        {{ $warehouses->links('vendor.pagination.simple') }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
            <div class="quick-link-card">
                <div class="quick-link-icon">📦</div>
                <div class="quick-link-content">
                    <h4>Inventory Management</h4>
                    <p>View and manage all inventory items</p>
                    <a href="{{ route('inventory.inventory.index') }}" class="quick-link-btn">Go to Inventory →</a>
                </div>
            </div>

            <div class="quick-link-card">
                <div class="quick-link-icon">🔄</div>
                <div class="quick-link-content">
                    <h4>Transfers</h4>
                    <p>Manage transfers between warehouses</p>
                    <a href="#" class="quick-link-btn">View Transfers from view transfers.index →</a>
                </div>
            </div>

            <div class="quick-link-card">
                <div class="quick-link-icon">📈</div>
                <div class="quick-link-content">
                    <h4>Reports</h4>
                    <p>View warehouse performance reports</p>
                    <a href="#" class="quick-link-btn">Generate Report from reports.warehouses →</a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Confirm Deletion</h3>
                    <button class="modal-close" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteWarehouseName"></strong>?</p>
                    <div class="warning-box">
                        ⚠️ This action cannot be undone. All associated inventory will be removed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-outline" onclick="closeModal()">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete Warehouse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="application/javascript" src="{{ asset('js/warehouses/index.js') }}"></script>
@endpush