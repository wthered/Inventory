document.addEventListener('DOMContentLoaded', function () {
    const warehouseId = document.getElementById('filter-warehouse').value;
    const form = document.getElementById('warehouse-filter-form');
    const csrfToken = document.querySelector('input[name="_token"]').value;

    // These suffixes match the route definitions in management.php
    const routeSuffixes = {
        aisle: 'aisles',
        rack:  'racks',
        shelf: 'shelves'
    };

    const selects = document.querySelectorAll('.cascade-filter');

    selects.forEach(select => {
        select.addEventListener('change', function () {
            const nextType = this.dataset.next;
            const selectedValue = this.value;

            // 1. Clear all subsequent child dropdowns to maintain data integrity
            clearChildren(this);

            // 2. If the user selects "All" (empty value), refresh the results immediately
            if (!selectedValue) {
                form.submit();
                return;
            }

            // 3. If there is a next level in the hierarchy, fetch the new options
            if (nextType && routeSuffixes[nextType]) {
                fetchChildData(`/warehouses/${warehouseId}/filter`, nextType, selectedValue);
            } else {
                // If it's the last dropdown (Shelf), submit the form to filter the view
                // form.submit();
            }
        });
    });

    /**
     * Fetches child data from the server and populates the next select menu
     */
    function fetchChildData(url, type, parentValue) {
        const nextSelect = document.getElementById(`filter-${type}`);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                parent_id: parentValue,
                warehouse: warehouseId,
                type: type,
                zone: document.getElementById('filter-zone').value,
                aisle: document.getElementById('filter-aisle').value,
                rack: document.getElementById('filter-rack').value,
                shelf: document.getElementById('filter-shelf').value,
            })
        }).then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        }).then(data => {
            // Re-add the default placeholder
            nextSelect.innerHTML = `<option value="">Select ${type.charAt(0).toUpperCase() + type.slice(1)}</option>`;

            console.log("[Line 67]",data);
            console.log("[Line 68] Type = ",data)

            nextSelect.innerHTML = data['options'];

            const locationsGrid = document.getElementById("locations-grid");
            locationsGrid.innerHTML = data['locations'];

            // Populate with new options
            // data.forEach(item => {
            //     const option = document.createElement('option');
            //     option.value = item.id;
            //     option.textContent = item.name;
            //     nextSelect.appendChild(option);
            // });
        }).catch(error => {
            console.error(`Error fetching ${type}:`, error);
        });
    }

    /**
     * Recursively clears all dependent dropdowns when a parent changes
     */
    function clearChildren(currentSelect) {
        let nextType = currentSelect.dataset.next;
        while (nextType) {
            const nextSelect = document.getElementById(`filter-${nextType}`);
            if (nextSelect) {
                nextSelect.innerHTML = `<option value="">${nextType.charAt(0).toUpperCase() + nextType.slice(1)}</option>`;
                nextType = nextSelect.dataset.next;
            } else {
                nextType = null;
            }
        }
    }
});