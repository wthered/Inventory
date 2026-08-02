document.addEventListener('DOMContentLoaded', function () {
    const stateInput = document.getElementById('state');
    const countrySelect = document.getElementById('country_id');
    const resultsContainer = document.getElementById('countryStates');
    const countryError = document.getElementById('country-select-error');

    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    const token = tokenElement ? tokenElement.getAttribute('content') : '';

    let debounceTimer = null;

    if (!stateInput || !resultsContainer) return;

    stateInput.addEventListener('keyup', function () {
        const query = this.value.trim();
        const countryId = countrySelect ? countrySelect.value : null;

        // Clear previous timer on every keyup
        clearTimeout(debounceTimer);

        // 1. Guard Clause: Require a selected Country
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

        // Hide country warning if a country is selected
        if (countryError) {
            countryError.classList.add('d-none');
        }

        // 2. Hide container if search query is too short
        if (query.length < 2) {
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
            return;
        }

        // 3. Show loading indicator while waiting for the 3-second debounce
        resultsContainer.classList.remove('d-none');
        resultsContainer.innerHTML = '<div class="autocomplete-item loading"><i class="ri-loader-4-line spin"></i> Searching in 3 seconds...</div>';

        // 4. Set 3000ms (3 seconds) delay after last keyup
        debounceTimer = setTimeout(() => {
            fetchStates(query, countryId);
        }, 3000);
    });

    function fetchStates(query, countryId) {
        resultsContainer.innerHTML = '<div class="autocomplete-item loading">Fetching results...</div>';

        const fetchUrl = `/countries/${countryId}/states`;

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({query: query})
        }).then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        }).then(data => {
            resultsContainer.innerHTML = '';

            if (!data || data.length === 0) {
                resultsContainer.innerHTML = '<div class="autocomplete-item no-results">No states found</div>';
                return;
            }

            console.log("Response:", data);
            data.forEach(item => {
                const div = document.createElement('div');

                // On selection
                div.addEventListener('click', function () {
                    stateInput.value = item.name || item;
                    resultsContainer.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                });

                resultsContainer.appendChild(div);
            });
        }).catch(error => {
            console.error('Error fetching states:', error);
            resultsContainer.innerHTML = '<div class="autocomplete-item no-results">Error loading suggestions</div>';
        });
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!stateInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    // Clear country error automatically if user selects a country later
    if (countrySelect) {
        countrySelect.addEventListener('change', function () {
            if (this.value && countryError) {
                countryError.classList.add('d-none');
            }
        });
    }
});