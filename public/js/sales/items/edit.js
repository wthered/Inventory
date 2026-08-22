document.addEventListener('DOMContentLoaded', function () {
    initializeOrderItemsTable();

    const parentCategorySelector = document.getElementById('filter_category');
    const filterCategorySelector = document.getElementById('filter_subcategory');
    const filterBrand = document.getElementById('filter_brand');

    parentCategorySelector?.addEventListener('change', async function () {
        const categoryId = this.value;

        if (categoryId) {

            fetchCategoryFilterData(categoryId).then(data => {
                if (!data) return null;
                filterCategorySelector.innerHTML = data.categories || '';
                filterCategorySelector.disabled = false;
                return data; // Pass data to the next .then()!
            }).then(data => {
                if (!data) return null;
                filterBrand.innerHTML = data.brands || '';
                filterBrand.disabled = false;
                return data; // Pass data to the next .then()!
            });
        }
    });

    if (filterCategorySelector) {
        filterCategorySelector.addEventListener('change', async function () {
            const subCategoryId = this.value;

            if (!filterBrand) return;

            if (!subCategoryId) {
                filterBrand.disabled = true;
                filterProducts(); // Reset picker options
                return;
            }

            filterBrand.disabled = true;

            // Fetch brands via AJAX POST
            const brands = await fetchCategoryBrands(subCategoryId);

            if (brands) {
                filterBrand.innerHTML = brands;
                filterBrand.disabled = false;
            } else {
                filterBrand.disabled = true;
            }

            // Trigger local filter refresh for the product picker dropdown
            filterProducts();
        });
    }
});