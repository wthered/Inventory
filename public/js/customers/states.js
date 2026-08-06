document.addEventListener('DOMContentLoaded', function () {
    const stateInput = document.getElementById('state');
    const countrySelect = document.getElementById('country_id');
    const resultsContainer = document.getElementById('countryStates');
    const countryError = document.getElementById('country-select-error');

    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    const token = tokenElement ? tokenElement.getAttribute('content') : '';

    let debounceTimer = null;

    if (!stateInput || !resultsContainer) return;

    // Use 'input' instead of 'keyup' for broader coverage (handles paste, typing, etc.)
    stateInput.addEventListener('input', function () {
        const query = this.value.trim();
        const countryId = countrySelect ? countrySelect.value : null;

        clearTimeout(debounceTimer);

        // Guard Clause: Require Country selection
        if (!countryId) {
            if (query.length > 0 && countryError) {
                countryError.classList.remove('d-none');
            } else if (countryError) {
                countryError.classList.add('d-none');
            }
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
            return;
        }

        if (countryError) {
            countryError.classList.add('d-none');
        }

        // Hide if query is less than 2 chars
        if (query.length < 2) {
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
            return;
        }

        // Show loading text during debounce
        resultsContainer.classList.remove('d-none');
        resultsContainer.innerHTML = '<div class="autocomplete-item loading">Searching...</div>';

        // 250ms Debounce
        debounceTimer = setTimeout(() => {
            fetchStates(query, countryId);
        }, 250);
    });

    function fetchStates(query, countryId) {
        const fetchUrl = `/countries/${countryId}/states`;

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({query: query})
        })
            .then(response => {
                if (!response.ok) throw new Error('Network response failed');
                return response.json();
            })
            .then(data => {
                resultsContainer.innerHTML = '';

                if (!data || data.length === 0) {
                    resultsContainer.innerHTML = '<div class="autocomplete-item no-results">No states found</div>';
                    return;
                }

                // Render returned data
                resultsContainer.innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching states:', error);
                resultsContainer.innerHTML = '<div class="autocomplete-item no-results">Error loading suggestions</div>';
            });
    }

    // Attach click listener once to parent container (Event Delegation)
    resultsContainer.addEventListener('click', function (e) {
        const clickedItem = e.target.closest('.autocomplete-item');
        if (clickedItem && !clickedItem.classList.contains('no-results') && !clickedItem.classList.contains('loading')) {
            stateInput.value = clickedItem.textContent.trim();
            resultsContainer.classList.add('d-none');
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!stateInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    // Clear error indicator when country changes
    if (countrySelect) {
        countrySelect.addEventListener('change', function () {
            if (this.value && countryError) {
                countryError.classList.add('d-none');
            }
        });
    }
});