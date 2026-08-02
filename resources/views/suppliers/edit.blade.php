@extends('templates.general')

@section('title', 'Edit Supplier: ' . ($supplier->company_name ?? $supplier->name))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/suppliers/edit.css') }}"/>
@endsection

@section('content')
    <div class="page-container">

        <!-- Header & Navigation -->
        <div class="page-header">
            <div class="header-title-group">
                <a href="{{ route('inventory.suppliers.show', $supplier->id) }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Supplier Details
                </a>
                <h1>
                    <i class="fas fa-edit icon-accent"></i> Edit
                    Supplier: {{ $supplier->company_name ?? $supplier->name }}
                </h1>
            </div>
        </div>

        <!-- Global Form Validation Errors -->
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Please fix the following errors before submitting:</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Edit Supplier Form -->
        <form action="{{ route('inventory.suppliers.update', $supplier->id) }}" method="POST" class="form-container">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic & Contact Details -->
            <div class="form-card">
                <h3 class="card-title">
                    <i class="fas fa-id-card icon-accent"></i> Basic & Primary Contact Information
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="code" class="form-label required">Supplier Code / SKU</label>
                        <input type="text"
                               id="code"
                               name="code"
                               value="{{ old('code', $supplier->code) }}"
                               class="form-control @error('code') is-invalid @enderror"
                               required>
                        @error('code')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label required">System Name / Display Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $supplier->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required>
                        @error('name')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_name" class="form-label">Registered Company Name</label>
                        <input type="text"
                               id="company_name"
                               name="company_name"
                               value="{{ old('company_name', $supplier->company_name) }}"
                               class="form-control @error('company_name') is-invalid @enderror">
                        @error('company_name')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text"
                               id="contact_person"
                               name="contact_person"
                               value="{{ old('contact_person', $supplier->contact_person) }}"
                               class="form-control @error('contact_person') is-invalid @enderror">
                        @error('contact_person')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $supplier->email) }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label required">Primary Phone</label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $supplier->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror"
                               required>
                        @error('phone')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Alternative / Contact Phone</label>
                        <input type="text"
                               id="contact_phone"
                               name="contact_phone"
                               value="{{ old('contact_phone', $supplier->contact_phone) }}"
                               class="form-control @error('contact_phone') is-invalid @enderror">
                        @error('contact_phone')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="website" class="form-label">Website URL</label>
                        <input type="text"
                               id="website"
                               name="website"
                               value="{{ old('website', $supplier->website) }}"
                               class="form-control @error('website') is-invalid @enderror"
                               placeholder="https://example.com">
                        @error('website')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Financial & Commercial Terms -->
            <div class="form-card">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar icon-accent"></i> Tax & Financial Terms
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tax_number" class="form-label">Tax / VAT Number</label>
                        <input type="text"
                               id="tax_number"
                               name="tax_number"
                               value="{{ old('tax_number', $supplier->tax_number) }}"
                               class="form-control @error('tax_number') is-invalid @enderror">
                        @error('tax_number')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="credit_limit" class="form-label required">Credit Limit (€)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               id="credit_limit"
                               name="credit_limit"
                               value="{{ old('credit_limit', $supplier->credit_limit) }}"
                               class="form-control @error('credit_limit') is-invalid @enderror"
                               required>
                        @error('credit_limit')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="payment_terms" class="form-label required">Payment Terms</label>
                        <select id="payment_terms"
                                name="payment_terms"
                                class="form-control @error('payment_terms') is-invalid @enderror"
                                required>
                            <option value="cash" {{ old('payment_terms', $supplier->payment_terms) == 'cash' ? 'selected' : '' }}>
                                Cash
                            </option>
                            <option value="credit_7" {{ old('payment_terms', $supplier->payment_terms) == 'credit_7' ? 'selected' : '' }}>
                                7 Days Credit
                            </option>
                            <option value="credit_15" {{ old('payment_terms', $supplier->payment_terms) == 'credit_15' ? 'selected' : '' }}>
                                15 Days Credit
                            </option>
                            <option value="credit_30" {{ old('payment_terms', $supplier->payment_terms) == 'credit_30' ? 'selected' : '' }}>
                                30 Days Credit
                            </option>
                            <option value="credit_60" {{ old('payment_terms', $supplier->payment_terms) == 'credit_60' ? 'selected' : '' }}>
                                60 Days Credit
                            </option>
                            <option value="credit_90" {{ old('payment_terms', $supplier->payment_terms) == 'credit_90' ? 'selected' : '' }}>
                                90 Days Credit
                            </option>
                        </select>
                        @error('payment_terms')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label class="toggle-checkbox-label">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                   class="toggle-checkbox">
                            <span class="toggle-text">Active Supplier Account</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 3: Physical Address -->
            <div class="form-card">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt icon-accent"></i> Address Details
                </h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="address" class="form-label">Street Address</label>
                        <textarea id="address"
                                  name="address"
                                  rows="2"
                                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                        @error('address')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text"
                               id="city"
                               name="city"
                               value="{{ old('city', $supplier->city) }}"
                               class="form-control @error('city') is-invalid @enderror">
                        @error('city')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="state" class="form-label">State / Region</label>
                        <input type="text"
                               id="state"
                               name="state"
                               value="{{ old('state', $supplier->state) }}"
                               class="form-control @error('state') is-invalid @enderror">
                        @error('state')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input type="text"
                               id="country"
                               name="country"
                               value="{{ old('country', $supplier->country) }}"
                               class="form-control @error('country') is-invalid @enderror">
                        @error('country')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text"
                               id="postal_code"
                               name="postal_code"
                               value="{{ old('postal_code', $supplier->postal_code) }}"
                               class="form-control @error('postal_code') is-invalid @enderror">
                        @error('postal_code')
                        <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Internal Notes -->
            <div class="form-card">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note icon-accent"></i> Internal Notes
                </h3>

                <div class="form-group full-width">
                    <label for="notes" class="form-label">Notes / Remarks</label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Add operational notes or details regarding this supplier...">{{ old('notes', $supplier->notes) }}</textarea>
                    @error('notes')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Form Actions Bar -->
            <div class="form-actions-bar">
                <a href="{{ route('inventory.suppliers.show', $supplier->id) }}" class="btn-cancel">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </form>

    </div>
@endsection