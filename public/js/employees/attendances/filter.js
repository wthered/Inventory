document.addEventListener('DOMContentLoaded', () => {
	const attFilterBar = document.getElementById('attendance-filter-bar');
	if (!attFilterBar) return;

	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	const fetchFilteredAttendances = async (page = 1) => {
		const employee = attFilterBar.querySelector('#employee_id')?.value;

		// if (!employee) {
		// 	console.error('Employee ID hidden input field not found.');
		// 	return;
		// }

		const filterUrl = `/hr/attendances/employees/${employee}/filter`;

		const statusValue = attFilterBar.querySelector('#att_status')?.value || null;
		const dateFromValue = attFilterBar.querySelector('#att_date_start')?.value || null;
		const dateToValue = attFilterBar.querySelector('#att_date_final')?.value || null;

		const clearErrors = (container) => {
			container.querySelectorAll('.error-text').forEach(span => span.textContent = '');
			container.querySelectorAll('.filter-input').forEach(input => input.classList.remove('is-invalid'));
		};

		const handleValidationErrors = (errors, container) => {
			const fieldMapping = {
				'status': 'att_status',
				'date_start': 'att_date_start',
				'date_final': 'att_date_final'
			};

			Object.keys(errors).forEach(field => {
				const elementId = fieldMapping[field] || field;

				const inputElement = container.querySelector(`#${elementId}`);
				const errorSpan = container.querySelector(`#error-${elementId}`);

				if (inputElement) {
					inputElement.classList.add('is-invalid');
				}
				if (errorSpan) {
					errorSpan.textContent = errors[field][0];
				}
			});
		};

		// Inside fetchFilteredAttendances()
		try {
			clearErrors(attFilterBar);

			const response = await fetch(filterUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					status: statusValue,
					date_start: dateFromValue,
					date_final: dateToValue,
					att_page: page
				}),
			});

			if (response.status === 422) {
				const errorData = await response.json();
				handleValidationErrors(errorData.errors, attFilterBar);
				return;
			}

			if (!response.ok) {
				throw new Error(`Server returned status ${response.status}`);
			}

			const data = await response.json();

			// Synchronize inputs with sanitized values sent back by backend
			if (data.filters) {
				if (data.filters.date_start) {
					attFilterBar.querySelector('#att_date_start').value = data.filters.date_start;
				}
				if (data.filters.date_final) {
					attFilterBar.querySelector('#att_date_final').value = data.filters.date_final;
				}
			}

			document.getElementById('attendance-table-container').innerHTML = data.attendances;

		} catch (error) {
			console.error('Error fetching attendance filters:', error);
		}
	};

	// Add inside document.addEventListener('DOMContentLoaded', () => { ... })
	const tableContainer = document.getElementById('attendance-table-container');

	tableContainer.addEventListener('click', (e) => {
		const link = e.target.closest('.pagination a');
		if (!link) return;

		e.preventDefault();

		// Extract page number from clicked pagination URL
		const urlParams = new URLSearchParams(new URL(link.href).search);
		const page = urlParams.get('page') || urlParams.get('att_page') || 1;

		// Trigger filter request including page parameter
		fetchFilteredAttendances(page).then(function (r) {
			console.log("[Filter.js:100] Filtered Attendances", r);
		});
	});

	// Attach change listener to attendance inputs
	attFilterBar.querySelectorAll('select, input').forEach(element => {
		if (element.type === 'hidden') return;

		element.addEventListener('change', fetchFilteredAttendances);
	});
});
