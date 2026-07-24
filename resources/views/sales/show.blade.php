@extends('templates.general')

@section('title', 'Sales Order #' . ($sale->order_number ?? $sale->id))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/sales/show.css') }}"/>
@endsection

@section('content')
    <div class="main-container">

        <!-- Header Block -->
        <div class="page-header">
            <div class="header-title">
                <div class="header-with-badge">
                    <h1>Order {{ $sale->order_number ?? '#' . $sale->id }}</h1>

                    {{-- Order Status Badge --}}
                    @if($sale->status)
                        <span class="badge"
                              style="background-color: {{ $sale->status->color() }}26; color: {{ $sale->status->color() }};">
                            {{ $sale->status->label() }}
                        </span>
                    @endif

                    {{-- Payment Status Badge --}}
                    @if($sale->payment_status)
                        <span class="badge"
                              style="background-color: {{ $sale->payment_status->hexColor() }}26; color: {{ $sale->payment_status->hexColor() }};">
                            {{ $sale->payment_status->label() }}
                        </span>
                    @endif
                </div>
                <p class="subtitle">Created on {{ $sale->order_date ? $sale->order_date->format('Y-m-d') : 'N/A' }}</p>
            </div>

            <div class="header-actions">
                <a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>

                {{-- Έλεγχος αν επιτρέπεται η επεξεργασία μέσω του Enum/Model helper --}}
                @if($sale->status_id?->isEditable())
                    <a href="{{ route('inventory.sales.edit', $sale->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Layout Split -->
        <div class="detail-layout">

            <!-- LEFT COLUMN: Order Items -->
            <div class="layout-main">
                <div class="card table-card">
                    <div class="card-header">
                        <h2><i class="fas fa-boxes"></i> Order Items</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="detail-table">
                            <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-center">Qty Ordered</th>
                                <th class="text-center">Qty Shipped</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Discount</th>
                                <th class="text-right">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($sale->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name ?? 'Unknown Product' }}</strong>
                                        @if($item->batch_number)
                                            <span class="item-meta"><br><i class="fas fa-barcode"></i> Batch: {{ $item->batch_number }}</span>
                                        @endif
                                    </td>
                                    <td><span class="sku-code">{{ $item->product->sku ?? 'N/A' }}</span></td>
                                    <td class="text-center font-numeric">{{ $item->quantity_ordered }}</td>
                                    <td class="text-center font-numeric">
                                            <span class="{{ $item->quantity_shipped < $item->quantity_ordered ? 'text-warning' : 'text-success' }}">
                                                {{ $item->quantity_shipped }}
                                            </span>
                                    </td>
                                    <td class="text-right font-numeric">€{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right font-numeric text-muted">
                                        {{ $item->discount_rate > 0 ? number_format($item->discount_rate, 1) . '%' : '-' }}
                                    </td>
                                    {{-- Χρήση του Virtual Column της βάσης σου για σωστό net line total --}}
                                    <td class="text-right font-numeric">
                                        <strong>€{{ number_format($item->total_ordered_price, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center no-data">
                                        <p>No items found in this sales order.</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes / Remarks Section --}}
                @if($sale->notes)
                    <div class="card notes-card">
                        <div class="card-header">
                            <h3><i class="fas fa-comment-alt"></i> Remarks / Instructions</h3>
                        </div>
                        <div class="card-body">
                            <p class="notes-text">{{ $sale->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- ORDER HISTORY / AUDIT TRAIL -->
                <div class="card history-card">
                    <div class="card-header">
                        <h2><i class="fas fa-history"></i> Ιστορικό Ενεργειών & Audit Trail</h2>
                    </div>
                    <div class="card-body">
                        @if($sale->history && $sale->history->count() > 0)
                            <div class="timeline">
                                @foreach($sale->history->sortByDesc('created_at') as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <span class="timeline-event"><strong>{{ $log->event ?? $log->action }}</strong></span>
                                                <span class="timeline-time font-numeric text-muted">
                                                    {{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : 'N/A' }}
                                                </span>
                                            </div>
                                            @if($log->description)
                                                <p class="timeline-desc">{{ $log->description }}</p>
                                            @endif
                                            <span class="timeline-user">
                                                <i class="fas fa-user-edit"></i> Χρήστης: {{ $log->user->name ?? 'Σύστημα / Automated' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted margin-0"><i class="fas fa-info-circle"></i> Δεν υπάρχουν ακόμη
                                καταγεγραμμένες ενέργειες για αυτή την παραγγελία.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Sidebar (Details & Totals) -->
            <div class="layout-sidebar">

                <!-- Order Fulfillment & Customer Info Card -->
                <div class="card info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Fulfillment Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <label>Customer</label>
                            <span class="info-value">
                                <i class="fas fa-user"></i> {{ $sale->customer->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="info-group">
                            <label>Fulfilling Warehouse</label>
                            <span class="info-value">
                                <i class="fas fa-warehouse"></i> {{ $sale->warehouse->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="info-group">
                            <label>Shipping Date</label>
                            <span class="info-value">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $sale->shipping_date ? \Carbon\Carbon::parse($sale->shipping_date)->format('Y-m-d') : 'Not scheduled' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Card -->
                <div class="card summary-card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-invoice-dollar"></i> Financial Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="font-numeric">€{{ number_format($sale->subtotal, 2) }}</span>
                        </div>

                        <div class="summary-row text-danger">
                            <span>Discount Given</span>
                            <span class="font-numeric">-€{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>

                        <div class="summary-row">
                            <span>Tax Amount</span>
                            <span class="font-numeric">€{{ number_format($sale->tax_amount, 2) }}</span>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-row total-row">
                            <span>Grand Total</span>
                            <span class="font-numeric final-amount">€{{ number_format($sale->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection