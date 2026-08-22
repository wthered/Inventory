@extends('templates.general')

@section('title', 'Επεξεργασία Παραγγελίας Πώλησης #' . ($sale->order_number ?? $sale->id))

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/sales/edit.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<!-- Header Block -->
		<div class="page-header">
			<div class="header-title">
				<h1>Επεξεργασία Παραγγελίας</h1>
				<p class="subtitle">Τροποποίηση στοιχείων για την
					παραγγελία {{ $sale->order_number ?? '#' . $sale->id }}</p>
			</div>
			<div class="header-actions">
				<a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary">
					<i class="fas fa-arrow-left"></i> Ακύρωση / Επιστροφή
				</a>
			</div>
		</div>

		{{-- Display Session Success Message --}}
		@if (session('success'))
			<div class="alert alert-success"
			     style="padding: 1rem; margin-bottom: 1rem; border-radius: 4px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
				<i class="fas fa-check-circle"></i> {{ session('success') }}
			</div>
		@endif

		{{-- Display Session Error Message (e.g., from try/catch exceptions) --}}
		@if (session('error'))
			<div class="alert alert-danger"
			     style="padding: 1rem; margin-bottom: 1rem; border-radius: 4px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
				<i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
			</div>
		@endif

		{{-- Display Global Validation Errors if any --}}
		@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="margin-0">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form action="{{ route('inventory.sales.update', $sale->id) }}" method="POST" class="edit-form">
			@csrf
			@method('PUT')

			<div class="form-grid">
				<!-- LEFT COLUMN: Main Form Cards -->
				<div class="form-main">
					<!-- Core Details Card -->
					<div class="card">
						<div class="card-header">
							<h2><i class="fas fa-file-invoice"></i> Βασικά Στοιχεία Παραγγελίας</h2>
						</div>
						<div class="card-body">
							{{-- First Row: Customer & Warehouse --}}
							<div class="input-row">
								<div class="form-group">
									<label for="customer_id">Πελάτης:</label>
									<select name="customer_id" id="customer_id" class="form-select" disabled>
										<option value="{{ $sale->customer_id }}">
											{{ $sale->customer->name ?? 'Unknown Customer' }}
										</option>
									</select>
									<small class="text-muted">Ο πελάτης δεν μπορεί να αλλάξει μετά τη
										δημιουργία.</small>
								</div>

								<div class="form-group">
									<label for="warehouse_id">Αποθήκη Εξυπηρέτησης:</label>
									<select name="warehouse_id" id="warehouse_id"
									        class="form-select @error('warehouse_id') is-invalid @enderror">
										@foreach($warehouses as $warehouse)
											<option value="{{ $warehouse->id }}" {{ old('warehouse_id', $sale->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
												{{ $warehouse->name }}
											</option>
										@endforeach
									</select>
									@error('warehouse_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>
							</div>

							{{-- NEW Second Row: Order Status & Payment Status --}}
							<div class="input-row">
								<div class="form-group">
									<label for="status_id">Κατάσταση Παραγγελίας:</label>
									<select name="status_id" id="status_id"
									        class="form-select @error('status_id') is-invalid @enderror">
										@foreach($statuses::cases() as $status)
											<option value="{{ $status->value }}" {{ old('status_id', $sale->status_id->value ?? $sale->status_id) == $status->value ? 'selected' : '' }}>
												{{ $status->label() }}
											</option>
										@endforeach
									</select>
									@error('status_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>

								<div class="form-group">
									<label for="payment_status_id">Κατάσταση Πληρωμής:</label>
									<select name="payment_status_id" id="payment_status_id"
									        class="form-select @error('payment_status_id') is-invalid @enderror">
										@foreach($payments::cases() as $paymentStatus)
											<option value="{{ $paymentStatus->value }}" {{ old('payment_status_id', $sale->payment_status_id->value ?? $sale->payment_status_id) == $paymentStatus->value ? 'selected' : '' }}>
												{{ $paymentStatus->label() }}
											</option>
										@endforeach
									</select>
									@error('payment_status_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>
							</div>

							{{-- NEW Third Row: Dates & Employee --}}
							<div class="input-row">
								<div class="form-group">
									<label for="order_date">Ημερομηνία Παραγγελίας:</label>
									<input type="date"
									       name="order_date"
									       id="order_date"
									       class="form-control @error('order_date') is-invalid @enderror"
									       value="{{ old('order_date', $sale->order_date ? $sale->order_date->format('Y-m-d') : '') }}">
									@error('order_date') <span class="error-text">{{ $message }}</span> @enderror
								</div>

								<div class="form-group">
									<label for="shipping_date">Ημερομηνία Αποστολής:</label>
									<input type="date"
									       name="shipping_date"
									       id="shipping_date"
									       class="form-control @error('shipping_date') is-invalid @enderror"
									       value="{{ old('shipping_date', $sale->shipping_date ? $sale->shipping_date->format('Y-m-d') : '') }}">
									@error('shipping_date') <span class="error-text">{{ $message }}</span> @enderror
								</div>

								<div class="form-group">
									<label for="created_by">Καταχωρήθηκε από (Υπάλληλος):</label>
									<select name="created_by" id="created_by" class="form-select" disabled>
										<option value="">
											{{ $sale->creator->account->fullName ?? $sale->creator->name ?? 'User #' . $sale->created_by }}
										</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Order Items Card -->
					<div class="card">
						<div class="card-header">
							<h2><i class="fas fa-boxes"></i> Προϊόντα Παραγγελίας</h2>
						</div>

						<!-- ADD PRODUCT BAR -->
						<div class="card-body bg-light border-bottom">
							<div class="product-picker-container">
								{{-- 1η Σειρά: Κύρια & Υποκατηγορία --}}
								<div class="input-row">
									<div class="form-group mb-0">
										<label for="filter_category">Κύρια Κατηγορία:</label>
										<select id="filter_category" class="form-select">
											<option value="">-- Όλες οι Κατηγορίες --</option>
											@foreach($categories as $index => $name)
												<option value="{{ $index }}">{{ $name }}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group mb-0">
										<label for="filter_subcategory">Υποκατηγορία:</label>
										<select id="filter_subcategory" class="form-select" disabled>
											<option value="">-- Όλες οι Υποκατηγορίες --</option>
										</select>
									</div>
								</div>

								{{-- 2η Σειρά: Brand (Μάρκα) --}}
								<div class="input-row">
									<div class="form-group mb-0">
										<label for="filter_brand">Brand / Μάρκα:</label>
										<select id="filter_brand" class="form-select" disabled>
											<option value="">-- Όλα τα Brands --</option>
										</select>
									</div>
								</div>

								{{-- 3η Σειρά: Επιλογή Προϊόντος & Κουμπί Προσθήκης --}}
								<div class="picker-action-row">
									<div class="form-group mb-0 position-relative flex-grow-1">
										<label for="product_search_input">Αναζήτηση Προϊόντος:</label>

										<!-- Search Input -->
										<input type="text"
										       id="product_search_input"
										       class="form-control"
										       placeholder="Πληκτρολογήστε όνομα ή SKU..."
										       autocomplete="off"
										       disabled>

										<!-- Hidden input to store selected product payload -->
										<input type="hidden" id="selected_product_data" value="">

										<!-- Autocomplete Suggestions Dropdown -->
										<div id="product_autocomplete_results" class="d-none"></div>
									</div>

									<div class="form-group mb-0 align-self-end">
										<button type="button" id="btn_add_product" class="btn btn-success" disabled>
											<i class="fas fa-plus"></i> Προσθήκη
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- TABLE -->
						<div class="table-responsive">
							<table class="edit-table" id="order_items_table">
								<!-- Update Table Header -->
								<thead>
								<tr>
									<th>Προϊόν</th>
									<th class="text-center" style="width: 110px;">Ποσότητα</th>
									<th class="text-right" style="width: 130px;">Τιμή Μονάδας</th>
									<th class="text-center" style="width: 110px;">Έκπτωση (%)</th>
									<th class="text-right" style="width: 140px;">Σύνολο Γραμμής</th>
									<th class="text-center" style="width: 60px;">Ενέργεια</th>
								</tr>
								</thead>
								<tbody>
								@foreach($sale->items as $item)
									<tr>
										<!-- Product Name & Hidden Inputs -->
										<td>
											<span class="item-name">{{ $item->product->name }}</span>
											<span class="item-sku text-muted">{{ $item->product->sku }}</span>
											<input type="hidden" class="input-product-id"
											       name="items[{{ $loop->index }}][product_id]"
											       value="{{ $item->product_id }}">
										</td>

										<!-- Quantity -->
										<td>
											<input type="number" step="1" min="1"
											       class="form-control input-qty"
											       name="items[{{ $loop->index }}][quantity_ordered]"
											       value="{{ $item->quantity_ordered }}">
										</td>

										<!-- Unit Price -->
										<td>
											<input type="number" step="0.01"
											       class="form-control input-price"
											       name="items[{{ $loop->index }}][unit_price]"
											       value="{{ $item->unit_price }}">
										</td>

										<!-- Item Discount (%) -->
										<td>
											<input type="number" step="0.01" min="0" max="100"
											       class="form-control input-discount"
											       name="items[{{ $loop->index }}][discount_rate]"
											       value="{{ old("items.{$loop->index}.discount_rate", $item->discount_rate ?? 0) }}">
										</td>

										<!-- Line Total Display -->
										@php
											$lineSubtotal = $item->quantity_ordered * $item->unit_price;
											$lineDiscount = $lineSubtotal * (($item->discount_rate ?? 0) / 100);
										@endphp
										<td class="row-total-display">
											€{{ number_format($lineSubtotal - $lineDiscount, 2, ',', '.') }}
										</td>

										<!-- Delete Action -->
										<td>
											<button type="button" class="btn btn-remove-item">&times;</button>
										</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<!-- Template for adding new rows via JS -->
					<template id="new_item_row_template">
						<tr>
							<td>
								<span class="item-name"></span>
								<span class="item-sku text-muted"></span>
								<input type="hidden" class="input-product-id" name="" value="">
							</td>
							<td>
								<input type="number" step="1" min="1" class="form-control input-qty" name="" value="1">
							</td>
							<td>
								<input type="number" step="0.01" class="form-control input-price" name="" value="0.00">
							</td>
							<td>
								<input type="number" step="0.01" min="0" max="100" class="form-control input-discount"
								       name="" value="0">
							</td>
							<td class="row-total-display">€0.00</td>
							<td>
								<button type="button" class="btn btn-remove-item">&times;</button>
							</td>
						</tr>
					</template>
				</div>

				<!-- RIGHT COLUMN: Sidebar Form Cards -->
				<div class="form-sidebar">
					<!-- Notes & Submit Card -->
					<div class="card card-accent">
						<div class="card-header">
							<h3><i class="fas fa-comment-alt"></i> Σημειώσεις / Οδηγίες</h3>
						</div>
						<div class="card-body">
							<div class="form-group">
								<textarea name="notes" id="notes" rows="6" class="form-control"
								          placeholder="Προσθέστε παρατηρήσεις ή οδηγίες παράδοσης...">{{ old('notes', $sale->notes) }}</textarea>
							</div>

							<hr class="form-divider">

							<div class="sidebar-totals" id="sidebar_totals" data-subtotal="{{ $sale->subtotal }}"
							     data-tax="{{ $sale->tax_amount }}">

								<!-- Subtotal -->
								<div class="total-row">
									<span class="total-label">Μερικό Σύνολο (Subtotal):</span>
									<span class="font-numeric"
									      id="subtotal_display">€{{ number_format($sale->subtotal, 2) }}</span>
								</div>

								<!-- Discount Input -->
								<div class="total-row total-row-input">
									<label for="discount_amount">Έκπτωση (€):</label>
									<input type="number"
									       step="0.01"
									       min="0"
									       name="discount_amount"
									       id="discount_amount"
									       class="form-control font-numeric discount-input"
									       value="{{ old('discount_amount', $sale->discount_amount) }}">
								</div>

								<!-- Tax -->
								<div class="total-row">
									<span class="total-label">ΦΠΑ (Tax):</span>
									<span class="font-numeric"
									      id="tax_display">€{{ number_format($sale->tax_amount, 2) }}</span>
									<input type="hidden" value="{{ number_format($sale->tax_amount, 2) }}"
									       id="tax_hidden" name="tax_amount">
								</div>

								<hr class="form-divider">

								<!-- Grand Total -->
								<div class="total-row total-row-grand">
									<span class="total-label">Συνολικό Ποσό (Grand Total):</span>
									<span class="font-numeric price-tag"
									      id="grand_total">€{{ number_format($sale->grand_total, 2) }}</span>
								</div>
							</div>

							<button type="submit" class="btn btn-primary btn-block">
								<i class="fas fa-save"></i> Αποθήκευση Αλλαγών
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
@endsection

@section('scripts')
	{{-- 1. Shared common functions & table logic MUST load first --}}
	<script src="{{ asset('js/sales/items/common.js') }}"></script>

	{{-- 2. Page specific edit scripts load second --}}
	<script src="{{ asset('js/sales/edit.js') }}"></script>
	<script src="{{ asset('js/sales/items/edit.js') }}"></script>
@endsection