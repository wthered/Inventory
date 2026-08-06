document.addEventListener('DOMContentLoaded', () => {
    const itemsContainer = document.getElementById("products-items");

    const parentCategorySelect = document.getElementById('parent_category');
    const childCategorySelect = document.getElementById('child_category');
    const brandSelect = document.getElementById('filter_brand');
    const supplierSelect = document.getElementById('filter_supplier');
    const statusSelect = document.getElementById('filter_stock');

    const token = document.querySelector('meta[name="csrf-token"]').content;

    // Where the products are being shown
    const productItems = document.getElementById('products-items');

    // Fetch Sub-Categories dynamically
    const fetchSubCategories = async (parentId) => {
        // Reset sub-category dropdown
        childCategorySelect.innerHTML = '<option value="">All Sub Categories</option>';

        if (!parentId) return;

        try {
            itemsContainer.style.opacity = '0.5';
            const response = await fetch(`/categories/${parentId}/filter`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({}),
            });
            const children = await response.json();
            childCategorySelect.innerHTML = children.categories;
            brandSelect.innerHTML = children.brands;
            productItems.innerHTML = children.products;
            itemsContainer.style.opacity = '1';
        } catch (error) {
            console.error('Error fetching subcategories:', error);
        }
    };

    // Trigger subcategory loading on parent category change
    parentCategorySelect.addEventListener('change', (e) => {
        fetchSubCategories(e.target.value).then(r => {
            console.clear();
            console.log("Children Categories:", r);
        });
    });

    childCategorySelect.addEventListener('change', (e) => {
        console.log("Products in child category:", e.target.value);
        fetchFilteredProducts(e.target.value).then(r => {
            console.clear();
            console.log("Filtered Products:", r);
        });
    });

    brandSelect.addEventListener('change', (e) => {
        console.log("Products in brand:", e.target.value);
        fetchFilteredProducts(e.target.value).then(r => {
            console.clear();
            console.log("Filtered Products:", r);
        });
    });

    supplierSelect.addEventListener('change', (e) => {
        console.log("Products by supplier:", e.target.value);
        fetchFilteredProducts(e.target.value).then(r => {
            console.clear();
            console.log("Filtered Products:", r);
        });
    });

    const fetchFilteredProducts = async () => {
        const payload = {
            category: parentCategorySelect.value,
            child_category: childCategorySelect.value,
            brand: brandSelect.value,
            supplier: supplierSelect.value,
            status: statusSelect.value,
        };

        try {
            itemsContainer.style.opacity = '0.5';
            const response = await fetch('/products/filter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (data.success) {
                // Products Results
                itemsContainer.innerHTML = data['products'];

                // Brands Select dropdown
                brandSelect.innerHTML = data['brands'];

                // Pagination Pages
                const paginationContainer = document.querySelector('.pagination');
                console.log("Pagination:", data['pagination']);
                if (paginationContainer && data['pagination']) {
                    paginationContainer.innerHTML = data['pagination'];
                }
                itemsContainer.style.opacity = '1';
            }
        } catch (error) {
            console.error('Filter Error:', error);
        }
    };

    // filters.forEach(filter => {
    //     filter.addEventListener('change', fetchFilteredProducts);
    // });
});