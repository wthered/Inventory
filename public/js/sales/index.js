document.addEventListener('DOMContentLoaded', function () {
	const filterForm = document.querySelector('.filter-form');
	if (!filterForm) return;

	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

	let searchDebounceTimer = null;
	const filterInputs = filterForm.querySelectorAll('.filter-group input, .filter-group select');

	// Function to collect all input/select values and send POST request
	async function sendFilterRequest() {
		const filterParams = {
			search: document.getElementById('search')?.value || '',
			status: document.getElementById('status')?.value || '',
			payment_status: document.getElementById('payment_status')?.value || '',
			date: document.getElementById('date')?.value || ''
		};

		try {
			const response = await fetch('/sales/index/filter', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json'
				},
				body: JSON.stringify(filterParams)
			});

			if (!response.ok) throw new Error(`Server error: ${response.status}`);

			const data = await response.json();

			const saleList = document.getElementById('sale_list');
			if (saleList) {
				console.log(data.rows);
				saleList.innerHTML = data.rows;
			}

			const paginationWrapper = document.getElementById('pagination-wrapper');
			if (paginationWrapper) paginationWrapper.innerHTML = data.pagination;

		} catch (error) {
			console.error('Filtering request failed:', error);
		}
	}

	// Attach event listeners
	filterInputs.forEach(input => {
		// Immediate submit on change for select and date fields
		if (input.tagName === 'SELECT' || input.type === 'date') {
			input.addEventListener('change', sendFilterRequest);
		} else {
			// Debounced submit on keyup for text and search inputs
			input.addEventListener('keyup', function () {
				clearTimeout(searchDebounceTimer);
				searchDebounceTimer = setTimeout(sendFilterRequest, 300);
			});
		}
	});
});
