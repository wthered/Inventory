/**
 * Purchase Order Line Items Editor with Existing Cascading Routes (purchases/edit.js)
 * Live calculation, dynamic cascading dropdowns via POST endpoints, and security CSRF handling.
 */
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('items-tbody');
    const addBtn = document.getElementById('add-item-btn');
    const templateElement = document.getElementById('row-template');

    // Get the CSRF token from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!tbody || !addBtn || !templateElement) {
        return;
    }

    const template = templateElement.innerHTML;
    let rowCount = tbody.querySelectorAll('.item-row').length;

    // Auto-initialize rows and calculations on load
    if (rowCount === 0) {
        addNewRow();
    } else {
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

        // 1. Category change -> Filter Brands
        if (target.classList.contains('category-select')) {
            filterBrandsForCategory(row);
        }

        // 2. Brand change -> Filter Products
        if (target.classList.contains('brand-select')) {
            filterProductsForBrand(row);
        }

        // 3. Product Selected -> Autofill the cost price
        if (target.classList.contains('product-select')) {
            const selectedOption = target.options[target.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            const priceInput = row.querySelector('.price-input');

            if (priceInput) {
                priceInput.value = parseFloat(price).toFixed(2);
            }
            calculateRowTotal(row);
            recalculateAll();
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
        const newRowHtml = template.replace(/__INDEX__/g, rowCount);
        tbody.insertAdjacentHTML('beforeend', newRowHtml);
        rowCount++;
        recalculateAll();
    }

    /**
     * POST /categories/{category}/brands
     * Fetches brands that belong to the selected category.
     */
    function filterBrandsForCategory(row) {
        const categoryId = row.querySelector('.category-select').value;
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');

        // Clear products select because category has changed
        productSelect.innerHTML = '<option value="">Select Brand first</option>';
        productSelect.disabled = true;

        if (!categoryId) {
            brandSelect.innerHTML = '<option value="">Select Category first</option>';
            return;
        }

        brandSelect.innerHTML = '<option value="">Loading Brands...</option>';

        fetch(`/categories/${categoryId}/brands`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => response.json())
            .then(brands => {
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                if (brands.length === 0) {
                    brandSelect.innerHTML = '<option value="">No Brands found</option>';
                    return;
                }
                brands.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.name;
                    brandSelect.add(option);
                });
            })
            .catch(error => {
                console.error('Error fetching brands:', error);
                brandSelect.innerHTML = '<option value="">Error loading Brands</option>';
            });
    }

    /**
     * POST /brands/{brand}/products
     * Fetches products that belong to the selected brand.
     */
    function filterProductsForBrand(row) {
        const brandId = row.querySelector('.brand-select').value;
        const productSelect = row.querySelector('.product-select');

        if (!brandId) {
            productSelect.innerHTML = '<option value="">Select Brand first</option>';
            productSelect.disabled = true;
            return;
        }

        productSelect.innerHTML = '<option value="">Loading Products...</option>';
        productSelect.disabled = true;

        // Τα δεδομένα που θέλεις να στείλεις στο controller
        const dataToSend = {
            category_id: row.querySelector('.category-select').value,
            brand_id: brandId,
        };
        fetch(`/brands/${brandId}/products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(dataToSend)
        }).then(function (response) {
            // Use .text() instead of .json() because the server is returning HTML
            return response.text();
        }).then(function (htmlOptions) {
            // htmlOptions now contains your rendered string: "<option value='1'>...</option>"

            if (!htmlOptions || htmlOptions.trim() === '') {
                productSelect.innerHTML = '<option value="">No products found</option>';
                productSelect.disabled = true;
                return;
            }

            // Combine your default placeholder with the raw HTML options from the response
            productSelect.innerHTML = htmlOptions;

            productSelect.disabled = false;
        }).catch(error => {
            console.error('Error fetching products:', error);
            productSelect.innerHTML = '<option value="">Error loading Products</option>';
        });
    }

    /**
     * Calculates totals for a single item row.
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
            rowTotalSpan.innerHTML = '&euro;' + rowTotal.toFixed(2);
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

        if (subtotalEl) subtotalEl.textContent = '$' + totalSubtotal.toFixed(2);
        if (discountEl) discountEl.textContent = '- $' + totalDiscount.toFixed(2);
        if (taxEl) taxEl.textContent = '+ $' + totalTax.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = '$' + grandTotal.toFixed(2);
    }
});