document.addEventListener('DOMContentLoaded', () => {
    const filterCard = document.querySelector('.filter-card');
    if (!filterCard) return;

    const filterForm = filterCard.querySelector('form');
    if (!filterForm) return;

    const tableContainer = document.querySelector('.table-container');
    if (!tableContainer) return;

    let debounceTimer;

    const fields = {
        product: document.getElementById('search'),
        reason: document.getElementById('reason'),
        date: document.getElementById('date'),
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Fetch and replace content via AJAX
    const fetchFilteredData = (url) => {
        console.log("Fetching Filtered Data from", url);
        // Optional: Indicate loading state visually
        tableContainer.style.opacity = '0.5';

        let formDataObject = {
            product: fields.product.value,
            reason: fields.reason.value,
            date: fields.date.value,
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(formDataObject)
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(html => {
            document.getElementById('adjustments').innerHTML = html['adjustments'];
            document.getElementById('pagination').innerHTML = html['pagination'];
        }).catch(error => {
            console.error('Error fetching filtered data:', error);
        }).finally(() => {
            tableContainer.style.opacity = '1';
        });
    };

    // Build query URL from current form inputs
    const handleFilterChange = () => {
        fetchFilteredData('/adjustments/filter');
    };

    // Event listener for <select> and <input type="date">
    filterForm.querySelectorAll('select, input[type="date"]').forEach(element => {
        element.addEventListener('change', handleFilterChange);
    });

    // Event listener with debounce for text inputs
    filterForm.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(handleFilterChange, 400); // Wait 400ms after typing stops
        });
    });

    // Handle form submit button (e.g. pressing Enter or clicking button)
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        clearTimeout(debounceTimer);
        handleFilterChange();
    });

    // Handle AJAX pagination clicks dynamically
    document.addEventListener('click', (e) => {
        const paginationLink = e.target.closest('.pagination-footer a');
        if (paginationLink) {
            e.preventDefault();
            fetchFilteredData(paginationLink.href);
        }
    });

    // Handle browser Back/Forward navigation
    window.addEventListener('popstate', () => {
        fetchFilteredData(window.location.href);
    });
});