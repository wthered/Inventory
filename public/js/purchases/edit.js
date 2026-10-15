/**
 * Purchase Order Line Items Editor (purchases/edit.js)
 * Live calculation, dynamic 3-tier cascading dropdowns matched to routes/management.php,
 * initial state restoration for editing existing items, and CSRF token handling.
 */
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('items-tbody');
    const addBtn = document.getElementById('add-item-btn');
    const templateElement = document.getElementById('row-template');

    // Get the CSRF token from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Reusable currency formatter
    const currencyFormatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    if (!tbody || !addBtn || !templateElement) {
        return;
    }

    const template = templateElement.innerHTML;
    let rowCount = tbody.querySelectorAll('.item-row').length;

    // Auto-initialize existing rows or add default empty row
    if (rowCount === 0) {
        addNewRow();
    } else {
        // Restore dynamic cascading dropdown values for existing database items
        tbody.querySelectorAll('.item-row').forEach(row => {
            restoreRowCascades(row);
        });
        recalculateAll();
    }

    // Event Listener: Add Row
    addBtn.addEventListener('click', function () {
        addNewRow();
    });

    // Event Delegation: Text/Number Input changes (Qty, Price, Discount)
    tbody.addEventListener('input', function (e) {
        const target = e.target;
        if (target.classList.contains('qty-input') ||
            target.classList.contains('price-input') ||
            target.classList.contains('discount-input')) {
            calculateRowTotal(target.closest('.item-row'));
            recalculateAll();
        }
    });

    // Event Delegation: Cascading Select and Product Selection Changes
    tbody.addEventListener('change', function (e) {
        const target = e.target;
        const row = target.closest('.item-row');

        if (!row) return;

        // 1. Parent Category change -> Filter Subcategories
        if (target.classList.contains('parent-category-select')) {
            filterSubcategoriesForParent(row);
        }

        // 2. Subcategory change -> Filter Brands
        if (target.classList.contains('category-select')) {
            filterBrandsForCategory(row);
        }

        // 3. Brand change -> Filter Products
        if (target.classList.contains('brand-select')) {
            filterProductsForBrand(row);
        }

        // 4. Product Selected -> Fetch details via AJAX
        if (target.classList.contains('product-select')) {
            const productId = target.value;

            if (productId) {
                fetchProductDetails(row, productId);
            } else {
                // Reset totals if the user deselects
                calculateRowTotal(row);
                recalculateAll();
            }
        }
    });

    // Event Delegation: Delete Line Item Row
    tbody.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.remove-row-btn');
        if (deleteBtn) {
            const row = deleteBtn.closest('.item-row');
            if (tbody.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalculateAll();
            } else {
                alert('At least one line item is required.');
            }
        }
    });

    /**
     * Appends a newly formatted row to the table.
     */
    function addNewRow() {
        // Accepts both __INDEX__ and _INDEX__ placeholders
        const newRowHtml = template.replace(/_?_INDEX__/g, rowCount);
        tbody.insertAdjacentHTML('beforeend', newRowHtml);
        rowCount++;
        recalculateAll();
    }

    /**
     * Restores existing cascade dropdown chains when loading order line items on Edit view.
     */
    function restoreRowCascades(row) {
        const parentSelect = row.querySelector('.parent-category-select');
        const categorySelect = row.querySelector('.category-select');
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');

        const selectedCatId = categorySelect?.getAttribute('data-selected-id');
        const selectedBrandId = brandSelect?.getAttribute('data-selected-id');
        const selectedProductId = productSelect?.getAttribute('data-selected-id');

        if (parentSelect && parentSelect.value) {
            filterSubcategoriesForParent(row, selectedCatId, function () {
                if (categorySelect && categorySelect.value) {
                    filterBrandsForCategory(row, selectedBrandId, function () {
                        if (brandSelect && brandSelect.value) {
                            filterProductsForBrand(row, selectedProductId);
                        }
                    });
                }
            });
        }
    }

    /**
     * POST /categories/{category}/filter
     * Fetches subcategories (child categories) for parent category.
     */
    function filterSubcategoriesForParent(row, selectedSubcatId = null, callback = null) {
        const parentId = row.querySelector('.parent-category-select').value;
        const categorySelect = row.querySelector('.category-select');
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');

        // Clear downstream selectors
        categorySelect.innerHTML = '<option value="">Select Subcategory</option>';
        brandSelect.innerHTML = '<option value="">Select Category first</option>';
        brandSelect.disabled = true;
        productSelect.innerHTML = '<option value="">Select Brand first</option>';
        productSelect.disabled = true;

        if (!parentId) {
            categorySelect.disabled = true;
            return;
        }

        categorySelect.innerHTML = '<option value="">Loading Subcategories...</option>';
        categorySelect.disabled = true;

        fetch(`/categories/${parentId}/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                product: row.querySelector('.product-select').dataset.product,
                warehouse: document.getElementById('warehouse_id').value,
            })
        }).then(function (response) {
            return response.json();
        }).then(subcategories => {
            categorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            if (!subcategories || subcategories.length === 0) {
                categorySelect.innerHTML = '<option value="">No Subcategories found</option>';
                return;
            }

            // Console Log The children Categories
            // console.log(subcategories);

            categorySelect.innerHTML = subcategories.categories;
            categorySelect.disabled = false;
            if (typeof callback === 'function') callback();
        }).catch(error => {
            console.error('Error fetching subcategories:', error);
            categorySelect.innerHTML = '<option value="">Error loading Subcategories</option>';
        });
    }

    /**
     * POST /categories/{category}/brands
     * Fetches brands that belong to the selected subcategory.
     */
    function filterBrandsForCategory(row, selectedBrandId = null, callback = null) {
        const categoryId = row.querySelector('.category-select').value;
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');

        productSelect.innerHTML = '<option value="">Select Brand first</option>';
        productSelect.disabled = true;

        if (!categoryId) {
            brandSelect.innerHTML = '<option value="">Select Subcategory first</option>';
            brandSelect.disabled = true;
            return;
        }

        brandSelect.innerHTML = '<option value="">Loading Brands...</option>';
        brandSelect.disabled = true;

        const parentCategorySelect = row.querySelector('.parent-category-select');
        const parentCategoryId = parentCategorySelect ? parentCategorySelect.value : null;

        fetch(`/categories/${categoryId}/brands`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                parent_category: parentCategoryId,
                product: row.querySelector('.product-select').dataset.product,
                warehouse: document.getElementById('warehouse_id').value,
            })
        }).then(function (response) {
            return response.json();
        }).then(brands => {
            if (!brands || brands.length === 0) {
                brandSelect.innerHTML = '<option value="">No Brands found</option>';
                return;
            }

            /*************************************
             ** Show in Console the brands whereHas products where inventories.warehouse_id = 5
             ** or whatever is the selected Warehouse
             ************************************/
            // console.log("Brand Options:", brands);

            brandSelect.disabled = false;
            brandSelect.innerHTML = brands.options;
            if (typeof callback === 'function') callback();
        }).catch(error => {
            console.error('Error fetching brands:', error);
            brandSelect.innerHTML = '<option value="">Error loading Brands</option>';
        });
    }

    /**
     * POST /brands/{brand}/products
     * Fetches products that belong to the selected brand.
     */
    function filterProductsForBrand(row, selectedProductId = null) {
        const brandId = row.querySelector('.brand-select').value;
        const categoryId = row.querySelector('.category-select').value;
        const productSelect = row.querySelector('.product-select');

        if (!brandId) {
            productSelect.innerHTML = '<option value="">Select Brand first</option>';
            productSelect.disabled = true;
            return;
        }

        productSelect.innerHTML = '<option value="">Loading Products...</option>';
        productSelect.disabled = true;

        fetch(`/brands/${brandId}/products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                category_id: categoryId,
                product: row.querySelector('.product-select').dataset.product,
                warehouse: document.getElementById('warehouse_id').value,
            })
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (!data || data.products.trim() === '') {
                productSelect.innerHTML = '<option value="">No products found</option>';
                productSelect.disabled = true;
                return;
            }

            productSelect.innerHTML = data.products;
            productSelect.disabled = false;

            row.querySelector('input[name*="[manufacturing_date]"]').value = data.inventory.manufacturing_date;
            row.querySelector('input[name*="[expiry_date]"]').value = data.inventory.expiry_date;
        }).catch(error => {
            console.error('Error fetching products:', error);
            productSelect.innerHTML = '<option value="">Error loading Products</option>';
        });
    }

    /**
     * Calculates totals for a single item row and applies formatted currency.
     */
    function calculateRowTotal(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const discountInput = row.querySelector('.discount-input');

        const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
        const discountPercent = parseFloat(discountInput ? discountInput.value : 0) || 0;

        const baseTotal = qty * price;
        const discountAmount = baseTotal * (discountPercent / 100);
        const rowTotal = baseTotal - discountAmount;

        const rowTotalSpan = row.querySelector('.row-total');
        if (rowTotalSpan) {
            rowTotalSpan.textContent = currencyFormatter.format(rowTotal);
        }

        return {baseTotal, discountAmount, rowTotal};
    }

    /**
     * Aggregates all row totals and updates the main Financial Summary Card.
     */
    function recalculateAll() {
        let totalSubtotal = 0;
        let totalDiscount = 0;

        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach(row => {
            const totals = calculateRowTotal(row);
            totalSubtotal += totals.baseTotal;
            totalDiscount += totals.discountAmount;
        });

        const taxRate = 0.24;
        const taxableAmount = totalSubtotal - totalDiscount;
        const totalTax = taxableAmount * taxRate;
        const grandTotal = taxableAmount + totalTax;

        const subtotalEl = document.getElementById('calc-subtotal');
        const discountEl = document.getElementById('calc-discount');
        const taxEl = document.getElementById('calc-tax');
        const grandTotalEl = document.getElementById('calc-grand-total');

        if (subtotalEl) subtotalEl.textContent = currencyFormatter.format(totalSubtotal);
        if (discountEl) discountEl.textContent = '- ' + currencyFormatter.format(totalDiscount);
        if (taxEl) taxEl.textContent = '+ ' + currencyFormatter.format(totalTax);
        if (grandTotalEl) grandTotalEl.textContent = currencyFormatter.format(grandTotal);
    }

    /**
     * POST /products/{product}/information
     * Fetches batch, expiry date, manufacturing date, and price for selected product.
     */
    function fetchProductDetails(row, productId) {
        if (!productId) return;

        // Target input fields in the current row
        const batchInput = row.querySelector('input[name*="[batch_number]"]');
        const mfgDateInput = row.querySelector('input[name*="[manufacturing_date]"]');
        const expDateInput = row.querySelector('input[name*="[expiry_date]"]');
        const priceInput = row.querySelector('.price-input');

        fetch(`/products/${productId}/details`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                warehouse: document.getElementById('warehouse_id').value,
            }),
        }).then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        }).then(data => {
            // Populate batch details if returned from backend
            if (batchInput && data.batch_number) batchInput.value = data.batch_number;
            if (mfgDateInput && data.manufacturing_date) mfgDateInput.value = data.manufacturing_date;
            if (expDateInput && data.expiry_date) expDateInput.value = data.expiry_date;

            // Populate unit cost price if returned
            if (priceInput && data.unit_price) {
                priceInput.value = parseFloat(data.unit_price).toFixed(2);
            }

            row.querySelector('input[name*="[batch_number]"]').value = data.details.batch_number;
            row.querySelector('input[name*="[manufacturing_date]"]').value = data.details.manufacturing_date.substring(0, 10);
            row.querySelector('input[name*="[expiry_date]"]').value = data.details.expiry_date.substring(0, 10);

            // Recalculate row & summary totals
            calculateRowTotal(row);
            recalculateAll();
        }).catch(error => {
            console.error('Error fetching product details:', error);
        });
    }
});