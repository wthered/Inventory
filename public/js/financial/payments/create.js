document.addEventListener('DOMContentLoaded', function () {
    const isIncomingSelect = document.getElementById('is_incoming');
    const partyLabelText = document.getElementById('party-label-text');
    const orderLabelText = document.getElementById('order-label-text');
    const partyInput = document.getElementById('party_name');
    const customerIdInput = document.getElementById('customer_id');
    const supplierIdInput = document.getElementById('supplier_id');
    const orderSelect = document.getElementById('order_id');
    const payableTypeInput = document.getElementById('payable_type');
    const resultsContainer = document.getElementById('autocomplete-results');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let debounceTimer;

    function hideDropdown() {
        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('hidden');
    }

    function resetOrdersDropdown() {
        orderSelect.innerHTML = '<option value="">Select party first...</option>';
        orderSelect.disabled = true;
        if (payableTypeInput) payableTypeInput.value = '';
    }

    // Fetch Sales Orders or Purchase Orders for selected party
    function fetchPartyOrders(partyId, isIncoming) {
        orderSelect.innerHTML = '<option value="">Loading orders...</option>';
        orderSelect.disabled = true;

        const endpoint = isIncoming ? `/customers/${partyId}/orders` : `/suppliers/${partyId}/orders`;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        }).then(function (res) {
            return res.json();
        }).then(response => {
            orderSelect.innerHTML = '<option value="">-- No linked order (Optional) --</option>';

            if (response) {
                response.orders.forEach(function (order) {
                    // orderSelect.appendChild(order);
                    orderSelect.innerHTML += order;
                });
            }
            orderSelect.disabled = false;
        }).catch(err => {
            console.error('Failed to load orders:', err);
            orderSelect.innerHTML = '<option value="">Failed to load orders</option>';
        });
    }

    // Direction Switcher
    isIncomingSelect.addEventListener('change', function () {
        const isIncoming = this.value === '1';

        partyLabelText.textContent = isIncoming ? 'Customer' : 'Supplier';
        orderLabelText.textContent = isIncoming ? 'Sales Order' : 'Purchase Order';

        partyInput.value = '';
        customerIdInput.value = '';
        supplierIdInput.value = '';
        resetOrdersDropdown();
        hideDropdown();
    });

    // Party Autocomplete Search
    partyInput.addEventListener('input', function () {
        const query = this.value.trim();
        const isIncoming = isIncomingSelect.value === '1';

        customerIdInput.value = '';
        supplierIdInput.value = '';
        resetOrdersDropdown();

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
                    is_incoming: isIncoming,
                })
            }).then(function (res) {
                return res.json();
            }).then(data => {
                document.getElementById('transaction_reference').value = data.reference;
                resultsContainer.innerHTML = '';
                if (!data || !data.items || data.items.trim() === '') {
                    resultsContainer.innerHTML = '<div class="autocomplete-item text-muted">No matches found</div>';
                } else {
                    resultsContainer.innerHTML = data.items;
                    resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.addEventListener('click', function (e) {
                            e.stopPropagation();
                            partyInput.value = this.dataset.name || this.textContent.trim();
                            const partyId = this.dataset.id;

                            if (isIncoming) {
                                customerIdInput.value = partyId;
                                supplierIdInput.value = '';
                            } else {
                                supplierIdInput.value = partyId;
                                customerIdInput.value = '';
                            }

                            hideDropdown();
                            fetchPartyOrders(partyId, isIncoming);
                        });
                    });
                }
                resultsContainer.classList.remove('hidden');
            });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!partyInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            hideDropdown();
        }
    });
});