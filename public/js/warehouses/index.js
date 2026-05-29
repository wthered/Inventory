document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded');
    // Bulk Actions
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('applyBulkAction').addEventListener('click', function() {
        const action = document.getElementById('bulkAction').value;
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(checkbox => checkbox.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one warehouse.');
            return;
        }

        if (!action) {
            alert('Please select an action.');
            return;
        }

        if (action === 'delete' && !confirm(`Delete ${selectedIds.length} warehouse(s)? This cannot be undone.`)) {
            return;
        }

        // In a real app, you would send this to your server
        console.log('Bulk action:', action, 'on IDs:', selectedIds);
        alert(`${action} action applied to ${selectedIds.length} warehouse(s).`);
    });

    // Export function
    function exportTable() {
        alert('Export functionality would be implemented here.');
    }

    // Modal functions
    function showDeleteModal(id, name) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const nameSpan = document.getElementById('deleteWarehouseName');

        nameSpan.textContent = name;
        form.action = `/warehouses/${id}`;
        modal.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Hover effects for table rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.warehouse-row');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
});