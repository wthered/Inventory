@csrf

{{-- Section 1: Primary Account & Business Info --}}
<div class="form-section">
    <div class="section-header">
        <h3><i class="ri-information-line icon-accent"></i> Primary & Business Information</h3>
    </div>
    <div class="form-grid">

        {{-- Customer Code --}}
        <div class="form-group">
            <label for="code" class="form-label required">Customer Code</label>
            <input type="text" name="code" id="code"
                   class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code', $customer->code ?? '') }}"
                   placeholder="e.g. CUST-00101" required>
            @error('code')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Full Name --}}
        <div class="form-group">
            <label for="name" class="form-label required">Full Name / Title</label>
            <input type="text" name="name" id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $customer->name ?? '') }}"
                   placeholder="e.g. John Doe" required>
            @error('name')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Company Name --}}
        <div class="form-group">
            <label for="company_name" class="form-label">Company Name</label>
            <input type="text" name="company_name" id="company_name"
                   class="form-control @error('company_name') is-invalid @enderror"
                   value="{{ old('company_name', $customer->company_name ?? '') }}"
                   placeholder="e.g. Acme Corporation">
            @error('company_name')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Tax Number / ΑΦΜ --}}
        <div class="form-group">
            <label for="tax_number" class="form-label">Tax Number (ΑΦΜ)</label>
            <input type="text" name="tax_number" id="tax_number"
                   class="form-control @error('tax_number') is-invalid @enderror"
                   value="{{ old('tax_number', $customer->tax_number ?? '') }}"
                   placeholder="e.g. 123456789">
            @error('tax_number')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Phone Number --}}
        <div class="form-group">
            <label for="phone" class="form-label required">Phone Number</label>
            <input type="text" name="phone" id="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $customer->phone ?? '') }}"
                   placeholder="+30 210 0000000" required>
            @error('phone')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $customer->email ?? '') }}"
                   placeholder="client@domain.com">
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Customer Type --}}
        <div class="form-group">
            <label for="customer_type" class="form-label required">Customer Type</label>
            <select name="customer_type" id="customer_type"
                    class="form-select @error('customer_type') is-invalid @enderror" required>
                <option value="">Select Customer Type</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}"
                            {{ old('customer_type', $customer->customer_type->value ?? '') == $type->value ? 'selected' : '' }}>
                        {{ ucfirst($type->label()) }}
                    </option>
                @endforeach
            </select>
            @error('customer_type')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Account Status --}}
        <div class="form-group">
            <label for="is_active" class="form-label">Account Status</label>
            <select name="is_active" id="is_active"
                    class="form-select @error('is_active') is-invalid @enderror">
                <option value="1" {{ old('is_active', (string)($customer->is_active ?? '1')) == '1' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ old('is_active', (string)($customer->is_active ?? '')) == '0' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
            @error('is_active')
            <div class="error">{{ $message }}</div>
            @enderror
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
                   class="form-control @error('credit_limit') is-invalid @enderror"
                   value="{{ old('credit_limit', $customer->credit_limit ?? '0.00') }}">
            @error('credit_limit')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Payment Terms --}}
        <div class="form-group">
            <label for="payment_terms" class="form-label">Payment Terms</label>
            <select name="payment_terms" id="payment_terms"
                    class="form-select @error('payment_terms') is-invalid @enderror">
                <option value="">Select Payment Terms</option>
                @foreach($terms as $term)
                    <option value="{{ $term->value }}"
                            {{ old('payment_terms', $customer->payment_terms->value ?? '') == $term->value ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $term->label())) }}
                    </option>
                @endforeach
            </select>
            @error('payment_terms')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

    </div>
</div>

{{-- Section 3: Addresses & Location --}}
<div class="form-section">
    <div class="section-header">
        <h3><i class="ri-map-pin-line icon-accent"></i> Location & Notes</h3>
    </div>
    <div class="form-grid">

        {{-- Billing Address --}}
        <div class="form-group full-width">
            <label for="billing_address" class="form-label">Billing Address</label>
            <textarea name="billing_address" id="billing_address" rows="2"
                      class="form-control @error('billing_address') is-invalid @enderror"
                      placeholder="Street address, building number...">{{ old('billing_address', $customer->billing_address ?? '') }}</textarea>
            @error('billing_address')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shipping Address --}}
        <div class="form-group full-width">
            <label for="shipping_address" class="form-label">Shipping Address</label>
            <textarea name="shipping_address" id="shipping_address" rows="2"
                      class="form-control @error('shipping_address') is-invalid @enderror"
                      placeholder="Delivery street address...">{{ old('shipping_address', $customer->shipping_address ?? '') }}</textarea>
            @error('shipping_address')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Country --}}
        <div class="form-group">
            <label for="country_id" class="form-label">Country</label>
            <select name="country_id" id="country_id"
                    class="form-select @error('country_id') is-invalid @enderror">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}"
                            {{ old('country_id', $customer->country_id ?? '') == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
            @error('country_id')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- City --}}
        <div class="form-group">
            <label for="city_id" class="form-label">City</label>
            <select name="city_id" id="city_id"
                    class="form-select @error('city_id') is-invalid @enderror"
                    {{ old('country_id', $customer->country_id ?? null) ? '' : 'disabled' }}>
                <option value="">Select City</option>
            </select>
            @error('city_id')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- State --}}
        <div class="form-group position-relative">
            <label for="state" class="form-label">State / Region</label>
            <input type="text" name="state" id="state"
                   class="form-control @error('state') is-invalid @enderror"
                   value="{{ old('state', $customer->state ?? '') }}"
                   placeholder="Attica" autocomplete="off">

            <div id="countryStates" class="autocomplete-results d-none"></div>
            @error('state')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Postal Code --}}
        <div class="form-group">
            <label for="postal_code" class="form-label">Postal Code</label>
            <input type="text" name="postal_code" id="postal_code"
                   class="form-control @error('postal_code') is-invalid @enderror"
                   value="{{ old('postal_code', $customer->postal_code ?? '') }}"
                   placeholder="10431">
            @error('postal_code')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Internal Notes --}}
        <div class="form-group full-width">
            <label for="notes" class="form-label">Internal Notes</label>
            <textarea name="notes" id="notes" rows="3"
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Add optional initial details or payment remarks...">{{ old('notes', $customer->notes ?? '') }}</textarea>
            @error('notes')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

    </div>
</div>

{{-- Form Actions Footer --}}
<div class="form-footer">
    <a href="{{ route('inventory.customers.index') }}" class="btn-secondary">Cancel</a>
    <button type="submit" class="btn-primary">
        <i class="{{ isset($customer) ? 'ri-save-line' : 'ri-user-add-line' }}"></i>
        {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
    </button>
</div>