document.addEventListener('DOMContentLoaded', function () {
    const filterInputs = document.querySelectorAll('.js-payment-filter');
    const typeSelect = document.querySelector('select[name="type"]');
    const methodSelect = document.querySelector('select[name="method"]');
    const statusSelect = document.querySelector('select[name="status"]'); // <-- Προσθήκη Status Select
    const searchInput = document.querySelector('input[name="search"]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function fetchFilteredPayments() {
        const payload = {
            search: searchInput ? searchInput.value : '', // <-- Αλλαγή από 'query' σε 'search'
            type: typeSelect ? typeSelect.value : '',
            method: methodSelect ? methodSelect.value : '',
            status: statusSelect ? statusSelect.value : '' // <-- Προσθήκη Status στο Payload
        };

        fetch('/payments/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(data => {
            if (data.html) {
                const tableWrapper = document.getElementById('payments-table');
                if (tableWrapper) {
                    tableWrapper.innerHTML = data.html;
                }
            }
        }).catch(error => {
            console.error('Filter AJAX Error:', error);
        });
    }

    // Attach listener to all dropdowns and search inputs
    filterInputs.forEach(element => {
        if (element.tagName === 'SELECT') {
            element.addEventListener('change', fetchFilteredPayments);
        }
    });
});