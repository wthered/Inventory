@extends('templates.general')

@section('title', 'Δημιουργία Νέας Πώλησης')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/sales/create.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<!-- Header Block -->
		<div class="page-header">
			<div class="header-title-group">
				<h1><i class="fas fa-shopping-bag icon-accent"></i> Νέα Πώληση</h1>
				<p class="subtitle">Καταχώρηση νέας παραγγελίας πώλησης στο σύστημα</p>
			</div>
			<div class="header-actions">
				<a href="{{ route('inventory.sales.index') }}" class="btn btn-secondary">
					<i class="fas fa-arrow-left"></i> Ακύρωση / Επιστροφή
				</a>
			</div>
		</div>

		{{-- Display Session Error Message --}}
		@if (session('error'))
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
			</div>
		@endif

		{{-- Display Global Validation Errors --}}
		@if ($errors->any())
			<div class="alert alert-danger">
				<ul style="margin: 0; padding-left: 1.25rem;">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form action="{{ route('inventory.sales.store') }}" method="POST" class="edit-form">
			@csrf

			<div class="form-grid">
				<!-- LEFT COLUMN: Main Form Cards -->
				<div class="form-main">
					<!-- Core Details Card -->
					<div class="card">
						<div class="card-header">
							<h2><i class="fas fa-file-invoice icon-accent"></i> Βασικά Στοιχεία Παραγγελίας</h2>
						</div>
						<div class="card-body">
							{{-- First Row: Customer & Warehouse --}}
							<div class="input-row">
								<div class="form-group">
									<label for="customer_id">Πελάτης: <span class="error-text">*</span></label>
									<select name="customer_id" id="customer_id"
									        class="form-select @error('customer_id') is-invalid @enderror" required>
										<option value="" disabled selected>-- Επιλέξτε Πελάτη --</option>
										@foreach($customers as $groupName => $customerGroup)
											<optgroup label="{{ $groupName }}">
												@foreach($customerGroup as $customer)
													<option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
														{{ $customer->name }} {{ $customer->company_name ? "({$customer->company_name})" : '' }}
													</option>
												@endforeach
											</optgroup>
										@endforeach
									</select>
									@error('customer_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>

								<div class="form-group">
									<label for="warehouse_id">Αποθήκη Εξυπηρέτησης: <span
												class="error-text">*</span></label>
									<select name="warehouse_id" id="warehouse_id"
									        class="form-select @error('warehouse_id') is-invalid @enderror" required>
										<option value="" disabled selected>-- Επιλέξτε Αποθήκη --</option>
										@foreach($warehouses as $warehouse)
											<option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
												{{ $warehouse->name }}
											</option>
										@endforeach
									</select>
									@error('warehouse_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>
							</div>

							{{-- Second Row: Order Date & Employee Assignment --}}
							<div class="input-row">
								<div class="form-group">
									<label for="order_date">Ημερομηνία Παραγγελίας: <span
												class="error-text">*</span></label>
									<input type="date"
									       name="order_date"
									       id="order_date"
									       class="form-control @error('order_date') is-invalid @enderror"
									       value="{{ old('order_date', now()->format('Y-m-d')) }}"
									       required>
									@error('order_date') <span class="error-text">{{ $message }}</span> @enderror
								</div>

								<div class="form-group">
									<label for="employee_id">Ανάθεση σε Υπάλληλο: <span
												class="error-text">*</span></label>
									<select name="employee_id"
									        id="employee_id"
									        class="form-select @error('employee_id') is-invalid @enderror"
									        required>
										<option value="">-- Επιλέξτε Υπάλληλο --</option>
										@foreach($employees as $id => $fullName)
											<option value="{{ $id }}" {{ old('employee_id', $sale->employee_id ?? '') == $id ? 'selected' : '' }}>
												{{ $fullName }}
											</option>
										@endforeach
									</select>
									@error('employee_id') <span class="error-text">{{ $message }}</span> @enderror
								</div>
							</div>
						</div>
					</div>

					<!-- Order Items Card -->
					<div class="card">
						<div class="card-header">
							<h2><i class="fas fa-boxes icon-accent"></i> Προϊόντα Παραγγελίας</h2>
						</div>

						<!-- ADD PRODUCT BAR (AJAX Filtering) -->
						<div class="card-body bg-light border-bottom">
							<div class="product-picker-container">
								{{-- 1η Σειρά: Κύρια & Υποκατηγορία --}}
								<div class="input-row">
									<div class="form-group">
										<label for="filter_category">Κύρια Κατηγορία:</label>
										<select id="filter_category" class="form-select">
											<option value="">-- Όλες οι Κατηγορίες --</option>
											@foreach($categories as $index => $name)
												<option value="{{ $index }}" @selected(old('filter_category') == $index)>
													{{ $name }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="form-group">
										<label for="filter_subcategory">Υποκατηγορία:</label>
										<select id="filter_subcategory" class="form-select" disabled>
											<option value="">-- Όλες οι Υποκατηγορίες --</option>
										</select>
									</div>
								</div>

								{{-- 2η Σειρά: Brand (Μάρκα) --}}
								<div class="input-row">
									<div class="form-group">
										<label for="filter_brand">Brand / Μάρκα:</label>
										<select id="filter_brand" class="form-select" disabled>
											<option value="">-- Όλα τα Brands --</option>
										</select>
									</div>
								</div>

								{{-- 3η Σειρά: Επιλογή Προϊόντος & Κουμπί Προσθήκης --}}
								<div class="picker-action-row">
									<div class="form-group position-relative flex-grow-1">
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

									<div class="form-group align-self-end">
										<button type="button" id="btn_add_product" class="btn btn-success" disabled>
											<i class="fas fa-plus"></i> Προσθήκη
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- TABLE -->
						<div class="table-wrapper">
							<table class="edit-table" id="order_items_table">
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
								{{-- Dynamically populated via JS --}}
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
							<td class="row-total-display text-right">€0,00</td>
							<td class="text-center">
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
							<h3><i class="fas fa-comment-alt icon-accent"></i> Σημειώσεις / Οδηγίες</h3>
						</div>
						<div class="card-body">
							<div class="form-group">
                                <textarea name="notes" id="notes" rows="6" class="form-control"
                                          placeholder="Προσθέστε παρατηρήσεις ή οδηγίες παράδοσης...">{{ old('notes') }}</textarea>
							</div>

							<hr class="form-divider">

							<div class="sidebar-totals" id="sidebar_totals" data-subtotal="0.00" data-tax="0.00">
								<!-- Subtotal -->
								<div class="total-row">
									<span class="total-label">Μερικό Σύνολο (Subtotal):</span>
									<span class="font-numeric" id="subtotal_display">€0,00</span>
								</div>

								<!-- Discount Input -->
								<div class="total-row total-row-input">
									<label for="discount_amount">Έκπτωση (€):</label>
									<input type="number" step="0.01" name="discount_amount" id="discount_amount"
									       class="form-control font-numeric discount-input"
									       value="{{ old('discount_amount', '0.00') }}">
								</div>

								<!-- Tax -->
								<div class="total-row">
									<span class="total-label">ΦΠΑ (24%):</span>
									<span class="font-numeric" id="tax_display">€0,00</span>
								</div>

								<hr class="form-divider">

								<!-- Grand Total -->
								<div class="total-row total-row-grand">
									<span class="total-label">Συνολικό Ποσό (Grand Total):</span>
									<span class="font-numeric price-tag" id="grand_total">€0,00</span>
								</div>
							</div>

							<button type="submit" class="btn btn-primary btn-block">
								<i class="fas fa-save"></i> Καταχώρηση Παραγγελίας
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

	{{-- 2. Page specific create scripts load second --}}
	<script src="{{ asset('js/sales/create.js') }}"></script>
	<script src="{{ asset('js/sales/items/create.js') }}"></script>
@endsection