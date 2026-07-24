@extends('templates.general')

@section('title', 'Product Edit')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/products/index.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/products/show.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/products/edit.css') }}"/>
@endsection

@section('content')
    <main>
        <!-- Product Edit Page Content -->
        <section class="products-page">
            <div class="header-actions">
                <h1><i class="fas fa-edit text-muted"></i> Edit Product: '{{ $product->name }}'</h1>
                <a href="{{ route('inventory.products.index') }}" class="btn back"><i class="fas fa-chevron-left"></i>
                    Back to Products</a>
            </div>

            <div class="card">
                <form action="{{ route('inventory.products.update', ['product' => $product->id]) }}" method="POST"
                      enctype="multipart/form-data">
                    @method('PUT')

                    <h2 class="form-section-title">Core Details</h2>
                    <!-- Core Identifiers Grid -->
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="sku">SKU / Item Code *</label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                            @if($errors->has('sku'))
                                @foreach($errors->get('sku') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Stock Keeping Unit (Unique)</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="slug">Product Slug *</label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" required>
                            @if($errors->has('slug'))
                                @foreach($errors->get('slug') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">URL-friendly identifier (Unique)</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="unit">Unit of Measurement *</label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required>
                            @if($errors->has('unit'))
                                @foreach($errors->get('unit') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">e.g., pcs, kg, liter, pack.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="category_id">Parent Category is {{ $parent_category->id }} *</label>
                            <select id="category_id" name="parent_category" required>
                                <option value="" disabled>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            @if(old('parent_category', $category->id) == $parent_category->id) selected @endif>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('parent_category'))
                                @foreach($errors->get('parent_category') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Selection is based on the Main Category.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="sub_category">Child Category is {{ $product->category['id'] }} *</label>
                            <select id="sub_category" name="child_category" required>
                                <option value="">Select Sub Category</option>
                                @foreach($child_categories as $child)
                                    <option value="{{ $child->id }}"
                                            @if($child->id == $product->category['id']) selected @endif>{{ $child->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('child_category'))
                                @foreach($errors->get('child_category') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Selection is based on the Main Category.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="brand_id">Brand</label>
                            <select id="brand_id" name="brand_id">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option
                                            value="{{ $brand->id }}" @selected(old('brand_id', $brand->id) == $product->brand['id'])>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('brand_id'))
                                @foreach($errors->get('brand_id') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Manufacturer or brand association.</span>
                            @endif
                        </div>


                    </div>

                    <h2 class="form-section-title">Pricing</h2>
                    <!-- Pricing Grid -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="cost_price">Cost Price ($) *</label>
                            <input type="number" id="cost_price" name="cost_price"
                                   value="{{ old('cost_price', $product->cost_price) }}" step="0.01" required>
                            @if($errors->has('cost_price'))
                                @foreach($errors->get('cost_price') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Your acquisition or manufacturing cost.</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="selling_price">Selling Price ($) *</label>
                            <input type="number" id="selling_price" name="selling_price"
                                   value="{{ old('selling_price', $product->selling_price) }}" step="0.01" required>
                            @if($errors->has('selling_price'))
                                @foreach($errors->get('selling_price') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">The regular retail price.</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="discount_price">Discount Price ($)</label>
                            <input type="number" id="discount_price" name="discount_price"
                                   value="{{ old('selling_price', $product->discount_price) }}" step="0.01">
                            @if($errors->has('discount_price'))
                                @foreach($errors->get('discount_price') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Current promotional or clearance price.</span>
                            @endif
                        </div>
                    </div>

                    <!-- Product Images Section -->
                    <div class="image-section">
                        <h2 class="form-section-title">Product Images</h2>
                        <div class="image-upload-container">
                            <div class="product-images-current-list" id="product-images">
                                @foreach($images as $image)
                                    <div class="image-item">
                                        <img src="{{ $image->image_location }}" alt="Product Image {{ $product->id }}"
                                             title="Image {{ $product->id }}">
                                        <button type="button" class="btn small delete"
                                                data-image="{{ $image->image_location }}" title="Remove Image"><i
                                                    class="fas fa-times"></i></button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="file-input-group">
                                <label for="image_upload">Upload New Image(s)</label>
                                <input type="file" id="image_upload" name="image_upload[]" accept="image/*" multiple>

                                <!-- Uploading Loading Indicator -->
                                <div id="upload-spinner" class="upload-status-indicator"
                                     style="display: none; margin-top: 8px;">
                                    <i class="fas fa-spinner fa-spin text-primary"></i>
                                    <span class="text-muted" style="margin-left: 6px; font-weight: 500;">Uploading image(s), please wait...</span>
                                </div>

                                <span class="text-muted">Max file size 5MB per image. Accepts JPEG, PNG, or GIF.</span>
                                @error('image_upload')
                                @foreach($errors->get('image_upload') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                                @enderror
                            </div>
                        </div>
                    </div>

                    <h2 class="form-section-title">Inventory Management</h2>
                    <!-- Inventory & Tracking Grid -->
                    <div class="form-grid">
                        <div class="group-box">
                            <div class="form-group checkbox-group">
                                <label for="track_inventory">Track Inventory?</label>
                                <input type="checkbox" id="track_inventory"
                                       name="track_inventory" @checked(old('is_active', $product->track_inventory))>
                            </div>
                            @if($errors->has('track_inventory'))
                                @foreach($errors->get('track_inventory') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Do you want to alert you when Stock is low?</span>
                            @endif
                        </div>

                        <div class="group-box">
                            <div class="form-group checkbox-group">
                                <label for="is_active_toggle">Product Status: Active</label>
                                <input type="checkbox" id="is_active_toggle"
                                       name="is_active" @checked(old('is_active', $product->is_active))>
                            </div>
                            @if($errors->has('is_active'))
                                @foreach($errors->get('is_active') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Do you want this product to be activated?</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="current_stock">Current Stock *</label>
                            <input type="number" id="current_stock" name="current_stock"
                                   value="{{ old('current_stock', $product->current_stock) }}" required>
                            @if($errors->has('current_stock'))
                                @foreach($errors->get('current_stock') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Current available quantity.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="reorder_point">Reorder Point</label>
                            <input type="number" id="reorder_point" name="reorder_point"
                                   value="{{ old('reorder_point', $product->reorder_point) }}">
                            @if($errors->has('reorder_point'))
                                @foreach($errors->get('reorder_point') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Quantity that triggers a reorder alert.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="min_stock_level">Min Stock Level</label>
                            <input type="number" id="min_stock_level" name="min_stock_level"
                                   value="{{ old('min_stock_level', $product->min_stock_level) }}">
                            @if($errors->has('min_stock_level'))
                                @foreach($errors->get('min_stock_level') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Minimum safety stock threshold.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="max_stock_level">Max Stock Level</label>
                            <input type="number" id="max_stock_level" name="max_stock_level"
                                   value="{{ old('max_stock_level', $product->max_stock_level) }}">
                            @if($errors->has('max_stock_level'))
                                @foreach($errors->get('max_stock_level') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Maximum preferred stock quantity.</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="supplier">Primary Supplier</label>
                            <select id="supplier" name="product_supplier">
                                @foreach($suppliers as $supplier)
                                    <option
                                            value="{{ $supplier->id }}" @selected($supplier->pivot['is_preferred'])>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('description'))
                                @foreach($errors->get('description') as $message)
                                    <span class="error-message">{{ $message }}</span><br>
                                @endforeach
                            @else
                                <span class="text-muted">Non-schema field kept for context.</span>
                            @endif
                        </div>
                    </div>

                    <h2 class="form-section-title">Description & Metadata</h2>
                    <!-- Full-Width Description -->
                    <div class="form-group">
                        <label for="description">Product Description</label>
                        <textarea id="description"
                                  name="description">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status/Meta Grid -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="barcode">Barcode / UPC</label>
                            <input type="text" id="barcode" name="barcode"
                                   value="{{ old('barcode', $product->barcode) }}">
                            @if(isset($errors) && $errors->has('barcode'))
                                <span class="error-message">{{ $errors->first('barcode') }}</span>
                            @endif
                            <span class="text-muted">Optional barcode number for scanning.</span>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="specifications">Specifications (JSON)</label>
                            <textarea id="specifications" name="specifications" rows="6">&hellip;</textarea>
                            <span class="text-muted">Store additional attributes as a JSON string.</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        @csrf
                        <a href="{{ route('inventory.products.show', ['product' => $product->id]) }}" class="btn back">Cancel</a>
                        <button type="submit" class="btn edit" id="updateButton"><i class="fas fa-save"></i> Update
                            Product
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection

@section('scripts')
    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        document.addEventListener('DOMContentLoaded', function () {
            attachDeleteListeners();
        });

        // Select all delete buttons and attach event listeners
        function attachDeleteListeners() {
            const deleteButtons = document.querySelectorAll('button.btn.small.delete');
            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const imageUrl = this.dataset.image;
                    console.log("Clicked to delete/remove", imageUrl);
                    refreshPreview(imageUrl);
                });
            });
        }

        function refreshPreview(imageUrl) {
            fetch('{{ route('inventory.products.image.destroy', ['product' => $product->id])}}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({image: imageUrl})
            }).then(function (response) {
                // Convert response to JSON
                return response.json();
            }).then(function (data) {
                if (data.success) {
                    document.getElementById("product-images").innerHTML = data.code;
                    attachDeleteListeners();
                } else {
                    alert('Failed to remove image.');
                }
            }).catch(function (err) {
                console.error('Error removing image:', err);
                alert('Error removing image.');
            });
        }

        // Upload an Image and couple it with the selected product
        // Upload an Image and couple it with the selected product
        document.getElementById('image_upload').addEventListener('change', function (event) {
            const fileInput = event.target;
            const files = fileInput.files;

            if (!files.length) {
                alert('Please select at least one image.');
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            // UI Feedback: Show spinner & disable file input / update button
            const spinner = document.getElementById('upload-spinner');
            const updateButton = document.getElementById('updateButton');

            spinner.style.display = 'block';
            fileInput.disabled = true;
            if (updateButton) updateButton.disabled = true;

            fetch('{{ route('inventory.products.image.upload', ['product' => $product->id]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            }).then(async (response) => {
                const data = await response.json();

                if (!response.ok) {
                    return Promise.reject(data);
                }

                return data;
            }).then(data => {
                if (data.success) {
                    document.getElementById('product-images').innerHTML = data.code;
                    attachDeleteListeners();
                }
            }).catch(error => {
                console.error('Upload error details:', error);

                if (error.errors) {
                    const messages = Object.values(error.errors).flat().join('\n');
                    alert('Validation Error:\n' + messages);
                } else if (error.message) {
                    alert('Error: ' + error.message);
                } else {
                    alert('Something went wrong while uploading.');
                }
            }).finally(() => {
                // UI Cleanup: Re-enable elements & hide spinner
                spinner.style.display = 'none';
                fileInput.disabled = false;
                fileInput.value = ''; // Reset input selection
                if (updateButton) updateButton.disabled = false;
            });
        });

        document.getElementById('category_id').addEventListener('change', function (event) {
            const selectedCategory = event.target.value;

            fetch(`/products/categories/${selectedCategory}/filter`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => {
                return response.json();
            }).then(data => {
                // For example, update a product list or the subcategories list
                document.getElementById('category_level').innerHTML = data['children'];
                document.getElementById('brands').innerHTML = data['brands'];
            }).catch(error => {
                console.error('Error:', error);
            });
        });

        // document.getElementById("updateButton").addEventListener('click', function (event) {
        // 	event.preventDefault();
        // });
    </script>
@endsection
