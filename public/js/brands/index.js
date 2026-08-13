document.addEventListener('DOMContentLoaded', function () {
    const parentCategorySelect = document.getElementById('parent_category_id');
    const childCategorySelect = document.getElementById('category_id');
    const searchInput = document.getElementById('search');
    const tableBody = document.querySelector('.data-table tbody');
    const paginationContainer = document.querySelector('.table-pagination');
    const filterForm = document.querySelector('.table-filters-bar form');

    // 1. When Parent Category changes -> Submit form to reload index
    if (parentCategorySelect && filterForm) {
        parentCategorySelect.addEventListener('change', function () {
            // Reset child category select before submitting so we only filter by parent
            if (childCategorySelect) {
                childCategorySelect.value = '';
            }
            filterForm.submit();
        });
    }

    // 2. When Child Subcategory changes -> AJAX fetch for brands table
    if (childCategorySelect) {
        childCategorySelect.addEventListener('change', function () {
            const categoryId = this.value;

            // If "All subcategories" is selected, submit form to fall back to parent index view
            if (!categoryId && filterForm) {
                filterForm.submit();
                return;
            }

            // Define the payload body to send with the POST request
            const requestBody = {
                category_id: categoryId,
                parent_category_id: parentCategorySelect ? parentCategorySelect.value : null,
                search: searchInput ? searchInput.value.trim() : ''
            };

            fetch(`/categories/${categoryId}/brands/list`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(requestBody),
            }).then(res => res.json()).then(data => {
                console.log("Brand List:", data);
                if (data.success) {
                    tableBody.innerHTML = data.rows;
                } else {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="table-empty-state">
                                <i class="fas fa-folder-open empty-icon"></i>
                                <p>Δε βρέθηκαν καταχωρημένα brands για τη συγκεκριμένη υποκατηγορία.</p>
                            </td>
                        </tr>`;
                }

                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination || '';
                }
            }).catch(err => console.error('Error fetching brand list:', err));
        });
    }
});