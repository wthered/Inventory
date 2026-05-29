// Λειτουργικότητα Dropdown
document.addEventListener('DOMContentLoaded', () => {
	const dropdowns = document.querySelectorAll('.dropdown');

	dropdowns.forEach(dropdown => {
		const button = dropdown.querySelector('.dropdown-toggle');

		// Εναλλαγή της κλάσης 'active' στο κλικ του κουμπιού
		button.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation(); // Σταματάει το event να φτάσει στο document
			dropdown.classList.toggle('active');
		});
	});

	// Κλείνει όλα τα dropdowns όταν κάνουμε κλικ οπουδήποτε αλλού
	document.addEventListener('click', (e) => {
		dropdowns.forEach(dropdown => {
			if (!dropdown.contains(e.target)) {
				dropdown.classList.remove('active');
			}
		});
	});

	// When I click on a small image, it becomes main Image
	// Get all thumbnail images
	const thumbnails = document.querySelectorAll('img.thumbnail');

	thumbnails.forEach(thumbnail => {
		thumbnail.addEventListener('click', function() {
			// Get the src of the clicked thumbnail
			const newSrc = this.src;

			// Update the main image
			const mainImage = document.getElementById('mainImage');
			if (mainImage) {
				mainImage.src = newSrc;
			}

			// Optional: Also update alt text
			mainImage.alt = this.alt;
		});
	});
});
