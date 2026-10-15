document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('search-input');
	const departmentSelect = document.getElementById('department-select');
	const warehouseSelect = document.getElementById('warehouse-select');
	const positionSelect = document.getElementById('position-select');
	const statusSelect = document.getElementById('status-select');
	const tableBody = document.getElementById('employees-table-body');
	const paginationContainer = document.getElementById('pagination-container');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

	let debounceTimer;

	function fetchFilteredEmployees(pageUrl = '/hr/employees/filter') {
		const query = searchInput ? searchInput.value : '';
		const department = departmentSelect ? departmentSelect.value : '';
		const warehouse = warehouseSelect ? warehouseSelect.value : '';
		const position = positionSelect ? positionSelect.value : '';
		const status = statusSelect ? statusSelect.value : '';

		fetch(pageUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': csrfToken,
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				search: query,
				warehouse: warehouse,
				department: department,
				position: position,
				status: status,
			})
		}).then(response => {
			if (!response.ok) throw new Error('Network response failed');
			return response.json();
		}).then(data => {
			if (tableBody && data.html !== undefined) {
				tableBody.innerHTML = data.html;
			}
			if (paginationContainer && data.pagination !== undefined) {
				paginationContainer.innerHTML = data.pagination;
			}
		}).catch(error => console.error('Error fetching employee records:', error));
	}

	// Input Text Event (Debounced)
	if (searchInput) {
		searchInput.addEventListener('input', function () {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => fetchFilteredEmployees(), 300);
		});
	}

	// Select Dropdowns Change Events
	[departmentSelect, warehouseSelect, positionSelect, statusSelect].forEach(select => {
		if (select) {
			select.addEventListener('change', () => fetchFilteredEmployees());
		}
	});

	// Pagination AJAX Click Handling
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
