document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('search-input');
	const statusSelect = document.getElementById('status-select');
	const tableBody = document.getElementById('customers-table-body');
	const paginationContainer = document.getElementById('pagination-container');
	// Retrieve token from meta tag with fallback to empty string
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	let debounceTimer;

	function fetchFilteredCustomers() {
		const query = searchInput.value;
		const status = statusSelect.value;

		fetch('/customers/filter', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				search: query,
				status: status
			})
		}).then(response => {
			if (!response.ok) throw new Error('Network error');
			return response.json();
		}).then(data => {
			// Update table body and pagination with HTML returned from controller
			tableBody.innerHTML = data.html;
			if (data.pagination) {
				paginationContainer.innerHTML = data.pagination;
			} else {
				paginationContainer.innerHTML = '';
			}
		}).catch(error => console.error('Error fetching customers:', error));
	}

	// Debounce typing to avoid firing requests on every keystroke
	searchInput.addEventListener('input', function () {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(fetchFilteredCustomers, 300);
	});

	statusSelect.addEventListener('change', fetchFilteredCustomers);
});
