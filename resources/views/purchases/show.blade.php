@extends('templates.general')

@section('title', 'Purchase Order #' . $order->po_number)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/purchases/show.css') }}"/>
@endsection

@section('content')
    <div class="main-container">
        <!-- Header Block -->
        <div class="page-header">
            <div class="header-title">
                <div class="title-with-badge">
                    <h1>Order {{ $order->po_number }}</h1>
                    <span class="badge badge-{{ mb_strtolower($order->status_id->name) }}">
                        {{ $order->status_id->label() }}
                    </span>
                </div>
                <p class="subtitle">Created by {{ $order->creator->name ?? 'System' }}
                    on {{ $order->created_at->format('Y-m-d H:i') }}</p>
            </div>

            <div class="header-actions">
                <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>

                @if($order->isEditable())
                    <a href="{{ route('inventory.purchases.edit', $order->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                @endif
            </div>
        </div>

        <!-- Upper Info Grid -->
        <div class="info-grid">
            <!-- Supplier & Delivery Details -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Procurement Details</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Supplier:</span>
                        <span class="detail-value"><strong>{{ $order->supplier->name ?? 'N/A' }}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Deliver To (Warehouse):</span>
                        <span class="detail-value">{{ $order->warehouse->name ?? 'N/A' }}</span>
                    </div>
                    <hr class="divider">
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value">{{ $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Expected Date:</span>
                        <span class="detail-value">{{ $order->expected_date ? $order->expected_date->format('Y-m-d') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Actual Receipt Date:</span>
                        <span class="detail-value">
                            {{ $order->received_at ? $order->received_at->format('Y-m-d H:i') : 'Not Yet Received' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Totals Summary -->
            <div class="info-card financial-card">
                <div class="card-header">
                    <h3><i class="fas fa-wallet"></i> Financial Summary</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Subtotal:</span>
                        <span class="detail-value">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tax Amount:</span>
                        <span class="detail-value text-muted">+ ${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Discount Amount:</span>
                        <span class="detail-value text-error">- ${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    <hr class="divider">
                    <div class="detail-row total-row">
                        <span class="detail-label">Grand Total:</span>
                        <span class="detail-value grand-total">${{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <div class="table-card margin-top-lg">
            <div class="card-header border-bottom">
                <h3><i class="fas fa-boxes"></i> Line Items</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Batch Reference</th>
                        <th class="text-center">Qty Ordered</th>
                        <th class="text-center">Qty Received</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-center">Discount %</th>
                        <th class="text-right">Total (Ordered)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                <div class="product-info">
                                    <span class="product-name"><strong>{{ $item->product->name }}</strong></span>
                                    <span class="product-sku text-muted">{{ $item->product->sku ?? 'No SKU' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($item->batch_number)
                                    <div class="batch-badge">
                                        <i class="fas fa-barcode"></i> {{ $item->batch_number }}
                                        @if($item->expiry_date)
                                            <span class="expiry-text text-muted">Exp: {{ $item->expiry_date->format('Y-m-d') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $item->quantity_ordered }}</strong></td>
                            <td class="text-center">
                                <span class="{{ $item->quantity_received >= $item->quantity_ordered ? 'text-success' : 'text-warning' }}">
                                    {{ $item->quantity_received }}
                                </span>
                            </td>
                            <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->discount_rate > 0 ? number_format($item->discount_rate, 2) . '%' : '-' }}</td>
                            <td class="text-right"><strong>${{ number_format($item->total_ordered_price, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center no-data">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                <p>No registered line items found on this purchase order.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes & Status History Audit Trail -->
        <div class="bottom-grid margin-top-lg">
            <!-- Order Notes -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-sticky-note"></i> Internal Notes</h3>
                </div>
                <div class="card-body notes-body">
                    @if($order->notes)
                        <blockquote class="notes-quote">
                            {!! nl2br(e($order->notes)) !!}
                        </blockquote>
                    @else
                        <p class="text-muted italic">No internal or supplier notes recorded for this purchase order.</p>
                    @endif
                </div>
            </div>

            <!-- Audit Trail (History Logs) -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Status Audit Trail</h3>
                </div>
                <div class="card-body history-body">
                    @if($order->history->count() > 0)
                        <ul class="history-timeline">
                            @foreach($order->history()->latest()->get() as $log)
                                <li class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <strong>{{ $log->event }}</strong>
                                            <span class="timeline-time">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                                        </div>
                                        <p class="timeline-desc text-muted">{{ $log->description }}</p>
                                        <span class="timeline-user">By: {{ $log->user->name ?? 'System' }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted italic">No operational state transition history tracked.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection