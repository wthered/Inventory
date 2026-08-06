document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Debounce Utility to avoid spamming the controller endpoint
    function debounce(func, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func(...args), delay);
        };
    }

    // --- 1. AJAX FETCH SEARCH ---
    const fetchProductSearchResults = async function (inputElement) {
        const row = inputElement.closest('tr');
        const dropdown = row.querySelector('.product-dropdown-results');
        const hiddenInput = row.querySelector('.product-id-hidden');
        const searchTerm = inputElement.value.trim();

        // CHANGE 1: Clear hidden input & dispatch change event if text is modified
        if (hiddenInput.value !== '') {
            hiddenInput.value = '';
            hiddenInput.dispatchEvent(new Event('change', {bubbles: true}));
        }

        if (searchTerm.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

        dropdown.innerHTML = '<div class="dropdown-no-result"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        dropdown.classList.remove('hidden');

        try {
            const response = await fetch('/products/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({q: searchTerm})
            });

            // Handle expired session token
            if (response.status === 419) {
                window.location.reload();
                return;
            }

            if (response.ok) {
                const products = await response.json();

                if (products.length === 0) {
                    dropdown.innerHTML = '<div class="dropdown-no-result">No matching products found</div>';
                    return;
                }

                // Render matching items inside floating menu
                dropdown.innerHTML = products.map(product => `
                    <div class="dropdown-item" 
                         data-id="${product.id}" 
                         data-name="${product.name}" 
                         data-sku="${product.sku || ''}">
                        <div class="item-name">${product.name}</div>
                        <div class="item-meta">
                            ${product.sku ? `<span>SKU: ${product.sku}</span>` : ''}
                            ${product.barcode ? `<span>Barcode: ${product.barcode}</span>` : ''}
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error searching products via AJAX:', error);
            dropdown.innerHTML = '<div class="dropdown-no-result">An error occurred while searching.</div>';
        }
    };

    const debouncedProductSearch = debounce(fetchProductSearchResults, 300);

    // --- 2. INPUT DELEGATION ---
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('product-search-input')) {
            debouncedProductSearch(e.target);
        }
    });

    // --- 3. CLICK SELECTION DELEGATION ---
    document.addEventListener('click', function (e) {
        const dropdownItem = e.target.closest('.dropdown-item');

        if (dropdownItem) {
            const row = dropdownItem.closest('tr');
            const searchInput = row.querySelector('.product-search-input');
            const hiddenIdInput = row.querySelector('.product-id-hidden');
            const dropdown = row.querySelector('.product-dropdown-results');

            // Populate form input and hidden ID
            const id = dropdownItem.dataset.id;
            const name = dropdownItem.dataset.name;
            const sku = dropdownItem.dataset.sku;

            hiddenIdInput.value = id;
            searchInput.value = sku ? `${name} (SKU: ${sku})` : name;

            dropdown.classList.add('hidden');

            // CHANGE 2: Dispatch change event so create_4.js picks up product_id and posts to /warehouses/zones
            hiddenIdInput.dispatchEvent(new Event('change', {bubbles: true}));

        } else if (!e.target.closest('.product-search-container')) {
            // Dismiss all open dropdowns when clicking outside
            document.querySelectorAll('.product-dropdown-results').forEach(el => el.classList.add('hidden'));
        }
    });
});