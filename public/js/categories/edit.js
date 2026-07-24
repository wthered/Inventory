document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('brand-search-filter');
    const brandItems = document.querySelectorAll('#brands-grid .brand-item');

    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase().trim();

            brandItems.forEach(item => {
                const brandSpan = item.querySelector('.brand-name');
                const brandName = brandSpan.textContent.toLowerCase();
                const brandSlug = brandSpan.getAttribute('title').toLowerCase();

                // Αν το κείμενο ταιριάζει είτε στο όνομα είτε στο slug, το δείχνουμε
                if (brandName.includes(searchTerm) || brandSlug.includes(searchTerm)) {
                    item.style.display = ''; // Επαναφορά στο default (π.χ. flex ή block)
                } else {
                    item.style.display = 'none'; // Κρύβουμε όσα δεν ταιριάζουν
                }
            });
        });
    }
});