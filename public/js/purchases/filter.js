document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('search');
	const statusInput = document.getElementById('status');
	const orderDate = document.getElementById('date');

	const filterForm = document.querySelector('.filter-form');
	const tableCard = document.querySelector('#purchases-table');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	if (!filterForm || !tableCard) return;

	let debounceTimer;

	// Array of your element references
	const inputs = [statusInput, searchInput, orderDate];

	inputs.forEach(input => {
		if (!input) return;

		['input', 'change'].forEach(eventType => {
			input.addEventListener(eventType, function (e) {
				// Prevent duplicate triggers (e.g., text firing both 'input' and 'change' on blur)
				if (e.type === 'input' && this !== searchInput) return;
				if (e.type === 'change' && this === searchInput) return;

				if (this === searchInput) {
					clearTimeout(debounceTimer);
					debounceTimer = setTimeout(fetchFilteredData, 350);
				} else {
					fetchFilteredData();
				}
			});
		});
	});

	function fetchFilteredData() {

		fetch('/purchases/filter', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': csrfToken
			},
			body: JSON.stringify({
				search: searchInput?.value,
				status: statusInput?.value,
				order: orderDate?.value,
			}),
		}).then(response => {
			if (!response.ok) throw new Error('Network response failed');
			return response.json();
		}).then(html => {
			tableCard.innerHTML = html['orders'];
			document.getElementById('pagination').innerHTML = html['pagination'];
		}).catch(error => {
			console.error('Filtering error:', error);
		});
	}
});
