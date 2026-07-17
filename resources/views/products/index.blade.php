@extends('templates.general')

@section('title', 'Products')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/products/index.css') }}" />
	<link rel="stylesheet" href="{{ asset('css/pagination.css') }}" />
@endsection

@section('content')
	<section id="products-container">
		<!-- Header -->
		<div class="page-header">
			<h1>Products</h1>
			<div class="actions">
				<input type="search" placeholder="Search products..." class="search-input" />
				<a href="#" class="btn add">+ Add Product</a>
			</div>
		</div>

		<!-- Summary Cards -->
		<div class="row">
			<div id="deck">
				<div class="card">
					<div class="card-header">
						<i class="fas fa-box"></i>
						<h3>Total Products</h3>
					</div>
					<p>{{ number_format($product_count) }}</p>
				</div>
				<div class="card">
					<div class="card-header">
						<i class="fas fa-exclamation-triangle"></i>
						<h3>Low Stock</h3>
					</div>
					<p>{{ $low_stock }}</p>
				</div>
				<div class="card">
					<div class="card-header">
						<i class="fas fa-times-circle"></i>
						<h3>Out of Stock</h3>
					</div>
					<p>{{ $out_of_stock }}</p>
				</div>
				<div class="card">
					<div class="card-header">
						<i class="fas fa-coins"></i>
						<h3>Total Inventory Value</h3>
					</div>
					<p>€{{ number_format($total_value, 2) }}</p>
				</div>
			</div>
		</div>

		<!-- Filters -->
		<div class="filters">
			<select class="filter" name="category" id="parent_category">
				<option value="">All Categories</option>
				@foreach($categories as $category)
					<option value="{{ $category->id }}">{{ $category['name'] }}</option>
				@endforeach
			</select>
            <select class="filter" name="child_category" id="child_category">
				<option value="">All Sub Categories</option>
			</select>
			<select class="filter" name="supplier" id="filter_supplier">
				<option value="">All Suppliers</option>
				@foreach($suppliers as $supplier)
					<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
				@endforeach
			</select>
			<select class="filter" name="stock" id="filter_stock">
				<option value="">Stock Status</option>
				<option value="Stock">In Stock</option>
				<option value="Low">Low Stock</option>
				<option value="Out">Out of Stock</option>
			</select>
		</div>

		<!-- Product Table -->
		<div class="table-container">
			<div class="products-container">
				<div class="products-header">
					<div>Image</div>
					<div>Name</div>
					<div>SKU</div>
					<div>Category</div>
					<div>Supplier</div>
					<div>Stock</div>
					<div>Selling Price (per Unit)</div>
					<div>Actions</div>
				</div>

				<div id="products-items">
					@foreach ($product_list as $product)
						@include('common.products.index_row', ['product' => $product])
					@endforeach
				</div>

				{{ $product_list->links('vendor.pagination.default_custom') }}

			</div>
		</div>

		<!-- Recent Product Activity -->
		<div class="activities" id="recent">
			<div class="activity-header">
				<h2>Recent Product Activity</h2>
				<button class="view-all">View All</button>
			</div>
			<ul class="activity-list">
				<li>
					<i class="fas fa-box-open"></i>
					<div class="activity-details">
						<p><strong>Wireless Mouse</strong> stock updated to <strong>120 units</strong>.</p>
						<span>2 hours ago</span>
					</div>
				</li>
				<li>
					<i class="fas fa-truck-loading"></i>
					<div class="activity-details">
						<p>Received new shipment from <strong>Supplier B</strong>.</p>
						<span>Yesterday</span>
					</div>
				</li>
			</ul>
		</div>
	</section>
@endsection

@section('scripts')
	<script type="application/javascript" src="{{ asset('js/products/index.js') }}"></script>
@endsection