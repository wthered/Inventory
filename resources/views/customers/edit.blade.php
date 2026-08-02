@extends('templates.general')

@section('title', 'Edit Customer - ' . $customer->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/customers/edit.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Error Handling Feedback --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="ri-error-warning-line"></i>
                <div>
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="ri-user-settings-line icon-accent"></i>
                    <span>Edit Customer: {{ $customer->name }}</span>
                </h1>
                <p class="subtitle">Update contact info, billing details, and credit configuration
                    [Code: {{ $customer->code }}]</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.customers.show', $customer->id) }}" class="btn-action btn-show"
                   title="View Customer Profile">
                    <i class="ri-eye-line"></i>
                </a>
                <a href="{{ route('inventory.customers.index') }}" class="btn-action btn-show"
                   title="Back to Customer List">
                    <i class="ri-arrow-left-line"></i>
                </a>
            </div>
        </div>

        {{-- Form Container --}}
        <form action="{{ route('inventory.customers.update', $customer->id) }}" method="POST" class="form-card">
            @csrf
            @method('PUT')

            {{-- Section 1: Primary Account & Business Info --}}
            <div class="form-section">
                <div class="section-header">
                    <h3><i class="ri-information-line icon-accent"></i> Primary & Business Information</h3>
                </div>
                <div class="form-grid">

                    {{-- Account Code --}}
                    <div class="form-group">
                        <label for="code" class="form-label required">Customer Code</label>
                        <input type="text" name="code" id="code" class="form-control"
                               value="{{ old('code', $customer->code) }}" required>
                    </div>

                    {{-- Full Name --}}
                    <div class="form-group">
                        <label for="name" class="form-label required">Full Name / Title</label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="{{ old('name', $customer->name) }}" required>
                    </div>

                    {{-- Company Name --}}
                    <div class="form-group">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" name="company_name" id="company_name" class="form-control"
                               value="{{ old('company_name', $customer->company_name) }}">
                    </div>

                    {{-- Tax Number / AFM --}}
                    <div class="form-group">
                        <label for="tax_number" class="form-label">Tax Number (AFM)</label>
                        <input type="text" name="tax_number" id="tax_number" class="form-control"
                               value="{{ old('tax_number', $customer->tax_number) }}">
                    </div>

                    {{-- Phone Number --}}
                    <div class="form-group">
                        <label for="phone" class="form-label required">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control"
                               value="{{ old('phone', $customer->phone) }}" required>
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="{{ old('email', $customer->email) }}">
                    </div>

                    {{-- Customer Type --}}
                    <div class="form-group">
                        <label for="customer_type" class="form-label required">Customer Type</label>
                        <select name="customer_type" id="customer_type" class="form-select" required>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" {{ old('customer_type', $customer->customer_type) === $type->value ? 'selected' : '' }}>
                                    {{ ucfirst($type->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Toggle --}}
                    <div class="form-group">
                        <label for="is_active" class="form-label">Account Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Active
                            </option>
                            <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- Section 2: Financial Terms --}}
            <div class="form-section">
                <div class="section-header">
                    <h3><i class="ri-bank-card-line icon-accent"></i> Financial Settings</h3>
                </div>
                <div class="form-grid">

                    {{-- Credit Limit --}}
                    <div class="form-group">
                        <label for="credit_limit" class="form-label">Credit Limit (€)</label>
                        <input type="number" step="0.01" min="0" name="credit_limit" id="credit_limit"
                               class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}">
                    </div>

                    {{-- Payment Terms --}}
                    <div class="form-group">
                        <label for="payment_terms" class="form-label">Payment Terms</label>
                        <select name="payment_terms" id="payment_terms" class="form-select">
                            @foreach($terms as $term)
                                <option value="{{ $term->value }}" {{ old('payment_terms', $customer->payment_terms->value ?? $customer->payment_terms) === $term->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $term->value)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            {{-- Section 3: Addresses & Notes --}}
            <div class="form-section">
                <div class="section-header">
                    <h3><i class="ri-map-pin-line icon-accent"></i> Location & Notes</h3>
                </div>
                <div class="form-grid">

                    {{-- Billing Address --}}
                    <div class="form-group full-width">
                        <label for="billing_address" class="form-label">Billing Address</label>
                        <textarea name="billing_address" id="billing_address" rows="2"
                                  class="form-control">{{ old('billing_address', $customer->billing_address) }}</textarea>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="form-group full-width">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea name="shipping_address" id="shipping_address" rows="2"
                                  class="form-control">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
                    </div>

                    {{-- City --}}
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control"
                               value="{{ old('city', $customer->city) }}">
                    </div>

                    {{-- State --}}
                    <div class="form-group">
                        <label for="state" class="form-label">State / Region</label>
                        <input type="text" name="state" id="state" class="form-control"
                               value="{{ old('state', $customer->state) }}">
                    </div>

                    {{-- Country --}}
                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" name="country" id="country" class="form-control"
                               value="{{ old('country', $customer->country) }}">
                    </div>

                    {{-- Postal Code --}}
                    <div class="form-group">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control"
                               value="{{ old('postal_code', $customer->postal_code) }}">
                    </div>

                    {{-- Internal Notes --}}
                    <div class="form-group full-width">
                        <label for="notes" class="form-label">Internal Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control"
                                  placeholder="Add optional details or payment remarks...">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                </div>
            </div>

            {{-- Form Actions Footer --}}
            <div class="form-footer">
                <a href="{{ route('inventory.customers.show', $customer->id) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Save Changes
                </button>
            </div>

        </form>

    </div>
@endsection