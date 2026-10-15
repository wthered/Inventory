@extends('templates.general')

@section('title', 'Edit Purchase Order #' . $order->po_number)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/purchases/edit.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<!-- Header Block -->
		<div class="page-header">
			<div class="header-title">
				<div class="title-with-badge">
					<h1>Edit Purchase Order {{ $order->order_number }}</h1>
					<span class="badge badge-{{ mb_strtolower($order->status_id->name) }}">
                        {{ $order->status_id->label() }}
                    </span>
				</div>
				<p class="subtitle">Modifying order created by {{ $order->creator->name ?? 'System' }}
					on {{ $order->created_at->format('Y-m-d H:i') }}</p>
			</div>

			<div class="header-actions">
				<a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn btn-secondary">
					<i class="fas fa-eye"></i> View Order
				</a>
				<a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
			</div>
		</div>

		@if(session('success'))
			<div class="alert alert-success margin-bottom-lg"
			     style="display: flex; align-items: center; gap: 10px; background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb;">
				<i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
				<div>
					{{ session('success') }}
				</div>
			</div>
		@endif

		<form action="{{ route('inventory.purchases.update', $order->id) }}" method="POST" id="edit-purchase-form">
			@csrf
			@method('PUT')

			<!-- Upper Form Grid -->
			<div class="form-grid">
				<!-- Primary Procurement Details -->
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-info-circle"></i> Procurement Details</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="supplier_id" class="form-label">Supplier <span
										class="text-error">*</span></label>
							<select name="supplier_id" id="supplier_id"
							        class="form-select @error('supplier_id') is-invalid @enderror" required>
								<option value="">Select Supplier</option>
								@foreach($suppliers as $supplier)
									<option
											value="{{ $supplier->id }}" {{ old('supplier_id', $order->supplier_id) == $supplier->id ? 'selected' : '' }}>
										{{ $supplier->name }}
									</option>
								@endforeach
							</select>
							@error('supplier_id')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="warehouse_id" class="form-label">Deliver To (Warehouse) <span
										class="text-error">*</span></label>
							<select name="warehouse_id" id="warehouse_id"
							        class="form-select @error('warehouse_id') is-invalid @enderror" required>
								<option value="">Select Warehouse</option>
								@foreach($warehouses as $warehouse)
									<option
											value="{{ $warehouse->id }}" {{ old('warehouse_id', $order->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
										{{ $warehouse->name }}
									</option>
								@endforeach
							</select>
							@error('warehouse_id')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>

				<!-- Schedule & Status -->
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-calendar-alt"></i> Dates & Scheduling</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="order_date" class="form-label">Order Date <span
										class="text-error">*</span></label>
							<input type="date" name="order_date" id="order_date"
							       class="form-control @error('order_date') is-invalid @enderror"
							       value="{{ old('order_date', $order->order_date ? $order->order_date->format('Y-m-d') : '') }}"
							       required>
							@error('order_date')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="expected_date" class="form-label">Expected Delivery Date</label>
							<input type="date" name="expected_date" id="expected_date"
							       class="form-control @error('expected_date') is-invalid @enderror"
							       value="{{ old('expected_date', $order->expected_date ? $order->expected_date->format('Y-m-d') : '') }}">
							@error('expected_date')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="status_id" class="form-label">Initial Status <span
										class="text-error">*</span></label>
							<select name="status_id" id="status_id"
							        class="form-select @error('status_id') is-invalid @enderror" required>
								@foreach($statuses::cases() as $status)
									{{-- Restrict initial creation to early stage statuses if desired --}}
									@if(in_array($status, [$statuses::DRAFT, $statuses::AWAITING_APPROVAL, $statuses::APPROVED]))
										<option
												value="{{ $status->value }}" @selected(old('status_id', \App\Enums\Purchases\PurchaseOrderStatus::DRAFT->value) == $status->value)>
											{{ $status->label() }}
										</option>
									@endif
								@endforeach
							</select>
							@error('status_id')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>
			</div>

			<!-- Table Card for Line Items -->
			<div class="table-card margin-top-lg">
				<div class="card-header border-bottom d-flex-between">
					<h3><i class="fas fa-boxes"></i> Line Items</h3>
					<button type="button" class="btn btn-secondary btn-sm" id="add-item-btn">
						<i class="fas fa-plus"></i> Add Line Item
					</button>
				</div>
				<div class="table-responsive">
					<table class="data-table" id="items-table">
						<thead>
						<tr>
							<th style="min-width: 280px;">Product Details <span class="text-error">*</span></th>
							<th style="width: 140px;">Batch Reference</th>
							<th class="text-center" style="width: 110px;">Qty Ordered <span class="text-error">*</span>
							</th>
							<th class="text-right" style="width: 160px;">Qty Received (&euro;)
								<span class="text-error">*</span>
							</th>
							<th class="text-center" style="width: 130px;">Unit Price</th>
							<th class="text-center" style="width: 130px;">Discount Rate</th>
							<th class="text-right" style="width: 160px;">Total</th>
							<th class="text-center" style="width: 70px;">Action</th>
						</tr>
						</thead>
						<tbody id="items-tbody">
						@forelse(old('items', $order->items) as $index => $item)
							@include('templates.purchases.update_rows', ['index' => $index, 'item' => $item])
						@empty
							<!-- JS fallback will populate a default row on load if empty -->
						@endforelse
						</tbody>
					</table>
				</div>
			</div>

			<!-- Financial Aggregates Summary & Notes Grid -->
			<div class="bottom-grid margin-top-lg">
				<!-- Internal Notes -->
				<div class="info-card">
					<div class="card-header">
						<h3><i class="fas fa-sticky-note"></i> Internal Notes</h3>
					</div>
					<div class="card-body">
                        <textarea name="notes" id="notes" rows="6" class="form-control textarea-notes"
                                  placeholder="Type any internal operational notes or supplier communications here...">{{ old('notes', $order->notes) }}</textarea>
					</div>
				</div>

				<!-- Live Calculated Summary -->
				<div class="info-card financial-card">
					<div class="card-header">
						<h3><i class="fas fa-calculator"></i> Calculations Summary</h3>
					</div>
					<div class="card-body">
						<div class="detail-row">
							<span class="detail-label">Subtotal:</span>
							<span class="detail-value" id="calc-subtotal">$0.00</span>
						</div>
						<div class="detail-row">
							<span class="detail-label">Tax Amount (e.g. 24%):</span>
							<span class="detail-value text-muted" id="calc-tax">+ $0.00</span>
						</div>
						<div class="detail-row">
							<span class="detail-label">Discount Amount:</span>
							<span class="detail-value text-error" id="calc-discount">- $0.00</span>
						</div>
						<hr class="divider">
						<div class="detail-row total-row">
							<span class="detail-label">Grand Total:</span>
							<span class="detail-value grand-total" id="calc-grand-total">$0.00</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Form Submission Actions -->
			<div class="form-actions-bar margin-top-lg">
				<button type="submit" class="btn btn-success btn-lg">
					<i class="fas fa-save"></i> Save & Update Order
				</button>
				<a href="{{ route('inventory.purchases.show', $order->id) }}" class="btn btn-secondary btn-lg">
					Cancel Changes
				</a>
			</div>
		</form>
	</div>

	<!-- Template for adding dynamic new rows -->
	<template id="row-template">
		@include('templates.purchases.update_rows', ['index' => '_INDEX__'])
	</template>
@endsection

@section('scripts')
	<script type="application/javascript" src="{{ asset('js/purchases/edit.js') }}"></script>
@endsection
