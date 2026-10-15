document.addEventListener('DOMContentLoaded', function () {
	const warehouseSelect = document.getElementById('warehouse_id');
	const employeeSelect = document.getElementById('employee_id');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	if (warehouseSelect) {
		warehouseSelect.addEventListener('change', function () {
			const warehouseId = this.value;

			console.log("Fetching All employees for warehouse", warehouseId);
			// Reset employee dropdown
			employeeSelect.innerHTML = '<option value="">-- Φόρτωση Υπαλλήλων… --</option>';
			employeeSelect.disabled = true;

			fetch('/sales/employees/filter', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json'
				},
				body: JSON.stringify({warehouse_id: warehouseId})
			}).then(response => response.json()).then(data => {
				employeeSelect.innerHTML = '<option value="">-- Επιλέξτε Υπάλληλο --</option>';
				console.log("Requested Employees Data:", data);
				employeeSelect.innerHTML = data['employees']
				employeeSelect.disabled = false;
			}).catch(error => {
				console.error('Error fetching employees:', error);
				employeeSelect.innerHTML = '<option value="">-- Σφάλμα Φόρτωσης --</option>';
			});
		});
	}
});
