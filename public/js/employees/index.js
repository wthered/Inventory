document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('search-input');
	const departmentSelect = document.getElementById('department-select');
	const tableBody = document.getElementById('employees-table-body');
	const paginationContainer = document.getElementById('pagination-container');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	let debounceTimer;

	function fetchFilteredEmployees(pageUrl = '/employees/filter') {
		const query = searchInput ? searchInput.value : '';
		const departmentId = departmentSelect ? departmentSelect.value : '';

		fetch(pageUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				search: query,
				department_id: departmentId
			})
		})
			.then(response => {
				if (!response.ok) throw new Error('Network response failed');
				return response.json();
			})
			.then(data => {
				if (tableBody && data.html !== undefined) {
					tableBody.innerHTML = data.html;
				}
				if (paginationContainer && data.pagination !== undefined) {
					paginationContainer.innerHTML = data.pagination;
				}
			})
			.catch(error => console.error('Error fetching employee records:', error));
	}

	if (searchInput) {
		searchInput.addEventListener('input', function () {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => fetchFilteredEmployees(), 300);
		});
	}

	if (departmentSelect) {
		departmentSelect.addEventListener('change', () => fetchFilteredEmployees());
	}

	if (paginationContainer) {
		paginationContainer.addEventListener('click', function (e) {
			const link = e.target.closest('a');
			if (link) {
				e.preventDefault();
				fetchFilteredEmployees(link.href);
			}
		});
	}
});
