document.addEventListener('DOMContentLoaded', () => {
	const filterForm = document.getElementById('leave-filter-form');
	const filterSelects = filterForm.querySelectorAll('.select-filter');
	const tableData = document.getElementById('leaves-table');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	filterSelects.forEach(select => {
		// console.log("Select ", select.innerHTML);
		select.addEventListener('change', async () => {
			try {
				const response = await fetch('/hr/leaves/filter', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrfToken
					},
					body: JSON.stringify({
						'employee_id': document.getElementById('employee_id')?.value,
						'leave_type': document.getElementById('leave_type_id')?.value,
						'leave_status': document.getElementById('leave_status')?.value,
					})
				});

				if (!response.ok) throw new Error('Filter request failed');

				const data = await response.json();

				// Expecting JSON response with rendered HTML partial for table content
				if (data.html) {
					tableData.innerHTML = data.html;
					document.getElementById('pagination').innerHTML = data.pagination;
				}
			} catch (error) {
				console.error('Error fetching filtered leave requests:', error);
			}
		});
	});
});
