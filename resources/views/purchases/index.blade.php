@extends('templates.general')

@section('title', 'Purchase Orders')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/purchases/index.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
@endsection

@section('content')
    <div class="main-container">
        <!-- Header Block -->
        <div class="page-header">
            <div class="header-title">
                <h1>Purchase Orders</h1>
                <p class="subtitle">Manage inbound supply orders and stock replenishment</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.purchases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Purchase Order
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('inventory.purchases.index') }}" class="filter-form">
                <div class="filter-group">
                    <label for="search">Search Order</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Order #, Supplier...">
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date">Order Date</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('inventory.purchases.index') }}" class="btn btn-link">Clear</a>
                </div>
            </form>
        </div>

        <!-- Data Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Expected Date</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong>
                                    <a href="{{ route('inventory.purchases.show', $order->id) }}" class="order-link">
                                        {{ $order->po_number }}
                                    </a>
                                </strong>
                            </td>
                            <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $order->expected_date ? $order->expected_date->format('Y-m-d') : 'N/A' }}</td>
                            <td>${{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge"
                                      style="background-color: {{ $order->status_id->color() }}26; color: {{ $order->status_id->color() }};">
                                    {{ $order->status_id->label() }}
                                </span>
                            </td>
                            <td class="text-right table-actions">
                                <a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn-icon"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- Matches backing value checks against status Enum --}}
                                @if($order->isEditable())
                                    <a href="{{ route('inventory.purchases.edit', $order->id) }}"
                                       class="btn-icon edit-btn" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('inventory.purchases.destroy', $order->id) }}" method="POST"
                                          class="inline-form delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon delete-btn" title="Delete"
                                                onclick="return confirm('Are you sure you want to cancel this purchase order?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center no-data">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                                <p>No purchase orders found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="table-pagination">
                    {{ $orders->withQueryString()->links('pagination::simple') }}
                </div>
            @endif
        </div>
    </div>
@endsection