// Safe global currency formatter check
if (typeof window.formatter === 'undefined') {
    window.formatter = new Intl.NumberFormat('el-GR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        useGrouping: true
    });
}

let newRowIndex = 0;
let searchDebouncingTimer = null;

/**
 * Helper to dynamically get CSRF token when needed.
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

/**
 * Recalculates line totals, subtotal, tax, order discount, and grand total.
 */
function recalculateAll() {
    let subtotal = 0;
    const TAX_RATE = 0.24; // 24% VAT

    // 1. Loop through each row in the table
    document.querySelectorAll('#order_items_table tbody tr').forEach(row => {
        const qtyInput = row.querySelector('.input-qty');
        const priceInput = row.querySelector('.input-price');
        const discountInput = row.querySelector('.input-discount');
        const rowTotalDisplay = row.querySelector('.row-total-display');

        const qty = parseFloat(qtyInput?.value) || 0;

        let rawPrice = priceInput?.value || priceInput?.dataset.price || '0';
        if (typeof rawPrice === 'string') {
            rawPrice = rawPrice.replace(',', '.').replace(/[^0-9.-]/g, '');
        }
        const price = parseFloat(rawPrice) || 0;

        let rawDiscount = discountInput?.value || '0';
        if (typeof rawDiscount === 'string') {
            rawDiscount = rawDiscount.replace(',', '.').replace(/[^0-9.-]/g, '');
        }
        const discountRate = parseFloat(rawDiscount) || 0;

        // Calculate line total with percentage discount
        const lineSubtotal = qty * price;
        const lineDiscount = lineSubtotal * (discountRate / 100);
        const lineTotal = lineSubtotal - lineDiscount;

        subtotal += lineTotal;

        // Update individual line total display (<td class="row-total-display">)
        if (rowTotalDisplay) {
            rowTotalDisplay.textContent = `€${window.formatter.format(lineTotal)}`;
        }
    });

    // 2. Read global order discount amount (€)
    const discountInput = document.getElementById('discount_amount');
    let rawDiscount = discountInput?.value || '0';
    if (typeof rawDiscount === 'string') {
        rawDiscount = rawDiscount.replace(',', '.').replace(/[^0-9.-]/g, '');
    }
    const orderDiscount = parseFloat(rawDiscount) || 0;

    // 3. Compute tax and grand total
    const taxableAmount = Math.max(0, subtotal - orderDiscount);
    const tax = taxableAmount * TAX_RATE;
    const grandTotal = taxableAmount + tax;

    // 4. Update elements in DOM
    const subtotalEl = document.getElementById('subtotal_display');
    const taxEl = document.getElementById('tax_display');
    const grandTotalEl = document.getElementById('grand_total');

    if (subtotalEl) subtotalEl.textContent = `€${window.formatter.format(subtotal)}`;
    if (taxEl) taxEl.textContent = `€${window.formatter.format(tax)}`;
    if (grandTotalEl) grandTotalEl.textContent = `€${window.formatter.format(grandTotal)}`;

    // Update dataset values
    const sidebar = document.getElementById('sidebar_totals');
    if (sidebar) {
        sidebar.dataset.subtotal = subtotal.toFixed(2);
        sidebar.dataset.tax = tax.toFixed(2);
    }
}

/**
 * Adds a new item row to table from template
 */
function addProductToTable() {
    const tableBody = document.querySelector('#order_items_table tbody');
    const template = document.getElementById('new_item_row_template');
    const selectedDataInput = document.getElementById('selected_product_data');
    const searchInput = document.getElementById('product_search_input');
    const addBtn = document.getElementById('btn_add_product');

    if (!template || !tableBody) return;

    let productId, name, sku, price;

    // 1. Check if using new Autocomplete mechanism
    if (selectedDataInput && selectedDataInput.value) {
        try {
            const product = JSON.parse(selectedDataInput.value);
            productId = product.id;
            name = product.name;
            sku = product.sku || '';
            price = parseFloat(product.price) || 0;
        } catch (e) {
            console.error('Failed to parse selected product JSON:', e);
            return;
        }
    } else {
        // 2. Legacy Select Dropdown Fallback
        const productPicker = document.getElementById('product_picker');
        if (productPicker) {
            const selectedOpt = productPicker.options[productPicker.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                productId = selectedOpt.value;
                name = selectedOpt.dataset.name;
                sku = selectedOpt.dataset.sku;
                price = parseFloat(selectedOpt.dataset.price) || 0;
            }
        }
    }

    if (!productId) {
        alert('Παρακαλώ επιλέξτε ένα προϊόν.');
        return;
    }

    newRowIndex++;
    const rowKey = `new_${newRowIndex}`;

    const clone = template.content.cloneNode(true);
    const tr = clone.querySelector('tr');

    tr.querySelector('.item-name').textContent = name;
    tr.querySelector('.item-sku').textContent = sku;

    const prodHidden = tr.querySelector('.input-product-id');
    if (prodHidden) {
        prodHidden.name = `items[${rowKey}][product_id]`;
        prodHidden.value = productId;
    }

    const qtyInput = tr.querySelector('.input-qty');
    if (qtyInput) {
        qtyInput.name = `items[${rowKey}][quantity_ordered]`;
    }

    const priceInput = tr.querySelector('.input-price');
    if (priceInput) {
        priceInput.name = `items[${rowKey}][unit_price]`;
        priceInput.value = price.toFixed(2);
    }

    const discountInput = tr.querySelector('.input-discount');
    if (discountInput) {
        discountInput.name = `items[${rowKey}][discount_rate]`;
        discountInput.value = '0';
    }

    tableBody.appendChild(tr);

    // Reset inputs
    if (selectedDataInput) selectedDataInput.value = '';
    if (searchInput) searchInput.value = '';
    if (addBtn) addBtn.disabled = true;

    const productPicker = document.getElementById('product_picker');
    if (productPicker) productPicker.value = '';

    recalculateAll();
}

/**
 * Primary initialization function
 */
function initializeOrderItemsTable() {
    const tableBody = document.querySelector('#order_items_table tbody');
    const filterCat = document.getElementById('filter_category');
    const filterSubCat = document.getElementById('filter_subcategory');
    const filterBrand = document.getElementById('filter_brand');
    const btnAdd = document.getElementById('btn_add_product');
    const discountInput = document.getElementById('discount_amount');

    if (filterCat) filterCat.addEventListener('change', updateSubcategories);
    if (filterSubCat) filterSubCat.addEventListener('change', filterProducts);
    if (filterBrand) filterBrand.addEventListener('change', filterProducts);

    if (btnAdd) btnAdd.addEventListener('click', addProductToTable);
    if (discountInput) discountInput.addEventListener('input', recalculateAll);

    if (tableBody) {
        // Handles removal of row
        tableBody.addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-item')) {
                const row = e.target.closest('tr');
                if (row) {
                    row.remove();
                    recalculateAll();
                }
            }
        });

        // Recalculates dynamically when quantity, price, or discount changes
        tableBody.addEventListener('input', function (e) {
            if (
                e.target.classList.contains('input-qty') ||
                e.target.classList.contains('input-price') ||
                e.target.classList.contains('input-discount')
            ) {
                recalculateAll();
            }
        });
    }

    // Initialize Autocomplete listeners
    setupProductAutocomplete();

    recalculateAll();
}

/**
 * Filters product options in the picker dropdown (legacy dropdown fallback)
 */
function filterProducts() {
    const filterCat = document.getElementById('filter_category');
    const filterSubCat = document.getElementById('filter_subcategory');
    const filterBrand = document.getElementById('filter_brand');
    const productPicker = document.getElementById('product_picker');

    if (!productPicker) return;

    const catVal = filterCat ? filterCat.value : '';
    const subCatVal = filterSubCat ? filterSubCat.value : '';
    const brandVal = filterBrand ? filterBrand.value : '';

    filterBrand.disabled = false;

    Array.from(productPicker.options).forEach(opt => {
        if (!opt.value) return;

        const prodCat = opt.dataset.category;
        const prodBrand = opt.dataset.brand;

        let matchCat = true;
        if (subCatVal) {
            matchCat = prodCat === subCatVal;
        } else if (catVal) {
            const validCategoryIds = [catVal];
            if (filterSubCat) {
                Array.from(filterSubCat.options).forEach(subOpt => {
                    if (subOpt.dataset.parent === catVal) {
                        validCategoryIds.push(subOpt.value);
                    }
                });
            }
            matchCat = validCategoryIds.includes(prodCat);
        }

        const matchBrand = !brandVal || prodBrand === brandVal;

        opt.style.display = (matchCat && matchBrand) ? '' : 'none';
    });

    productPicker.value = '';
}

/**
 * Updates subcategories select dropdown
 */
function updateSubcategories() {
    const filterCat = document.getElementById('filter_category');
    const filterSubCat = document.getElementById('filter_subcategory');

    if (!filterCat || !filterSubCat) return;

    const selectedParentId = filterCat.value;
    filterSubCat.value = '';

    if (!selectedParentId) {
        filterSubCat.disabled = true;
        Array.from(filterSubCat.options).forEach(opt => {
            if (opt.value) opt.style.display = 'none';
        });
    } else {
        filterSubCat.disabled = false;
        let hasSubcategories = false;

        Array.from(filterSubCat.options).forEach(opt => {
            if (!opt.value) return;

            if (opt.dataset.parent === selectedParentId) {
                opt.style.display = '';
                hasSubcategories = true;
            } else {
                opt.style.display = 'none';
            }
        });

        filterSubCat.disabled = !hasSubcategories;
    }

    filterProducts();
}

/**
 * Sends a POST request into categories/{category}/filter to fetch child categories & products.
 */
async function fetchCategoryFilterData(categoryId) {
    if (!categoryId) return null;

    try {
        const response = await fetch(`/categories/${categoryId}/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                category_id: categoryId,
            })
        });

        if (!response.ok) throw new Error(`Server returned status: ${response.status}`);

        return await response.json();
    } catch (error) {
        console.error('Error fetching filtered category data:', error);
        return null;
    }
}

/**
 * Sends a POST request into categories/{category}/brands to fetch brands for a subcategory/category.
 */
async function fetchCategoryBrands(categoryId) {
    if (!categoryId) return null;

    const subCatVal = document.getElementById('filter_subcategory')?.value;
    const catVal = document.getElementById('filter_category')?.value;

    try {
        const response = await fetch(`/categories/${categoryId}/brands`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                category: subCatVal ? parseInt(subCatVal, 10) : null,
                parent_category: catVal ? parseInt(catVal, 10) : null,
            }),
        });

        if (!response.ok) throw new Error(`Server returned status: ${response.status}`);

        return await response.text();
    } catch (error) {
        console.error('Error fetching category brands:', error);
        return null;
    }
}

/**
 * Fetches filtered products by brand, category, and query term.
 */
async function fetchBrandProducts(brandId, filters = {}) {
    if (!brandId) return [];

    try {
        const response = await fetch(`/brands/${brandId}/products/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                search: filters.search || '',
                parent_category_id: filters.parent_category_id ? parseInt(filters.parent_category_id, 10) : null,
                category_id: filters.category_id ? parseInt(filters.category_id, 10) : null,
                warehouse_id: document.getElementById('warehouse_id').value,
            }),
        });

        if (!response.ok) throw new Error(`Server status: ${response.status}`);

        return await response.json();
    } catch (error) {
        console.error('Error fetching brand products:', error);
        return [];
    }
}

/**
 * Initializes autocomplete logic for product search input
 */
function setupProductAutocomplete() {
    const searchInput = document.getElementById('product_search_input');
    const resultsContainer = document.getElementById('product_autocomplete_results');
    const selectedDataInput = document.getElementById('selected_product_data');
    const addBtn = document.getElementById('btn_add_product');

    const filterWarehouse = document.getElementById('warehouse_id');
    const filterCat = document.getElementById('filter_category');
    const filterSubCat = document.getElementById('filter_subcategory');
    const filterBrand = document.getElementById('filter_brand');

    if (!searchInput || !resultsContainer) return;

    if (filterBrand) {
        filterBrand.addEventListener('change', function () {
            const hasBrand = Boolean(this.value);
            searchInput.disabled = !hasBrand;
            if (!hasBrand) {
                searchInput.value = '';
                resultsContainer.classList.add('d-none');
            }
        });
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        const brandId = filterBrand ? filterBrand.value : null;
        const categoryId = filterSubCat?.value || filterCat?.value || null;
        const warehouseId = filterWarehouse.value || null;

        if (selectedDataInput) selectedDataInput.value = '';
        if (addBtn) addBtn.disabled = true;

        if (!brandId || query.length < 1) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('d-none');
            return;
        }

        clearTimeout(searchDebouncingTimer);
        searchDebouncingTimer = setTimeout(async () => {
            const products = await fetchBrandProducts(brandId, {
                search: query,
                parent_category_id: document.getElementById('filter_category')?.value,
                category_id: categoryId,
                brand_id: brandId,
                warehouse_id: warehouseId,
            });

            renderAutocompleteResults(products, resultsContainer);
        }, 300);
    });

    resultsContainer.addEventListener('click', function (e) {
        const item = e.target.closest('.product-autocomplete-item');
        if (!item) return;

        e.preventDefault();

        const productData = {
            id: item.dataset.id,
            name: item.dataset.name,
            sku: item.dataset.sku || '',
            price: item.dataset.price || 0
        };

        if (!productData.id) return;

        const tableBody = document.querySelector('#order_items_table tbody');
        if (tableBody) {
            const existingInput = tableBody.querySelector(`input[name*="[product_id]"][value="${productData.id}"]`);
            if (existingInput) {
                const existingRow = existingInput.closest('tr');
                const qtyInput = existingRow ? existingRow.querySelector('.input-qty') : null;
                if (qtyInput) {
                    qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
                    recalculateAll();
                }

                resultsContainer.classList.add('d-none');
                resultsContainer.innerHTML = '';
                if (searchInput) searchInput.value = '';
                return;
            }
        }

        if (selectedDataInput) {
            selectedDataInput.value = JSON.stringify(productData);
        }

        addProductToTable();

        resultsContainer.classList.add('d-none');
        resultsContainer.innerHTML = '';
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });
}

/**
 * Renders HTML result items inside dropdown container
 */
function renderAutocompleteResults(products, container) {
    container.innerHTML = '';

    if (!products || products.length === 0) {
        container.innerHTML = `<div class="list-group-item text-muted small">Δεν βρέθηκαν προϊόντα</div>`;
        container.classList.remove('d-none');
        return;
    }

    container.innerHTML = products.results;
    container.classList.remove('d-none');
}

/**
 * Handles choosing a product from the suggestion list
 */
function selectAutocompleteProduct(product) {
    const searchInput = document.getElementById('product_search_input');
    const resultsContainer = document.getElementById('product_autocomplete_results');
    const selectedDataInput = document.getElementById('selected_product_data');
    const addBtn = document.getElementById('btn_add_product');

    searchInput.value = `${product.name} (${product.sku || ''})`;
    selectedDataInput.value = JSON.stringify(product);

    if (addBtn) addBtn.disabled = false;
    resultsContainer.classList.add('d-none');
}

/**
 * Calculates the current subtotal by summing all table row totals.
 */
function calculateLiveSubtotal() {
    let subtotal = 0;

    document.querySelectorAll('#order_items_table tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.input-price')?.value) || 0;
        const discountRate = parseFloat(row.querySelector('.input-discount')?.value) || 0;

        const lineSubtotal = qty * price;
        const lineTotal = lineSubtotal - (lineSubtotal * (discountRate / 100));

        subtotal += lineTotal;

        const lineTotalCell = row.querySelector('.row-total-display');
        if (lineTotalCell) {
            lineTotalCell.textContent = '€' + window.formatter.format(lineTotal);
        }
    });

    return subtotal;
}

// Export function globally
window.calculateLiveSubtotal = calculateLiveSubtotal;

// Global Exports
window.fetchBrandProducts = fetchBrandProducts;
window.setupProductAutocomplete = setupProductAutocomplete;
window.fetchCategoryBrands = fetchCategoryBrands;
window.fetchCategoryFilterData = fetchCategoryFilterData;
window.recalculateAll = recalculateAll;
window.initializeOrderItemsTable = initializeOrderItemsTable;