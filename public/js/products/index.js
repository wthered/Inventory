document.addEventListener('DOMContentLoaded', () => {
	const filters = document.querySelectorAll('.filter');
	const itemsContainer = document.getElementById("products-items");

	const fetchFilteredProducts = async () => {
		const payload = {
			category: document.getElementById('parent_category').value,
			supplier: document.getElementById('filter_supplier').value,
			status: document.getElementById('filter_stock').value,
		};

		try {
			const response = await fetch('/products/filter', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
					'Accept': 'application/json'
				},
				body: JSON.stringify(payload),
			});

			const data = await response.json();

			if (data.success) {
				// Ενημέρωση του πίνακα
				itemsContainer.innerHTML = data.products;
				// Ενημέρωση του pagination (προαιρετικά)
				const paginationContainer = document.querySelector('.pagination');
				if (paginationContainer && data.pagination) {
					paginationContainer.innerHTML = data.pagination;
				}
			}
		} catch (error) {
			console.error('Filter Error:', error);
		}
	};

	// Πρόσθεσε event listener σε κάθε select
	filters.forEach(filter => {
		filter.addEventListener('change', fetchFilteredProducts);
	});
});