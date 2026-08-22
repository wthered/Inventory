@extends('templates.general')

@section('title', 'Create Purchase Order')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/purchases/create.css') }}"/>
@endsection

@section('content')
	<div class="main-container">
		<!-- Header Block -->
		<div class="page-header">
			<div class="header-title">
				<div class="title-with-badge">
					<h1>Create New Purchase Order</h1>
					<span class="badge badge-draft">Draft</span>
				</div>
				<p class="subtitle">Prepare a new procurement order to submit to a supplier</p>
			</div>

			<div class="header-actions">
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

		<form action="{{ route('inventory.purchases.store') }}" method="POST" id="create-purchase-form">
			@csrf

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
										value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
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
										value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
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
							       value="{{ old('order_date', date('Y-m-d')) }}"
							       required>
							@error('order_date')
							<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

						<div class="form-group">
							<label for="expected_date" class="form-label">Expected Delivery Date</label>
							<input type="date" name="expected_date" id="expected_date"
							       class="form-control @error('expected_date') is-invalid @enderror"
							       value="{{ old('expected_date') }}">
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
							<th class="text-right" style="width: 160px;">Unit Cost ($) <span class="text-error">*</span>
							</th>
							<th class="text-center" style="width: 130px;">Discount %</th>
							<th class="text-right" style="width: 160px;">Total</th>
							<th class="text-center" style="width: 70px;">Action</th>
						</tr>
						</thead>
						<tbody id="items-tbody">
						@if(old('items'))
							@foreach(old('items') as $index => $item)
								<tr class="item-row" data-index="{{ $index }}">
									<td>
										<div class="cascade-selectors">
											<div class="form-group margin-bottom-xs">
												<select name="items[{{ $index }}][category_id]"
												        class="form-select category-select @error("items.{$index}.category_id") is-invalid @enderror"
												        required>
													<option value="">Select Category</option>
													@foreach($categories as $category)
														<option
															value="{{ $category->id }}" @selected(old("items.{$index}.category_id") == $category->id)>
															{{ $category->name }}
														</option>
													@endforeach
												</select>
												@error("items.{$index}.category_id")
												<span class="invalid-feedback">{{ $message }}</span>
												@enderror
											</div>

											<div class="form-group margin-bottom-xs">
												<select name="items[{{ $index }}][brand_id]"
												        class="form-select brand-select @error("items.{$index}.brand_id") is-invalid @enderror"
												        required>
													<option value="">Select Brand</option>
												</select>
												@error("items.{$index}.brand_id")
												<span class="invalid-feedback">{{ $message }}</span>
												@enderror
											</div>

											<div class="form-group">
												<select name="items[{{ $index }}][product_id]"
												        class="form-select product-select @error("items.{$index}.product_id") is-invalid @enderror"
												        required>
													<option value="">Select Product</option>
												</select>
												@error("items.{$index}.product_id")
												<span class="invalid-feedback">{{ $message }}</span>
												@enderror
											</div>
										</div>
									</td>

									<td>
										<div class="batch-inputs">
											<input type="text" name="items[{{ $index }}][batch_number]"
											       class="form-control form-control-sm @error("items.{$index}.batch_number") is-invalid @enderror"
											       placeholder="Batch #"
											       value="{{ old("items.{$index}.batch_number") }}">
											@error("items.{$index}.batch_number")
											<span class="invalid-feedback">{{ $message }}</span>
											@enderror

											<input type="date" name="items[{{ $index }}][expiry_date]"
											       class="form-control form-control-sm margin-top-xs @error("items.{$index}.expiry_date") is-invalid @enderror"
											       title="Expiry Date"
											       value="{{ old("items.{$index}.expiry_date") }}">
											@error("items.{$index}.expiry_date")
											<span class="invalid-feedback">{{ $message }}</span>
											@enderror
										</div>
									</td>

									<td class="text-center">
										<input type="number" name="items[{{ $index }}][quantity_ordered]"
										       class="form-control text-center qty-input @error("items.{$index}.quantity_ordered") is-invalid @enderror"
										       min="1" step="any" required
										       value="{{ old("items.{$index}.quantity_ordered", 1) }}">
										@error("items.{$index}.quantity_ordered")
										<span class="invalid-feedback d-block">{{ $message }}</span>
										@enderror
									</td>

									<td class="text-right">
										<input type="number" name="items[{{ $index }}][unit_price]"
										       class="form-control text-right price-input @error("items.{$index}.unit_price") is-invalid @enderror"
										       min="0" step="0.01" required
										       value="{{ old("items.{$index}.unit_price", '0.00') }}">
										@error("items.{$index}.unit_price")
										<span class="invalid-feedback d-block">{{ $message }}</span>
										@enderror
									</td>

									<td class="text-center">
										<input type="number" name="items[{{ $index }}][discount_rate]"
										       class="form-control text-center discount-input @error("items.{$index}.discount_rate") is-invalid @enderror"
										       min="0" max="100" step="0.01"
										       value="{{ old("items.{$index}.discount_rate", '0.00') }}">
										@error("items.{$index}.discount_rate")
										<span class="invalid-feedback d-block">{{ $message }}</span>
										@enderror
									</td>

									<td class="text-right middle-align">
										<span class="row-total fw-bold">$0.00</span>
									</td>

									<td class="text-center middle-align">
										<button type="button" class="btn-delete remove-row-btn"
										        title="Remove Line Item">
											<i class="fas fa-trash-alt"></i>
										</button>
									</td>
								</tr>
							@endforeach
						@endif
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
                                  placeholder="Type any internal operational notes or supplier communications here...">{{ old('notes') }}</textarea>
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
					<i class="fas fa-plus-circle"></i> Create Purchase Order
				</button>
				<a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary btn-lg">
					Cancel
				</a>
			</div>
		</form>
	</div>

	<!-- Template for adding dynamic new rows -->
	<template id="row-template">
		<tr class="item-row" data-index="__INDEX__">
			<td>
				<div class="cascade-selectors">
					<div class="form-group margin-bottom-xs">
						<select class="form-select category-select" name="items[__INDEX__][category_id]" required>
							<option value="">Select Category</option>
							@foreach($categories as $category)
								<option value="{{ $category->id }}">{{ $category->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="form-group margin-bottom-xs">
						<select class="form-select brand-select" name="items[__INDEX__][brand_id]" disabled required>
							<option value="">Select Category first</option>
						</select>
					</div>

					<div class="form-group">
						<select class="form-select product-select" name="items[__INDEX__][product_id]" disabled
						        required>
							<option value="">Select Brand first</option>
						</select>
					</div>
				</div>
			</td>

			<td>
				<div class="batch-inputs">
					<input type="text" name="items[__INDEX__][batch_number]" class="form-control form-control-sm"
					       placeholder="Batch #">
					<input type="date" name="items[__INDEX__][expiry_date]"
					       class="form-control form-control-sm margin-top-xs" title="Expiry Date">
				</div>
			</td>

			<td class="text-center">
				<input type="number" name="items[__INDEX__][quantity_ordered]"
				       class="form-control text-center qty-input" value="1" min="1" step="any" required>
			</td>

			<td class="text-right">
				<input type="number" name="items[__INDEX__][unit_price]" class="form-control text-right price-input"
				       step="0.01" value="0.00" min="0" required>
			</td>

			<td class="text-center">
				<input type="number" name="items[__INDEX__][discount_rate]"
				       class="form-control text-center discount-input" step="0.01" value="0.00" min="0" max="100"
				       required>
			</td>

			<td class="text-right middle-align">
				<span class="row-total fw-bold">$0.00</span>
			</td>

			<td class="text-center middle-align">
				<button type="button" class="btn-delete remove-row-btn" title="Remove Line Item">
					<i class="fas fa-trash-alt"></i>
				</button>
			</td>
		</tr>
	</template>
@endsection

@section('scripts')
	<script type="application/javascript" src="{{ asset('js/purchases/create.js') }}"></script>
@endsection
