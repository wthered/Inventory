<tr class="item-row" data-index="{{ $index }}">
	<!-- Product Details Cascading Column -->
	<td>
		<div class="cascade-selectors">
			<!-- Parent Category Selector -->
			<div class="form-group margin-bottom-xs">
				<select name="items[{{ $index }}][parent_category_id]"
				        class="form-select parent-category-select @error("items.".$index.".parent_category_id") is-invalid @enderror"
				        required>
					<option value="">Select Parent Category</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}"
								@selected(old("items.".$index.".parent_category_id", data_get($item, 'product.category.parent_id', data_get($item, 'parent_category_id'))) == $category->id)>
							{{ $category->name }}
						</option>
					@endforeach
				</select>
				@error("items.".$index.".parent_category_id")
				<span class="invalid-feedback">{{ $message }}</span>
				@enderror
			</div>

			<!-- Subcategory Selector -->
			<div class="form-group margin-bottom-xs">
				<select name="items[{{ $index }}][category_id]"
				        class="form-select category-select @error("items.".$index.".category_id") is-invalid @enderror"
				        data-selected-id="{{ old("items.".$index.".category_id", data_get($item, 'product.category_id', data_get($item, 'category_id'))) }}"
				        disabled required>
					<option value="">Select Subcategory</option>
				</select>
				@error("items.".$index.".category_id")
				<span class="invalid-feedback">{{ $message }}</span>
				@enderror
			</div>

			<!-- Brand Selector -->
			<div class="form-group margin-bottom-xs">
				<select name="items[{{ $index }}][brand_id]"
				        class="form-select brand-select @error("items.".$index.".brand_id") is-invalid @enderror"
				        data-selected-id="{{ old("items.".$index.".brand_id", data_get($item, 'product.brand_id', data_get($item, 'brand_id'))) }}"
				        disabled required>
					<option value="">Select Brand</option>
				</select>
				@error("items.".$index.".brand_id")
				<span class="invalid-feedback">{{ $message }}</span>
				@enderror
			</div>

			<!-- Product Selector -->
			<div class="form-group">
				<select name="items[{{ $index }}][product_id]"
				        class="form-select product-select @error("items.".$index.".product_id") is-invalid @enderror"
				        data-product="{{ old("items.".$index.".product_id", data_get($item, 'product_id')) }}"
				        disabled required>
					<option value="">Select Product</option>
				</select>
				@error("items.".$index.".product_id")
				<span class="invalid-feedback">{{ $message }}</span>
				@enderror
			</div>
		</div>
	</td>

	<!-- Batch Reference -->
	<td>
		<div class="batch-inputs">
			<input type="text"
			       name="items[{{ $index }}][batch_number]"
			       class="form-control form-control-sm @error("items.".$index.".batch_number") is-invalid @enderror"
			       placeholder="Batch #"
			       value="{{ old("items.".$index.".batch_number", data_get($item, 'batch_number')) }}">
			@error("items.".$index.".batch_number")
			<span class="invalid-feedback">{{ $message }}</span>
			@enderror

			<input type="date"
			       name="items[{{ $index }}][manufacturing_date]"
			       class="form-control form-control-sm margin-top-xs @error("items.".$index.".manufacturing_date") is-invalid @enderror"
			       title="Manufacturing Date"
			       value="{{ old("items.".$index.".manufacturing_date", data_get($item, 'manufacturing_date') ?? '') }}">
			@error("items.".$index.".manufacturing_date")
			<span class="invalid-feedback">{{ $message }}</span>
			@enderror

			<input type="date"
			       name="items[{{ $index }}][expiry_date]"
			       class="form-control form-control-sm margin-top-xs @error("items.".$index.".expiry_date") is-invalid @enderror"
			       title="Expiry Date"
			       value="{{ old("items.".$index.".expiry_date", data_get($item, 'expiry_date') ?? '') }}">
			@error("items.".$index.".expiry_date")
			<span class="invalid-feedback">{{ $message }}</span>
			@enderror
		</div>
		<input type="hidden" name="items[{{ $index }}][location_id]"
		       value="{{ old("items.".$index.".location_id", data_get($item, 'location_id')) }}">
	</td>

	<!-- Quantity -->
	<td class="text-center">
		<input type="number" name="items[{{ $index }}][quantity_ordered]"
		       class="form-control text-center qty-input @error("items.".$index.".quantity_ordered") is-invalid @enderror"
		       min="1" step="any" required
		       value="{{ old("items.".$index.".quantity_ordered", data_get($item, 'quantity_ordered', 1)) }}">
		@error("items.".$index.".quantity_ordered")
		<span class="invalid-feedback d-block">{{ $message }}</span>
		@enderror
	</td>
	<td class="text-center">
		<input type="number"
		       name="items[{{ $index }}][quantity_received]" class="form-control text-center qty-input @error("items.".$index.".quantity_received") is-invalid @enderror" min="0" step="any"
		       value="{{ old("items.".$index.".quantity_received", data_get($item, 'quantity_received', 0)) }}">
		@error("items.".$index.".quantity_received")
		<span class="invalid-feedback d-block">{{ $message }}</span>
		@enderror
	</td>

	<!-- Cost Price -->
	<td class="text-right">
		<input type="number" name="items[{{ $index }}][unit_price]"
		       class="form-control text-right price-input @error("items.".$index.".unit_price") is-invalid @enderror"
		       min="0" step="0.01" required
		       value="{{ old("items.".$index.".unit_price", sprintf('%.2f', data_get($item, 'unit_price', 0))) }}">
		@error("items.".$index.".unit_price")
		<span class="invalid-feedback d-block">{{ $message }}</span>
		@enderror
	</td>

	<!-- Discount -->
	<td class="text-center">
		<input type="number" name="items[{{ $index }}][discount_rate]"
		       class="form-control text-center discount-input @error("items.".$index.".discount_rate") is-invalid @enderror"
		       min="0" max="100" step="0.01"
		       value="{{ old("items.".$index.".discount_rate", sprintf('%.2f', data_get($item, 'discount_rate', 0))) }}">
		@error("items.".$index.".discount_rate")
		<span class="invalid-feedback d-block">{{ $message }}</span>
		@enderror
	</td>

	<!-- Live Subtotal Display -->
	<td class="text-right middle-align">
		<span class="row-total fw-bold">$0.00</span>
	</td>

	<!-- Delete Row Action -->
	<td class="text-center middle-align">
		<button type="button" class="btn-delete remove-row-btn" title="Remove Line Item">
			<i class="fas fa-trash-alt"></i>
		</button>
	</td>
</tr>