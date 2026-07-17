document.addEventListener('DOMContentLoaded', function () {

	// Extract CSRF Token from meta tag
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	// --- 1. CORE SEARCH FUNCTION ---
	const handleProductSearch = async function (inputElement) {
		const itemId = inputElement.dataset.itemId;
		const searchTerm = inputElement.value.trim();

		const categorySelect = document.querySelector(`.category-select[data-item-id="${itemId}"]`);
		const brandSelect = document.querySelector(`.brand-select[data-item-id="${itemId}"]`);
		const productSelect = document.querySelector(`.product-ajax-select[data-item-id="${itemId}"]`);

		const categoryId = categorySelect ? categorySelect.value : null;
		const brandId = brandSelect ? brandSelect.value : null;

		if (searchTerm.length < 2) {
			if (productSelect) productSelect.innerHTML = '<option value="">Πληκτρολογήστε για αναζήτηση...</option>';
			return;
		}

		try {
			const response = await fetch('/products/search', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			body: JSON.stringify({
			q: searchTerm,
			category_id: categoryId ? parseInt(categoryId) : null,
			brand_id: brandId ? parseInt(brandId) : null
		})
		});

			// Έλεγχος για ληγμένο CSRF token (Session Expired) κατά την αναζήτηση
			if (response.status === 419) {
				window.location.reload();
				return;
			}

			if (response.ok && productSelect) {
				const products = await response.json();
				productSelect.innerHTML = '';

				if (products.length === 0) {
					productSelect.innerHTML = '<option value="">Δεν βρέθηκαν προϊόντα</option>';
					return;
				}

				products.forEach(product => {
					const opt = document.createElement('option');
					opt.value = product.id;
					opt.textContent = `${product.name} (SKU: ${product.sku})`;
					productSelect.appendChild(opt);
				});
			}
		} catch (error) {
			console.error("Error searching products:", error);
		}
	};

	// --- 2. DEBOUNCE UTILITY ---
	function debounce(func, delay) {
		let timer;
		return function (...args) {
			clearTimeout(timer);
			timer = setTimeout(() => {
				func(...args);
			}, delay);
		};
	}

	const debouncedSearch = debounce(handleProductSearch, 300);

	// --- 3. EVENT LISTENERS (Event Delegation) ---

	document.addEventListener('change', async function (e) {

		// 1. Όταν αλλάζει η Κατηγορία (Category Select)
		if (e.target && e.target.classList.contains('category-select')) {
			const select = e.target;
			const itemId = select.dataset.itemId;
			const categoryId = select.value;

			const brandSelect = document.querySelector(`.brand-select[data-item-id="${itemId}"]`);
			const productSelect = document.querySelector(`.product-ajax-select[data-item-id="${itemId}"]`);

			if (brandSelect) brandSelect.innerHTML = '<option value="">Επιλέξτε Μάρκα...</option>';
			if (brandSelect) brandSelect.disabled = !categoryId;
			if (productSelect) productSelect.innerHTML = '<option value="">Πληκτρολογήστε για αναζήτηση...</option>';

			if (categoryId && brandSelect) {
				try {
					const response = await fetch(`/categories/${categoryId}/brands`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'Accept': 'application/json'
					},
					body: JSON.stringify({ category_id: parseInt(categoryId) })
				});

					if (response.status === 419) {
						window.location.reload();
						return;
					}

					if (response.ok) {
						// Εφόσον επιστρέφει έτοιμο HTML string:
						brandSelect.innerHTML = await response.text();
					}
				} catch (error) {
					console.error("Error fetching brands:", error);
				}
			}
		}

		// 2. ΝΕΟ: Όταν αλλάζει το Brand (Brand Select)
		if (e.target && e.target.classList.contains('brand-select')) {
			const select = e.target;
			const itemId = select.dataset.itemId;
			const brandId = select.value;

			const categorySelect = document.querySelector(`.category-select[data-item-id="${itemId}"]`);
			const productSelect = document.querySelector(`.product-ajax-select[data-item-id="${itemId}"]`);

			// Αν ο χρήστης δεν έχει επιλεγμένο το Brand, επαναφέρουμε το productSelect στην αρχική κατάσταση
			if (!brandId) {
				if (productSelect) productSelect.innerHTML = '<option value="">Πληκτρολογήστε για αναζήτηση...</option>';
				return;
			}

			if (productSelect) {
				productSelect.innerHTML = '<option value="">Φόρτωση προϊόντων...</option>';
			}

			try {
				// Παίρνουμε και το categoryId αν χρειάζεται να το στείλουμε στον controller για έξτρα φιλτράρισμα
				const categoryId = categorySelect ? categorySelect.value : null;

				const response = await fetch(`/brands/${brandId}/products`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'Accept': 'application/json'
					},
					body: JSON.stringify({
						brand_id: parseInt(brandId),
						category_id: categoryId ? parseInt(categoryId) : null
					})
				});

				if (response.status === 419) {
					window.location.reload();
					return;
				}

				if (response.ok && productSelect) {
					// Εφόσον ο Controller επιστρέφει έτοιμα HTML <option> tags, διαβάζουμε ως text
					productSelect.innerHTML = await response.text();
				}
			} catch (error) {
				console.error("Error fetching brand products:", error);
			}
		}
	});

	// 2. Για το Live Search (Input Event)
	document.addEventListener('input', function (e) {
		if (e.target && e.target.classList.contains('product-search-input')) {
			debouncedSearch(e.target);
		}
	});

});
