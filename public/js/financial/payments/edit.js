/**
 * Payment Edit Autocomplete & Direction Switcher (payments/edit.js)
 */
document.addEventListener('DOMContentLoaded', function () {
    const isIncomingSelect = document.getElementById('is_incoming');
    const partyLabelText = document.getElementById('party-label-text');
    const partyInput = document.getElementById('party_name');
    const customerIdInput = document.getElementById('customer_id');
    const supplierIdInput = document.getElementById('supplier_id');
    const resultsContainer = document.getElementById('autocomplete-results');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let debounceTimer;
    let isSelectingItem = false;

    if (!isIncomingSelect || !partyInput || !resultsContainer) return;

    function hideDropdown() {
        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('hidden');
    }

    // Toggle Label & Reset values when payment type changes
    isIncomingSelect.addEventListener('change', function () {
        const selectedType = this.value;

        if (selectedType === 'receipt' || selectedType === 'refund') {
            partyLabelText.textContent = 'Customer';
        } else {
            partyLabelText.textContent = 'Supplier';
        }

        partyInput.value = '';
        customerIdInput.value = '';
        supplierIdInput.value = '';
        hideDropdown();
    });

    // Handle POST AJAX Search on keyup
    partyInput.addEventListener('input', function () {
        // Skip fetching if the change was triggered by selecting an item
        if (isSelectingItem) {
            isSelectingItem = false;
            return;
        }

        const query = this.value.trim();
        const rawType = isIncomingSelect.value;
        const isIncoming = (rawType === 'receipt' || rawType === 'refund');

        // Clear existing IDs when user types new text
        customerIdInput.value = '';
        supplierIdInput.value = '';

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            hideDropdown();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch('/payments/edit/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    party_name: query,
                    payment_type: isIncoming
                })
            }).then(function (response) {
                return response.json();
            }).then(data => {
                resultsContainer.innerHTML = '';

                if (!data || !data.items || data.items.trim() === '') {
                    resultsContainer.innerHTML = '<div class="autocomplete-item text-muted">No matches found</div>';
                } else {
                    // 1. Inject the HTML string returned by the server
                    resultsContainer.innerHTML = data.items;

                    // 2. Select all rendered items and attach click handlers
                    resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.addEventListener('click', function (e) {
                            e.stopPropagation();
                            isSelectingItem = true;

                            // Set input text display
                            partyInput.value = this.dataset.name || this.textContent.trim();

                            // Assign customer or supplier ID based on payment direction
                            if (isIncoming) {
                                document.querySelector('#customer_id').value = this.dataset.id;
                                document.querySelector('#supplier_id').value = '';
                            } else {
                                document.querySelector('#supplier_id').value = this.dataset.id;
                                document.querySelector('#customer_id').value = '';
                            }

                            hideDropdown();
                        });
                    });
                }
                resultsContainer.classList.remove('hidden');
            }).catch(error => {
                console.error('Search error:', error);
            });
        }, 300);
    });

    // Close autocomplete when clicking outside
    document.addEventListener('click', function (e) {
        if (!partyInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            hideDropdown();
        }
    });
});