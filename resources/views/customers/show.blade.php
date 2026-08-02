@extends('templates.general')

@section('title', 'Customer Profile - ' . $customer->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/customers/show.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- System Notifications --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="ri-checkbox-circle-line"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="ri-user-line icon-accent"></i>
                    <span>{{ $customer->name }}</span>
                    @if($customer->company_name)
                        <span class="td-description">({{ $customer->company_name }})</span>
                    @endif
                    @if($customer->trashed())
                        <span class="badge-trashed">
                        <i class="ri-delete-bin-line"></i> Archived
                    </span>
                    @endif
                </h1>
                <p class="subtitle">Code: <strong>{{ $customer->code }}</strong> &bull;
                    Registered {{ $customer->created_at->format('M d, Y') }}</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.customers.index') }}" class="btn-action btn-show" title="Back to Index">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <a href="{{ route('inventory.customers.edit', $customer->id) }}" class="btn-action btn-edit"
                   title="Edit Customer">
                    <i class="ri-pencil-line"></i>
                </a>
                <form action="{{ route('inventory.customers.destroy', $customer->id) }}" method="POST"
                      class="inline-form"
                      onsubmit="return confirm('Are you sure you want to delete this customer?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-delete" title="Delete Customer">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Details Grid Section --}}
        <div class="customer-grid">

            {{-- Primary Info Card --}}
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="ri-information-line icon-accent"></i> Primary Details</h3>
                </div>
                <div class="card-body">
                    <div class="detail-group">
                        <label>Full Name / Title</label>
                        <p class="td-name">{{ $customer->name }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Company Name</label>
                        <p>{{ $customer->company_name ?? '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Email Address</label>
                        <p>{{ $customer->email ?? '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Phone Number</label>
                        <p>{{ $customer->phone }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Tax Number (AFM)</label>
                        <p>{{ $customer->tax_number ?? '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Customer Type</label>
                        <p>{{ ucfirst($customer->customer_type) }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Credit Limit</label>
                        <p>€{{ number_format($customer->credit_limit, 2) }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Payment Terms</label>
                        <p>{{ $customer->payment_terms->value ?? $customer->payment_terms }}</p>
                    </div>
                </div>
            </div>

            {{-- Address Info Card --}}
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="ri-map-pin-line icon-accent"></i> Location Details</h3>
                </div>
                <div class="card-body">
                    <div class="detail-group">
                        <label>Billing Address</label>
                        <p>{{ $customer->billing_address ?? '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Shipping Address</label>
                        <p>{{ $customer->shipping_address ?? '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>City & State</label>
                        <p>{{ filter_var([$customer->city, $customer->state]) ? implode(', ', array_filter([$customer->city, $customer->state])) : '—' }}</p>
                    </div>
                    <div class="detail-group">
                        <label>Postal Code / Country</label>
                        <p>{{ filter_var([$customer->postal_code, $customer->country]) ? implode(', ', array_filter([$customer->postal_code, $customer->country])) : '—' }}</p>
                    </div>
                    @if($customer->notes)
                        <div class="detail-group" style="grid-column: 1 / -1;">
                            <label>Notes</label>
                            <p class="td-description">{{ $customer->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Associated Sales/Orders History Table --}}
        <div class="table-card">
            <div class="table-card-header">
                <h3><i class="ri-history-line icon-accent"></i> Transaction History</h3>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Created By</th>
                        <th>Total Amount</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customer->sales as $sale)
                        <tr>
                            <td class="td-id">{{ $sale->order_number ?? '#' . $sale->id }}</td>
                            <td>{{ $sale->order_date ? $sale->order_date->format('Y-m-d') : $sale->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($sale->creator)
                                    {{ $sale->creator->name }}
                                @else
                                    <span class="td-description">Unassigned</span>
                                @endif
                            </td>
                            <td>€{{ number_format($sale->grand_total ?? 0, 2) }}</td>
                            <td class="text-right">
                                <div class="action-buttons-group">
                                    <a href="{{ route('inventory.sales.show', $sale->id) }}" class="btn-action btn-show"
                                       title="View Sale">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty-state">
                                <i class="ri-inbox-archive-line empty-icon"></i>
                                <p>No transactions found for this customer.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection