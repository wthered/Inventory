document.addEventListener('DOMContentLoaded', function () {
    const itemsContainer = document.getElementById('items-container');
    const addItemBtn = document.getElementById('add-item-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let rowIndex = 0;

    // Helper: Reset dropdown
    function resetDropdown(selectElement, placeholder = 'Επιλέξτε...') {
        selectElement.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        selectElement.disabled = true;
    }

    // Helper: Populate dropdown
    // Used by commented 1. Category -> Fetch Subcategories
    function populateDropdown(selectElement, data, placeholder = 'Επιλέξτε...') {
        selectElement.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name + (item.sku ? ` (${item.sku})` : '');
            if (item.selling_price) {
                option.dataset.price = item.selling_price;
            }
            selectElement.appendChild(option);
        });
        selectElement.disabled = false;
    }

    // AJAX Cascading Logic
    itemsContainer.addEventListener('change', function (e) {
        const row = e.target.closest('tr');
        if (!row) return;

        const warehouseSelect = row.querySelector('#warehouse_id');
        const categorySelect = row.querySelector('.category-select');
        const subcategorySelect = row.querySelector('.subcategory-select');
        const brandSelect = row.querySelector('.brand-select');
        const productSelect = row.querySelector('.product-select');
        const priceInput = row.querySelector('.price-input');

        // 1. Category -> Fetch Subcategories
        // if (e.target.classList.contains('category-select')) {
        //     const categoryId = categorySelect.value;
        //     resetDropdown(subcategorySelect);
        //     resetDropdown(brandSelect);
        //     resetDropdown(productSelect);
        //
        //     if (categoryId) {
        //         fetch(`/api/categories/${categoryId}/subcategories`, {
        //             headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}
        //         }).then(res => res.json())
        //             .then(data => populateDropdown(subcategorySelect, data, 'Επιλέξτε Υποκατηγορία...'))
        //             .catch(err => console.error('Subcategories Fetch Error:', err));
        //     }
        // }

        // 2. Subcategory -> Fetch Brands
        // if (e.target.classList.contains('subcategory-select')) {
        //     const subcategoryId = subcategorySelect.value;
        //     resetDropdown(brandSelect);
        //     resetDropdown(productSelect);
        //
        //     if (subcategoryId) {
        //         fetch(`/api/categories/${subcategoryId}/brands`, {
        //             headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}
        //         })
        //             .then(res => res.json())
        //             .then(data => populateDropdown(brandSelect, data, 'Επιλέξτε Brand...'))
        //             .catch(err => console.error('Brands Fetch Error:', err));
        //     }
        // }

        // 3. Brand -> Fetch Products
        if (e.target.classList.contains('brand-select')) {
            const subcategoryId = subcategorySelect.value;
            const brandId = brandSelect.value;
            resetDropdown(productSelect);

            // /api/products/filter?category_id=${subcategoryId}&brand_id=${brandId}
            if (subcategoryId && brandId) {
                fetch(`brands/${brandId}/products`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        brand_id: brandId,
                        // Add additional payload key-value pairs here if needed
                        category_id: subcategoryId,
                        parent_category_id: categorySelect.value,
                        warehouse_id: warehouseSelect.value,
                    }),
                }).then(res => res.json())
                    .then(data => {
                        console.log("Products Filtered Response:", data);
                    }).catch(err => {
                    console.error('Products Fetch Error:', err);
                });
            }
        }

        // 4. Product Select -> Auto-fill price
        if (e.target.classList.contains('product-select')) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.price) {
                priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
            }
            calculateTotals();
        }

        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input') || e.target.classList.contains('discount-input')) {
            calculateTotals();
        }
    });

    // Dynamic Calculation
    function calculateTotals() {
        let subtotal = 0;
        let totalDiscount = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discountRate = parseFloat(row.querySelector('.discount-input').value) || 0;

            const lineSubtotal = qty * price;
            const lineDiscount = lineSubtotal * (discountRate / 100);

            subtotal += lineSubtotal;
            totalDiscount += lineDiscount;
        });

        const netTotal = subtotal - totalDiscount;
        const tax = netTotal * 0.24; // 24% VAT
        const grandTotal = netTotal + tax;

        document.getElementById('summary-subtotal').textContent = `${subtotal.toFixed(2)} €`;
        document.getElementById('summary-discount').textContent = `${totalDiscount.toFixed(2)} €`;
        document.getElementById('summary-tax').textContent = `${tax.toFixed(2)} €`;
        document.getElementById('summary-grand-total').textContent = `${grandTotal.toFixed(2)} €`;
    }

    // Add Row
    addItemBtn.addEventListener('click', function () {
        rowIndex++;
        const firstRow = itemsContainer.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.dataset.index = rowIndex;
        newRow.querySelectorAll('select, input').forEach(el => {
            if (el.name) {
                el.name = el.name.replace(/\[\d+\]/, `[${rowIndex}]`);
            }
            if (el.tagName === 'SELECT') {
                if (el.classList.contains('category-select')) {
                    el.selectedIndex = 0;
                } else {
                    resetDropdown(el);
                }
            } else if (el.classList.contains('qty-input')) {
                el.value = 1;
            } else if (el.classList.contains('discount-input')) {
                el.value = 0;
            } else {
                el.value = '';
            }
        });

        itemsContainer.appendChild(newRow);
    });

    // Remove Row
    itemsContainer.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item-btn')) {
            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            }
        }
    });
});