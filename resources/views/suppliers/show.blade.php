@extends('templates.general')

@section('title', 'Supplier: ' . ($supplier->company_name ?? $supplier->name))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/suppliers/show.css') }}"/>
@endsection

@section('content')
    <div class="page-container">

        <!-- Top Action / Navigation Header -->
        <div class="page-header">
            <div class="breadcrumb-group">
                <a href="{{ route('inventory.suppliers.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Suppliers
                </a>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.suppliers.edit', $supplier->id) }}" class="btn-primary">
                    <i class="fas fa-edit"></i> Edit Supplier
                </a>
                @if($supplier->email)
                    <a href="mailto:{{ $supplier->email }}" class="btn-secondary-action">
                        <i class="fas fa-envelope"></i> Send Email
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Supplier Profile Banner -->
        <div class="profile-banner">
            <div class="profile-main-info">
                <div class="profile-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="profile-title-stack">
                    <div class="title-with-badge">
                        <h1>{{ $supplier->company_name ?? $supplier->name }}</h1>
                        @if($supplier->is_active)
                            <span class="badge-status badge-status-active">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                        @else
                            <span class="badge-status badge-status-inactive">
                            <i class="fas fa-times-circle"></i> Inactive
                        </span>
                        @endif
                    </div>
                    <p class="supplier-code-subtitle">
                        Supplier SKU / Code: <strong>{{ $supplier->code }}</strong>
                        @if($supplier->company_name && $supplier->name !== $supplier->company_name)
                            &bull; Account: {{ $supplier->name }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Details Grid (Left: Info, Right: Financial & Terms) -->
        <div class="supplier-grid">

            <!-- General Information Card -->
            <div class="info-card">
                <h3 class="card-title"><i class="fas fa-address-card icon-accent"></i> Contact & Address</h3>
                <div class="detail-list">
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-user"></i> Contact Person:</span>
                        <span class="value">{{ $supplier->contact_person ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-envelope"></i> Email:</span>
                        <span class="value">
                        @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}"
                                   class="link-highlight">{{ $supplier->email }}</a>
                            @else
                                —
                            @endif
                    </span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-phone"></i> Phone:</span>
                        <span class="value">{{ $supplier->phone }}</span>
                    </div>
                    @if($supplier->contact_phone)
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-mobile-alt"></i> Alt / Contact Phone:</span>
                            <span class="value">{{ $supplier->contact_phone }}</span>
                        </div>
                    @endif
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-globe"></i> Website:</span>
                        <span class="value">
                        @if($supplier->website)
                                <a href="{{ Str::startsWith($supplier->website, 'http') ? $supplier->website : 'https://' . $supplier->website }}"
                                   target="_blank" rel="noopener noreferrer" class="link-highlight">
                                {{ $supplier->website }} <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            @else
                                —
                            @endif
                    </span>
                    </div>
                    <div class="detail-row align-start">
                        <span class="label"><i class="fas fa-map-marker-alt"></i> Physical Address:</span>
                        <span class="value">
                        @if($supplier->address || $supplier->city || $supplier->country)
                                {{ $supplier->address ?? '' }}<br>
                                {{ implode(', ', array_filter([$supplier->city, $supplier->state, $supplier->postal_code, $supplier->country])) }}
                            @else
                                —
                            @endif
                    </span>
                    </div>
                </div>
            </div>

            <!-- Financial Terms & Tax Card -->
            <div class="info-card">
                <h3 class="card-title"><i class="fas fa-file-invoice-dollar icon-accent"></i> Financial & Commercial
                    Terms</h3>
                <div class="detail-list">
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-hashtag"></i> Tax / VAT Number:</span>
                        <span class="value">{{ $supplier->tax_number ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-credit-card"></i> Credit Limit:</span>
                        <span class="value text-accent">&euro;{{ number_format($supplier->credit_limit, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-handshake"></i> Payment Terms:</span>
                        <span class="value">
                        <span class="term-pill">
                            {{ str_replace('_', ' ', strtoupper($supplier->payment_terms)) }}
                        </span>
                    </span>
                    </div>
                    <div class="detail-row align-start">
                        <span class="label"><i class="fas fa-sticky-note"></i> Internal Notes:</span>
                        <span class="value text-muted-custom">
                        {{ $supplier->notes ?? 'No internal notes specified for this supplier.' }}
                    </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Associated Products Section -->
        <div class="table-card">
            <div class="table-header-bar">
                <h2><i class="fas fa-boxes icon-accent"></i> Supplied Products ({{ $supplier->products->count() }})</h2>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Product SKU</th>
                        <th>Product Name</th>
                        <th>Supplier Price</th>
                        <th>Lead Time</th>
                        <th>MOQ</th>
                        <th>Preference</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($supplier->products as $product)
                        <tr>
                            <td class="td-id">{{ $product->sku }}</td>
                            <td class="td-name">
                                <strong>
                                    <a href="{{ route('inventory.products.show', $product->id) }}" class="product-link">
                                        {{ $product->name }}
                                    </a>
                                </strong>
                            </td>
                            <td class="td-price">&euro;{{ number_format($product->pivot->price, 2) }}</td>
                            <td>{{ $product->pivot->lead_time_days ? $product->pivot->lead_time_days . ' days' : '—' }}</td>
                            <td>{{ $product->pivot->moq ? number_format($product->pivot->moq) . ' units' : '—' }}</td>
                            <td>
                                @if(!empty($product->pivot->is_preferred))
                                    <span class="badge-preferred">
                                        <i class="fas fa-star"></i> Preferred
                                    </span>
                                @else
                                    <span class="td-description">Standard</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('inventory.products.show', $product->id) }}"
                                   class="btn-action btn-show" title="View Product Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty-state">
                                <i class="fas fa-box-open empty-icon"></i>
                                <p>No products currently associated with this supplier.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection