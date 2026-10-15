document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('items-tbody');
    const template = document.getElementById('row-template');
    const addItemBtn = document.getElementById('add-item-btn');

    // Financial calculations UI elements
    const calcSubtotal = document.getElementById('calc-subtotal');
    const calcTax = document.getElementById('calc-tax');
    const calcDiscount = document.getElementById('calc-discount');
    const calcGrandTotal = document.getElementById('calc-grand-total');

    const TAX_RATE = 0.24; // 24% standard tax rate

    // Ensure you have a CSRF token meta tag in your HTML head:
    // <meta name="csrf-token" content="{{ csrf_token() }}">
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Determine starting index based on existing rows (e.g. from old input after validation error)
    let rowIndex = tbody.querySelectorAll('.item-row').length;

    /**
     * Add a new row to the table using HTML template
     */
    function addRow() {
        if (!template) return;

        let templateHtml = template.innerHTML.replace(/__INDEX__/g, rowIndex);
        tbody.insertAdjacentHTML('beforeend', templateHtml);

        const newRow = tbody.querySelector(`.item-row[data-index="${rowIndex}"]`);
        attachRowEvents(newRow);

        rowIndex++;
        calculateTotals();
    }

    /**
     * Remove a row from the table
     */
    function removeRow(row) {
        row.remove();
        calculateTotals();
    }

    /**
     * Event bindings for dynamic rows
     */
    function attachRowEvents(row) {
        if (!row) return; // Guard against null targets

        const parentCategorySelect = row.querySelector('.parent-category-select');
        const categorySelect = row.querySelector('.category-select');
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');

        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const discountInput = row.querySelector('.discount-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        // Handle Parent Category -> Child Category change
        if (parentCategorySelect) {
            parentCategorySelect.addEventListener('change', function () {
                const parentId = parentCategorySelect.value;

                categorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                productSelect.innerHTML = '<option value="">Select Product</option>';

                categorySelect.disabled = true;
                brandSelect.disabled = true;
                productSelect.disabled = true;

                if (!parentId) return;

                categorySelect.disabled = true;
                brandSelect.disabled = true;
                productSelect.disabled = true;

                fetch(`/categories/${parentId}/filter`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category_id: parentId
                    }),
                }).then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                }).then(children => {
                    categorySelect.innerHTML = children.categories;
                    brandSelect.innerHTML = children.brands;
                    categorySelect.disabled = false;
                }).catch(err => console.error('Error fetching subcategories:', err));
            });
        }

        // Handle Child Category -> Brand change (dynamic POST)
        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                const categoryId = categorySelect.value;
                const parentCategoryId = parentCategorySelect ? parentCategorySelect.value : null;

                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                productSelect.innerHTML = '<option value="">Select Product</option>';
                brandSelect.disabled = true;
                productSelect.disabled = true;

                if (!categoryId || !parentCategoryId) return;

                fetch(`/categories/${categoryId}/brands`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category: categoryId,
                        parent_category: parentCategoryId
                    })
                }).then(function (res) {
                    return res.json();
                }).then(category => {
                    brandSelect.innerHTML = category.brands;
                    brandSelect.disabled = false;
                }).catch(err => {
                    console.error('Error fetching brands:', err);
                    brandSelect.disabled = false;
                });
            });
        }

        if (brandSelect) {
            brandSelect.addEventListener('change', function () {
                handleBrandChange(categorySelect, brandSelect, productSelect);
            });
        }

        if (productSelect) {
            productSelect.addEventListener('change', function () {
                handleProductChange(productSelect, priceInput);
            });
        }

        [qtyInput, priceInput, discountInput].forEach(input => {
            if (input) {
                input.addEventListener('input', function () {
                    calculateRowTotal(row);
                    calculateTotals();
                });
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                removeRow(row);
            });
        }
    }

    /**
     * Calculate individual line item total
     */
    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const unitPrice = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const discountRate = parseFloat(row.querySelector('.discount-input')?.value) || 0;

        const baseTotal = qty * unitPrice;
        const discountAmount = baseTotal * (discountRate / 100);
        const finalRowTotal = Math.max(0, baseTotal - discountAmount);

        const rowTotalSpan = row.querySelector('.row-total');
        if (rowTotalSpan) {
            rowTotalSpan.textContent = `$${finalRowTotal.toFixed(2)}`;
        }

        return {
            baseTotal: baseTotal,
            discountAmount: discountAmount,
            finalTotal: finalRowTotal
        };
    }

    /**
     * Calculate grand totals for the entire purchase order
     */
    function calculateTotals() {
        let grossSubtotal = 0;
        let totalDiscount = 0;

        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach(row => {
            const {baseTotal, discountAmount} = calculateRowTotal(row);
            grossSubtotal += baseTotal;
            totalDiscount += discountAmount;
        });

        const netSubtotal = grossSubtotal - totalDiscount;
        const taxAmount = netSubtotal * TAX_RATE;
        const grandTotal = netSubtotal + taxAmount;

        if (calcSubtotal) calcSubtotal.textContent = `$${grossSubtotal.toFixed(2)}`;
        if (calcDiscount) calcDiscount.textContent = `- $${totalDiscount.toFixed(2)}`;
        if (calcTax) calcTax.textContent = `+ $${taxAmount.toFixed(2)}`;
        if (calcGrandTotal) calcGrandTotal.textContent = `$${grandTotal.toFixed(2)}`;
    }

    /**
     * Cascading Dropdown: Brand -> Product
     */
    function handleBrandChange(categorySelect, brandSelect, productSelect) {
        const categoryId = categorySelect?.value;
        const brandId = brandSelect?.value;

        productSelect.innerHTML = '<option value="">Select Product</option>';

        if (!brandId || !categoryId) {
            productSelect.disabled = true;
            return;
        }

        productSelect.disabled = true;

        // Fetch warehouse value from parent form
        const warehouseId = document.getElementById('warehouse_id')?.value;

        fetch(`/purchases/items/product`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                category: categoryId,
                brand: brandId,
                warehouse: warehouseId ?? null
            }),
        }).then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        }).then(list => {
            productSelect.innerHTML = list.products;
            productSelect.disabled = false;
        }).catch(err => {
            console.error('Error fetching products:', err);
            productSelect.disabled = false;
        });
    }

    /**
     * Autofill Unit Cost, Batch Number, Expiry Date, and Manufacturing Date on Product Selection
     */
    function handleProductChange(productSelect, priceInput) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productId = productSelect.value;

        if (selectedOption && selectedOption.dataset.cost) {
            priceInput.value = parseFloat(selectedOption.dataset.cost).toFixed(2);
            priceInput.dispatchEvent(new Event('input'));
        }

        if (!productId) return;

        // Find the closest table row to target its line-item inputs
        const row = productSelect.closest('.item-row');
        if (!row) return;

        const batchInput = row.querySelector('input[name*="[batch_number]"]');
        const expiryInput = row.querySelector('input[name*="[expiry_date]"]');
        const mfgInput = row.querySelector('input[name*="[manufacturing_date]"]');
        const locationInput = row.querySelector('input[name*="[location_id]"]');
        const warehouseId = document.getElementById('warehouse_id')?.value;

        fetch(`/products/${productId}/inventories`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                warehouse: warehouseId ?? null,
            })
        }).then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        }).then(data => {
            console.log("Inventory Data", data.inventories);
            if (data.success || data) {
                if (batchInput) batchInput.value = data.inventories.batch_number || '';
                if (expiryInput) expiryInput.value = data.expiry_date || '';
                if (mfgInput) mfgInput.value = data.manufacturing_date || '';
                if (locationInput) locationInput.value = data.inventories.location_id || '';
            }
        }).catch(err => console.error('Error fetching inventory details:', err));
    }

    // Supplier selection change listener for auto-updating delivery date
    const supplierSelect = document.getElementById('supplier_id');
    const orderDateInput = document.getElementById('order_date');
    const expectedDateInput = document.getElementById('expected_date');

    if (supplierSelect) {
        supplierSelect.addEventListener('change', function () {
            console.log("Selected Supplier ID", this.value);
            const supplierId = this.value;
            if (!supplierId) return;

            fetch(`/suppliers/${supplierId}/delivery-delay`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_date: orderDateInput?.value || null
                })
            }).then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            }).then(data => {
                if (data.success && data.calculated_date && expectedDateInput) {
                    expectedDateInput.value = data.calculated_date;
                }
            }).catch(err => console.error('Error calculating supplier delivery date:', err));
        });
    }

    // Attach listeners to initial rows (if rendered via Blade server side / old inputs)
    tbody.querySelectorAll('.item-row').forEach(row => {
        attachRowEvents(row);
    });

    // Add row button trigger
    if (addItemBtn) {
        addItemBtn.addEventListener('click', addRow);
    }

    // Auto-add an initial empty row if none exist on page load
    if (tbody.querySelectorAll('.item-row').length === 0) {
        addRow();
    } else {
        calculateTotals();
    }
});
