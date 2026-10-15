@extends('templates.general')

@section('content')
	<h1>Create Invoice</h1>

	<form action="{{ route('invoices.store') }}" method="POST">
		@csrf

		<label>Invoice Number</label>
		<input type="text" name="invoice_number" value="{{ old('invoice_number') }}">
		@error('invoice_number') <div>{{ $message }}</div> @enderror

		<label>Customer</label>
		<select name="customer_id">
			@foreach($customers as $customer)
				<option value="{{ $customer->id }}">{{ $customer->name }}</option>
			@endforeach
		</select>
		@error('customer_id') <div>{{ $message }}</div> @enderror

		<label>Invoice Date</label>
		<input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}">
		@error('invoice_date') <div>{{ $message }}</div> @enderror

		<label>Due Date</label>
		<input type="date" name="due_date" value="{{ old('due_date') }}">
		@error('due_date') <div>{{ $message }}</div> @enderror

		<hr>
		<h3>Items</h3>

		<div id="items-container">
			<div class="item-row">
				<select name="items[0][product_id]">
					@foreach($products as $product)
						<option value="{{ $product->id }}">{{ $product->name }}</option>
					@endforeach
				</select>
				<input type="number" name="items[0][quantity]" value="1" min="1">
				<input type="number" step="0.01" name="items[0][unit_price]" value="0">
			</div>
		</div>

		<button type="button" id="add-item">Add Another Item</button>

		<button type="submit">Create Invoice</button>
	</form>

	<script>
		let itemIndex = 1;
		document.getElementById('add-item').addEventListener('click', function() {
			const container = document.getElementById('items-container');
			const row = document.createElement('div');
			row.classList.add('item-row');
			row.innerHTML = `
        <select name="items[${itemIndex}][product_id]">
            @foreach($products as $product)
			<option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
			</select>
			<input type="number" name="items[${itemIndex}][quantity]" value="1" min="1">
        <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" value="0">
    `;
			container.appendChild(row);
			itemIndex++;
		});
	</script>
@endsection