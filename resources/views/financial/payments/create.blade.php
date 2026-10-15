@extends('templates.general')

@section('title', 'Create Payment')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/payments/create.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<div class="page-header">
			<div class="header-title">
				<div class="title-with-badge">
					<h1>Create New Payment</h1>
				</div>
				<p class="subtitle">
					Record a new incoming or outgoing transaction in the system.
				</p>
			</div>

			<div class="header-actions">
				<a href="{{ route('inventory.payments.index') }}" class="btn btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
			</div>
		</div>

		@if(session('success'))
			<div class="alert alert-success margin-bottom-lg">
				<i class="fas fa-check-circle alert-icon"></i>
				<div>{{ session('success') }}</div>
			</div>
		@endif

		<form action="{{ route('inventory.payments.store') }}" method="POST" id="create-payment-form">
			@csrf

			<div class="form-grid">
				<!-- Payment Party & Classification -->
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-info-circle"></i> Payment Information</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="is_incoming" class="form-label">Payment Direction
								<span class="text-error">*</span></label>
							<select name="is_incoming" id="is_incoming" class="form-select @error('is_incoming') is-invalid @enderror" required>
								<option value="1" @selected(old('is_incoming', '1') == '1')>Incoming (Customer
									Receipt)
								</option>
								<option value="0" @selected(old('is_incoming') == '0')>Outgoing (Supplier Payment)
								</option>
							</select>
							@error('is_incoming')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<!-- Party Search (Customer/Supplier) -->
						<div class="form-group position-relative" id="party-search-group">
							<label for="party_name" class="form-label">
								<span id="party-label-text">
									{{ old('is_incoming', true) == '1' ? 'Customer' : 'Supplier' }}
								</span>
								<span class="text-error">*</span>
							</label>

							<input type="text"
							       id="party_name"
							       class="form-control @error('customer_id') is-invalid @enderror @error('supplier_id') is-invalid @enderror"
							       placeholder="Type to search..."
							       value="{{ old('party_name') }}"
							       autocomplete="off"
							       required>

							<input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
							<input type="hidden" name="supplier_id" id="supplier_id" value="{{ old('supplier_id') }}">

							<div id="autocomplete-results" class="autocomplete-dropdown shadow-sm hidden"></div>

							@error('customer_id')
							<span class="invalid-feedback d-block">{{ $message }}</span>
							@enderror
							@error('supplier_id')
							<span class="invalid-feedback d-block">{{ $message }}</span>
							@enderror
						</div>

						<!-- Related Order (Sales Order / Purchase Order) -->
						<div class="form-group">
							<label for="order_id" class="form-label">
								<span id="order-label-text">
									{{ old('is_incoming', '1') == '1' ? 'Sales Order' : 'Purchase Order' }}
								</span>
							</label>
							<select name="payable_id" id="order_id" class="form-select @error('payable_id') is-invalid @enderror" disabled>
								<option value="">Select party first...</option>
							</select>
							@error('payable_id')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>

				<!-- Financial Details -->
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-money-bill-wave"></i> Transaction Details</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="amount" class="form-label">Amount <span class="text-error">*</span></label>
							<input type="number" step="0.01" name="amount" id="amount"
							       class="form-control @error('amount') is-invalid @enderror"
							       value="{{ old('amount') }}" placeholder="0.00" required>
							@error('amount')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="currency" class="form-label">Currency <span class="text-error">*</span></label>
							<input type="text" name="currency" id="currency"
							       class="form-control @error('currency') is-invalid @enderror"
							       value="{{ old('currency', 'EUR') }}" required>
							@error('currency')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="payment_method" class="form-label">Payment Method
								<span class="text-error">*</span></label>
							<select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
								<option value="" disabled @selected(!old('payment_method'))>Select Method</option>
								@foreach($methods::cases() as $method)
									<option value="{{ $method->value }}" @selected(old('payment_method') == $method->value)>
										{{ method_exists($method, 'label') ? $method->label() : $method->name }}
									</option>
								@endforeach
							</select>
							@error('payment_method')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="status" class="form-label">Transaction Status <span class="text-error">*</span></label>
							<select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
								@foreach($transactions::cases() as $status)
									<option value="{{ $status->value }}" @selected(old('status', 'completed') == $status->value)>
										{{ method_exists($status, 'label') ? $status->label() : $status->name }}
									</option>
								@endforeach
							</select>
							@error('status')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="paid_at" class="form-label">Paid Date <span class="text-error">*</span></label>
							<input type="datetime-local" name="paid_at" id="paid_at"
							       class="form-control @error('paid_at') is-invalid @enderror"
							       value="{{ old('paid_at', now()->format('Y-m-d\TH:i:s')) }}" step="1" required>
							@error('paid_at')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="transaction_reference" class="form-label">Transaction Reference</label>
							<input type="text" name="transaction_reference" id="transaction_reference"
							       class="form-control @error('transaction_reference') is-invalid @enderror"
							       value="{{ old('transaction_reference') }}" placeholder="e.g. TRX-12345">
							@error('transaction_reference')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>
			</div>

			<div class="bottom-grid margin-top-lg">
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-sticky-note"></i> Payment Notes</h3>
					</div>
					<div class="card-body">
						<textarea name="notes" id="notes" rows="4" class="form-control textarea-notes"
						          placeholder="Enter payment notes or remarks...">{{ old('notes') }}</textarea>
					</div>
				</div>
			</div>

			<div class="form-actions-bar margin-top-lg">
				<button type="submit" class="btn btn-success btn-lg">
					<i class="fas fa-save"></i> Save Payment
				</button>
				<a href="{{ route('inventory.payments.index') }}" class="btn btn-secondary btn-lg">
					Cancel
				</a>
			</div>
		</form>
	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/payments/create.js') }}"></script>
@endsection