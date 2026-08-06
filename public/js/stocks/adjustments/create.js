document.addEventListener('DOMContentLoaded', () => {
    const warehouseSelect = document.getElementById('warehouse_id');
    const rowsContainer = document.getElementById('adjustment-rows');
    const emptyRow = document.getElementById('empty-row');
    const template = document.getElementById('adjustment-row-template')?.innerHTML;
    const addBtn = document.getElementById('add-product-btn');
    const triggerFirstBtn = document.getElementById('trigger-first-add');

    let itemIndex = 0;

    // --- 1. ROW MANAGEMENT ---
    function checkEmptyState() {
        const hasRows = rowsContainer.querySelectorAll('.adjustment-item-row').length > 0;
        if (emptyRow) {
            emptyRow.style.display = hasRows ? 'none' : 'table-row';
        }
    }

    async function addRow() {
        if (!template) return;

        const compiledTemplate = template.replace(/INDEX/g, itemIndex++);
        rowsContainer.insertAdjacentHTML('beforeend', compiledTemplate);
        checkEmptyState();
    }

    if (addBtn) addBtn.addEventListener('click', addRow);
    if (triggerFirstBtn) triggerFirstBtn.addEventListener('click', addRow);

    rowsContainer?.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.remove-row-btn');
        if (deleteBtn) {
            const row = deleteBtn.closest('tr');
            row.remove();
            checkEmptyState();
        }
    });

    // --- 2. PRODUCT SELECTION EVENT LISTENER ---
    // Fires when hiddenInput.dispatchEvent(new Event('change', {bubbles: true})) runs
    document.addEventListener('change', async function (e) {
        if (!e.target.classList.contains('product-id-hidden')) return;

        const row = e.target.closest('.adjustment-item-row') || e.target.closest('tr');
        if (!row) return;

        const warehouseId = warehouseSelect?.value;
        const zoneSelect = row.querySelector('.loc-zone');
        const aisleSelect = row.querySelector('.loc-aisle');
        const rackSelect = row.querySelector('.loc-rack');
        const shelfSelect = row.querySelector('.loc-shelf');
        const binSelect = row.querySelector('.loc-bin');
        const rowGroup = row.querySelector('.location-select-group') || row;
        const productId = e.target.value;

        // Reset downstream selects when product changes
        resetSelects([zoneSelect, aisleSelect, rackSelect, shelfSelect, binSelect]);
        clearLocationError(rowGroup);

        if (!warehouseId) {
            showLocationError(rowGroup, '<div class="error-item">Please select a warehouse first.</div>');
            return;
        }

        if (productId && zoneSelect) {
            await fetchCascadeOptions('/warehouses/zones', {
                warehouse: parseInt(warehouseId),
                product: parseInt(productId)
            }, zoneSelect, 'zone');
        }
    });

    // --- 3. CASCADING LOCATION DROPDOWNS CONTROLLER ---
    document.addEventListener('change', async function (e) {
        const target = e.target;
        if (!target.classList.contains('loc-select')) return;

        const rowGroup = target.closest('.location-select-group');
        if (!rowGroup) return;

        const row = target.closest('.adjustment-item-row') || target.closest('tr');
        const warehouseId = warehouseSelect?.value;
        const productId = row?.querySelector('.product-id-hidden')?.value;

        if (!warehouseId) {
            showLocationError(rowGroup, '<div class="error-item">Please select a warehouse first.</div>');
            return;
        }

        const zoneSelect = rowGroup.querySelector('.loc-zone');
        const aisleSelect = rowGroup.querySelector('.loc-aisle');
        const rackSelect = rowGroup.querySelector('.loc-rack');
        const shelfSelect = rowGroup.querySelector('.loc-shelf');
        const binSelect = rowGroup.querySelector('.loc-bin');
        const hiddenInput = rowGroup.querySelector('.location-id-hidden');

        clearLocationError(rowGroup);
        if (hiddenInput) hiddenInput.value = '';

        // 3.1. ZONE CHANGED -> Fetch Aisles
        if (target.classList.contains('loc-zone')) {
            resetSelects([aisleSelect, rackSelect, shelfSelect, binSelect]);
            if (zoneSelect.value) {
                await fetchCascadeOptions('/warehouses/aisles', {
                    warehouse: parseInt(warehouseId),
                    product: productId ? parseInt(productId) : null,
                    zone: zoneSelect.value,
                    type: 'aisle'
                }, aisleSelect, 'aisle');
            }
        }

        // 3.2. AISLE CHANGED -> Fetch Racks
        else if (target.classList.contains('loc-aisle')) {
            resetSelects([rackSelect, shelfSelect, binSelect]);
            if (aisleSelect.value) {
                await fetchCascadeOptions('/warehouses/racks', {
                    warehouse: parseInt(warehouseId),
                    product: productId ? parseInt(productId) : null,
                    zone: zoneSelect.value,
                    aisle: aisleSelect.value,
                    type: 'rack'
                }, rackSelect, 'rack');
            }
        }

        // 3.3. RACK CHANGED -> Fetch Shelves
        else if (target.classList.contains('loc-rack')) {
            resetSelects([shelfSelect, binSelect]);
            if (rackSelect.value) {
                await fetchCascadeOptions('/warehouses/shelves', {
                    warehouse: parseInt(warehouseId),
                    product: productId ? parseInt(productId) : null,
                    zone: zoneSelect.value,
                    aisle: aisleSelect.value,
                    rack: rackSelect.value,
                    type: 'shelf'
                }, shelfSelect, 'shelf');
            }
        }

        // 3.4. SHELF CHANGED -> Fetch Bins
        else if (target.classList.contains('loc-shelf')) {
            resetSelects([binSelect]);
            if (shelfSelect.value) {
                await fetchCascadeOptions('/warehouses/bins', {
                    warehouse: parseInt(warehouseId),
                    product: productId ? parseInt(productId) : null,
                    zone: parseInt(zoneSelect.value),
                    aisle: parseInt(aisleSelect.value),
                    rack: parseInt(rackSelect.value),
                    shelf: parseInt(shelfSelect.value),
                    type: 'bin'
                }, binSelect, 'bin');
            }
        }

        // 3.5. RESOLVE FINAL LOCATION
        if (zoneSelect.value && aisleSelect.value && rackSelect.value && shelfSelect.value && binSelect.value) {
            await resolveFinalLocation(rowGroup, {
                warehouse: parseInt(warehouseId),
                product: productId ? parseInt(productId) : null,
                zone: zoneSelect.value,
                aisle: aisleSelect.value,
                rack: rackSelect.value,
                shelf: shelfSelect.value,
                bin: binSelect.value,
            });
        }
    });

    /**
     * Fetch child dropdown options from backend
     */
    async function fetchCascadeOptions(endpoint, payload, targetSelect, placeholder) {
        targetSelect.disabled = true;
        targetSelect.innerHTML = `<option value="" disabled selected>Loading ${placeholder}s...</option>`;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });

            if (response.status === 419) {
                window.location.reload();
                return;
            }

            const data = await response.json();

            if (response.ok) {
                console.log("Placeholder", placeholder);
                console.log("Data", data[placeholder]);
                targetSelect.innerHTML = data[placeholder];
                // targetSelect.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                //
                // const options = Array.isArray(data) ? data : (data.data || []);
                // if (options.length === 0) {
                //     targetSelect.innerHTML = `<option value="" disabled selected>No active ${placeholder}s</option>`;
                //     return;
                // }
                //
                // options.forEach(item => {
                //     const val = typeof item === 'object' ? (item.id || item.value) : item;
                //     const label = typeof item === 'object' ? (item.name || item.label || item.id) : `${placeholder} ${item}`;
                //     targetSelect.appendChild(new Option(label, val));
                // });

                targetSelect.disabled = false;
            } else {
                handleFetchError(targetSelect, data, placeholder);
            }
        } catch (error) {
            console.error(`Error fetching ${placeholder}s:`, error);
            targetSelect.innerHTML = `<option value="" disabled selected>Error loading ${placeholder}s</option>`;
        }
    }

    async function resolveFinalLocation(rowGroup, payload) {
        const hiddenInput = rowGroup.querySelector('.location-id-hidden');

        try {
            const response = await fetch('/warehouses/locations/resolve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                clearLocationError(rowGroup);
                const locationId = typeof data === 'object' ? data.id : data;
                if (hiddenInput) hiddenInput.value = locationId || '';
            } else {
                let errorText = 'Invalid or inactive location.';
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat();
                    errorText = errorList.map(err => `<div class="error-item">${err}</div>`).join('');
                } else if (data.message) {
                    errorText = `<div class="error-item">${data.message}</div>`;
                }
                showLocationError(rowGroup, errorText);
            }
        } catch (err) {
            console.error('Failed to resolve location:', err);
            showLocationError(rowGroup, '<div class="error-item">Server error while resolving location.</div>');
        }
    }

    function resetSelects(selectElements) {
        selectElements.forEach(select => {
            if (!select) return;
            const placeholder = select.className.match(/loc-(\w+)/)?.[1] || 'option';
            const capitalized = placeholder.charAt(0).toUpperCase() + placeholder.slice(1);
            select.innerHTML = `<option value="" disabled selected>${capitalized}</option>`;
            select.value = '';
            select.disabled = true;
        });
    }

    function handleFetchError(targetSelect, data, placeholder) {
        let msg = `No active ${placeholder}s`;
        if (data.errors) {
            msg = Object.values(data.errors).flat()[0];
        } else if (data.message) {
            msg = data.message;
        }
        targetSelect.innerHTML = `<option value="" disabled selected>${msg}</option>`;
    }

    // --- 4. WAREHOUSE SELECTION CHANGE ---
    warehouseSelect?.addEventListener('change', async function () {
        const warehouseId = this.value;
        const allRows = document.querySelectorAll('.adjustment-item-row');

        for (const row of allRows) {
            const zoneSelect = row.querySelector('.loc-zone');
            const aisleSelect = row.querySelector('.loc-aisle');
            const rackSelect = row.querySelector('.loc-rack');
            const shelfSelect = row.querySelector('.loc-shelf');
            const binSelect = row.querySelector('.loc-bin');
            const rowGroup = row.querySelector('.location-select-group') || row;

            resetSelects([zoneSelect, aisleSelect, rackSelect, shelfSelect, binSelect]);
            clearLocationError(rowGroup);

            const productId = row.querySelector('.product-id-hidden')?.value;

            if (warehouseId && zoneSelect && productId) {
                await fetchCascadeOptions('/warehouses/zones', {
                    warehouse: parseInt(warehouseId),
                    product: parseInt(productId)
                }, zoneSelect, 'Zone');
            }
        }
    });
});

/**
 * Collects all currently selected products from the form table.
 */
window.getSelectedProducts = function () {
    const selectedProducts = [];
    const rows = document.querySelectorAll('#adjustment-rows .adjustment-item-row');

    rows.forEach(row => {
        const productId = row.querySelector('.product-id-hidden')?.value;

        if (productId) {
            selectedProducts.push({
                rowId: row.dataset.itemId || null,
                productId: productId,
                locationId: row.querySelector('.location-id-hidden')?.value || null,
                name: row.querySelector('.product-search-input')?.value || '',
                type: row.querySelector('.type-select')?.value || '',
                quantity: parseFloat(row.querySelector('.qty-input')?.value) || 0,
                reason: row.querySelector('.reason-select')?.value || ''
            });
        }
    });

    return selectedProducts;
};

/**
 * Displays error messages under the location dropdowns.
 */
function showLocationError(rowGroup, htmlMessage) {
    const errorEl = rowGroup.querySelector('.location-error-msg');
    if (errorEl) {
        errorEl.innerHTML = htmlMessage;
        errorEl.style.display = 'block';
    }
    rowGroup.querySelectorAll('.loc-select').forEach(select => select.classList.add('is-invalid'));
}

/**
 * Clears error messages and invalid states from the location dropdowns.
 */
function clearLocationError(rowGroup) {
    const errorEl = rowGroup.querySelector('.location-error-msg');
    if (errorEl) {
        errorEl.innerHTML = '';
        errorEl.style.display = 'none';
    }
    rowGroup.querySelectorAll('.loc-select').forEach(select => select.classList.remove('is-invalid'));
}