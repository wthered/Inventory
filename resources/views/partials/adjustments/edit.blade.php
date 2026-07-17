{{-- resources/views/partials/adjustments/edit.blade.php --}}
<template id="new-item-template">
	<div class="item-row-card new-item" data-index="__INDEX__">

		{{-- Header Γραμμής --}}
		<div class="item-row-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px dashed #e2e8f0; padding-bottom: 0.5rem;">
			<div class="item-row-title">
				<div>📦 Γραμμή #: Νέα Γραμμή Προϊόντος</div>
			</div>
			<button type="button" class="btn btn-danger remove-item-btn">Αφαίρεση</button>
		</div>

		{{-- Grid Φόρμας --}}
		<div class="form-grid">

			{{-- 1. Category Select --}}
			<div class="form-group">
				<label class="form-label" for="category___INDEX__">Κατηγορία</label>
				<select class="form-input category-select" data-item-id="__INDEX__" id="category___INDEX__">
					<option value="">Επιλέξτε Κατηγορία...</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}">{{ $category->name }}</option>
					@endforeach
				</select>
			</div>

			{{-- 2. Brand Select --}}
			<div class="form-group">
				<label class="form-label" for="brand___INDEX__">Μάρκα</label>
				<select class="form-input brand-select" data-item-id="__INDEX__" id="brand___INDEX__" disabled>
					<option value="">Επιλέξτε Μάρκα...</option>
				</select>
			</div>

			{{-- 3. Search Input & Product Select (AJAX) --}}
			<div class="form-group">
				<label class="form-label" for="product___INDEX__">Αναζήτηση & Επιλογή Προϊόντος</label>
				<input type="text" class="form-input product-search-input" id="product___INDEX__" data-item-id="__INDEX__" placeholder="Πληκτρολογήστε για φιλτράρισμα..." autocomplete="off">

				<select name="items[__INDEX__][product_id]" class="form-input product-ajax-select" data-item-id="__INDEX__" required style="margin-top: 0.5rem;">
					<option value="">Πληκτρολογήστε για αναζήτηση...</option>
				</select>
			</div>

			{{-- 4. Location Dropdown --}}
			<div class="form-group">
				<label class="form-label" for="location___INDEX__">Θέση Αποθήκης</label>
				<select name="items[__INDEX__][warehouse_location_id]" class="form-input" id="location___INDEX__" required>
					<option value="" disabled selected>Επιλέξτε Θέση...</option>
					@foreach($locations as $location)
						<option value="{{ $location->id }}">{{ $location->name }} ({{ $location->code }})</option>
					@endforeach
				</select>
			</div>

			{{-- 5. Reason Dropdown --}}
			<div class="form-group">
				<label class="form-label" for="reason___INDEX__">Αιτιολογία</label>
				<select name="items[__INDEX__][reason]" class="form-input" id="reason___INDEX__" required>
					<option value="" disabled selected>Επιλέξτε Αιτιολογία...</option>
					@foreach($reasons as $groupLabel => $options)
						<optgroup label="{{ $groupLabel }}">
							@foreach($options as $value => $label)
								<option value="{{ $value }}">{{ $label }}</option>
							@endforeach
						</optgroup>
					@endforeach
				</select>
			</div>

			{{-- 6. Movement Type --}}
			<div class="form-group">
				<label class="form-label" for="type___INDEX__">Τύπος Κίνησης</label>
				<select name="items[__INDEX__][type]" class="form-input" id="type___INDEX__" required>
					<option value="{{ $types::INCREASE->value }}" selected>Αύξηση (+)</option>
					<option value="{{ $types::DECREASE->value }}">Μείωση (-)</option>
				</select>
			</div>

			{{-- 7. Quantity --}}
			<div class="form-group">
				<label class="form-label">Ποσότητα Μεταβολής</label>
				<input type="number" name="items[__INDEX__][quantity]" class="form-input" min="1" value="1" required>
			</div>

		</div>
	</div>
</template>