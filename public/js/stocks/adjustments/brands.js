document.addEventListener('DOMContentLoaded', function () {

	// Απόκτηση του CSRF Token από το meta tag της σελίδας
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	// Χρήση Event Delegation στο document για να πιάνουμε και τις δυναμικές/νέες γραμμές
	document.addEventListener('change', async function (e) {

		// Ελέγχουμε αν το στοιχείο που άλλαξε είναι ένα select box κατηγορίας
		if (e.target && e.target.classList.contains('category-select')) {
			const categorySelect = e.target;
			const categoryId = categorySelect.value;

			// Παίρνουμε το μοναδικό ID της γραμμής (π.χ. 1, 2, ή new_1719750000)
			const itemId = categorySelect.dataset.itemId;

			// Εντοπίζουμε το αντίστοιχο Brand Select box της ΙΔΙΑΣ γραμμής
			const brandSelect = document.querySelector(`.brand-select[data-item-id="${itemId}"]`);

			if (!brandSelect) {
				console.error(`Brand select container not found for item ID: ${itemId}`);
				return;
			}

			// Αν ο χρήστης αποεπιλέξει την κατηγορία (κενή τιμή)
			if (!categoryId) {
				brandSelect.innerHTML = '<option value="">Επιλέξτε Μάρκα...</option>';
				return;
			}

			// Εμφάνιση loading κατάστασης στο select
			brandSelect.innerHTML = '<option value="">Φόρτωση Brands...</option>';

			try {
				// Εκτέλεση του AJAX Request προς το backend
				const response = await fetch(`/categories/${categoryId}/brands`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'Accept': 'application/json'
					},
					body: JSON.stringify({ category_id: parseInt(categoryId) })
				});

				if (response.ok) {
					const brands = await response.json();

					// Καθαρισμός και αρχικοποίηση του brand select
					brandSelect.innerHTML = '<option value="">Επιλέξτε Μάρκα...</option>' + brands;

					// Γέμισμα με τα νέα δεδομένα (μάρκες) που επέστρεψε ο Controller
					// brands.forEach(brand => {
					// 	const option = document.createElement('option');
					// 	option.value = brand.id;
					// 	option.textContent = brand.name;
					// 	brandSelect.appendChild(option);
					// });

				} else {
					brandSelect.innerHTML = '<option value="">Σφάλμα κατά τη φόρτωση</option>';
					console.error("Server responded with an error status:", response.status);
				}

			} catch (error) {
				brandSelect.innerHTML = '<option value="">Σφάλμα δικτύου</option>';
				console.error("Error fetching brands via AJAX:", error);
			}
		}
	});
});
