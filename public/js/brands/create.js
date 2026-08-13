document.addEventListener('DOMContentLoaded', function () {
    const parentFilter = document.getElementById('parent_filter');
    const subcategorySelect = document.getElementById('subcategory_select');

    function fetchChildrenCategories(parentId) {
        // Reset subcategory dropdown if no parent category is selected
        if (!parentId) {
            subcategorySelect.innerHTML = `<option value="">-- Επιλέξτε πρώτα γονική κατηγορία --</option>`;
            subcategorySelect.disabled = true;
            return;
        }

        // Loading state
        subcategorySelect.disabled = true;
        subcategorySelect.innerHTML = `<option value="">Φόρτωση υποκατηγοριών...</option>`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`/categories/${parentId}/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP status: ${response.status}`);
            }
            return response.json(); // Parses the JSON payload: { categories: "...", brands: "...", products: "..." }
        }).then(data => {
            // Check if the HTML options string exists in data.categories
            if (data.categories) {
                subcategorySelect.innerHTML = data.categories;
                subcategorySelect.disabled = false;
            } else {
                subcategorySelect.innerHTML = `<option value="">Δε βρέθηκαν υποκατηγορίες</option>`;
            }
        }).catch(error => {
            console.error('Error fetching subcategories:', error);
            subcategorySelect.innerHTML = `<option value="">Σφάλμα κατά τη φόρτωση των υποκατηγοριών</option>`;
        });
    }

    // Event listener for parent category select change
    if (parentFilter) {
        parentFilter.addEventListener('change', function () {
            fetchChildrenCategories(this.value);
        });
    }
});