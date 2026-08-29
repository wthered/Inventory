document.addEventListener('DOMContentLoaded', () => {
	const leaveFilterBar = document.getElementById('leave-filter-bar');
	const leaveContainer = document.getElementById('leave-table-container');
	if (!leaveFilterBar || !leaveContainer) return;

	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
	const employeeId = document.getElementById('employee_id')?.value;
	const filterUrl = `/hr/leaves/employees/${employeeId}/filter`;

	const clearErrors = () => {
		leaveFilterBar.querySelectorAll('.filter-input').forEach(i => i.classList.remove('is-invalid'));
		leaveFilterBar.querySelectorAll('.error-text').forEach(s => s.textContent = '');
	};

	const handleValidationErrors = (errors) => {
		Object.keys(errors).forEach(field => {
			const inputElement = leaveFilterBar.querySelector(`#${field}`);
			const errorSpan = leaveFilterBar.querySelector(`#error-${field}`);

			if (inputElement) inputElement.classList.add('is-invalid');
			if (errorSpan) errorSpan.textContent = errors[field][0];
		});
	};

	const fetchFilteredLeaves = async (page = 1) => {
		clearErrors();

		const typeValue = leaveFilterBar.querySelector('#leave_type').value;
		const statusValue = leaveFilterBar.querySelector('#leave_status').value;
		const dateFromValue = leaveFilterBar.querySelector('#leave_date_from').value;
		const dateToValue = leaveFilterBar.querySelector('#leave_date_to').value;

		try {
			const response = await fetch(filterUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					leave_type: typeValue,
					leave_status: statusValue,
					leave_date_start: dateFromValue,
					leave_date_final: dateToValue,
					leave_page: page,
				}),
			});

			const data = await response.json();

			if (response.status === 422) {
				handleValidationErrors(data.errors);
				return;
			}

			if (data.filters) {
				if (data.filters.leave_date_from) leaveFilterBar.querySelector('#leave_date_from').value = data.filters.leave_date_from;
				if (data.filters.leave_date_to) leaveFilterBar.querySelector('#leave_date_to').value = data.filters.leave_date_to;
			}

			leaveContainer.innerHTML = data.leaves;
			return data;
		} catch (error) {
			console.error('Error fetching filtered leaves:', error);
		}
	};

	// Filter input triggers
	leaveFilterBar.querySelectorAll('.filter-input').forEach(input => {
		input.addEventListener('change', () => fetchFilteredLeaves(1));
	});

	// Pagination links delegation
	leaveContainer.addEventListener('click', (e) => {
		const link = e.target.closest('.pagination a');
		if (!link) return;

		e.preventDefault();
		const urlParams = new URLSearchParams(new URL(link.href).search);
		const page = urlParams.get('leave_page') || urlParams.get('page') || 1;

		fetchFilteredLeaves(page).then(function (r) {
			console.log("Fetched leaves on page", page, ":", r);
		});
	});
});
