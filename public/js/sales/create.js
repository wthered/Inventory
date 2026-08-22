document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize table listeners and autocomplete handlers from common.js
    initializeOrderItemsTable();

    const parentCategorySelector = document.getElementById('filter_category');
    const filterCategorySelector = document.getElementById('filter_subcategory');
    const filterBrandSelector = document.getElementById('filter_brand');

    // 2. Parent Category -> Fetch Subcategories & Brands (AJAX)
    parentCategorySelector?.addEventListener('change', async function () {
        const categoryId = this.value;

        if (categoryId) {
            fetchCategoryFilterData(categoryId).then(data => {
                if (!data) return null;
                filterCategorySelector.innerHTML = data.categories || '';
                filterCategorySelector.disabled = false;
                return data;
            }).then(data => {
                if (!data) return null;
                filterBrandSelector.innerHTML = data.brands || '';
                filterBrandSelector.disabled = false;
                return data;
            });
        } else {
            // Reset selectors when parent is cleared
            if (filterCategorySelector) {
                filterCategorySelector.innerHTML = '<option value="">-- Όλες οι Υποκατηγορίες --</option>';
                filterCategorySelector.disabled = true;
            }
            if (filterBrandSelector) {
                filterBrandSelector.innerHTML = '<option value="">-- Όλα τα Brands --</option>';
                filterBrandSelector.disabled = true;
            }
        }
    });

    // 3. Subcategory -> Fetch Brands (AJAX)
    filterCategorySelector.addEventListener('change', async function () {
        const subCategoryId = this.value;

        if (!filterBrandSelector) return;

        if (!subCategoryId) {
            filterBrandSelector.disabled = true;
            filterProducts(); // Reset picker options if fallback dropdown exists
            return;
        }

        filterBrandSelector.disabled = true;

        // Fetch brands via AJAX POST
        const brands = await fetchCategoryBrands(subCategoryId);

        if (brands) {
            filterBrandSelector.innerHTML = brands;
            filterBrandSelector.disabled = false;
        } else {
            filterBrandSelector.disabled = true;
        }

        // Trigger local filter refresh for legacy fallback picker
        filterProducts();
    });
});