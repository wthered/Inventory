/**
 * Warehouse Management System - Show Page Logic
 */

// 1. Modal Management
function showDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'none';
}

function showReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) modal.style.display = 'flex';
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) modal.style.display = 'none';
}

// 2. Global Event Listeners
document.addEventListener('DOMContentLoaded', function () {

    // --- AJAX Filtering Logic ---
    const filterForm = document.querySelector('.filter-row');
    const locationsGrid = document.querySelector('.locations-grid');

    if (filterForm && locationsGrid) {
        const selects = filterForm.querySelectorAll('select');

        selects.forEach(select => {
            // Αφαιρούμε το inline submit για να διαχειριστούμε το request μέσω JS
            select.removeAttribute('onchange');

            select.addEventListener('change', async function () {
                const formData = new FormData(filterForm);

                // Οπτικό feedback φόρτωσης
                locationsGrid.style.opacity = '0.5';
                locationsGrid.style.pointerEvents = 'none';

                try {
                    const response = await fetch(filterForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();

                    // Ενημέρωση του Grid με το HTML που επιστρέφει ο Server
                    if (data.html) {
                        locationsGrid.innerHTML = data.html;
                    }

                    // Ενημέρωση του Pagination section
                    const paginationSection = document.querySelector('.pagination-section');
                    if (paginationSection && data.pagination) {
                        paginationSection.innerHTML = data.pagination;
                    }

                } catch (error) {
                    console.error('Error fetching locations:', error);
                } finally {
                    locationsGrid.style.opacity = '1';
                    locationsGrid.style.pointerEvents = 'auto';
                }
            });
        });
    }

    // --- Modal & UI Logic ---

    // Close modals when clicking outside the content area
    window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    // Handle Custom Date Range Visibility in Reports
    const dateRange = document.getElementById('dateRange');
    const customDates = document.getElementById('customDates');

    if (dateRange && customDates) {
        dateRange.addEventListener('change', function () {
            customDates.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    }

    // Set Default Dates for Report (Today)
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    if (startDate && endDate) {
        const today = new Date().toISOString().split('T')[0];
        if (!startDate.value) startDate.value = today;
        if (!endDate.value) endDate.value = today;
    }

    // Confirmation for Deactivate/Activate/Delete forms
    const statusForms = document.querySelectorAll('.action-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const btn = this.querySelector('.action-text');
            const btnText = btn ? btn.innerText.toLowerCase() : 'proceed';
            if (!confirm(`Are you sure you want to ${btnText}?`)) {
                e.preventDefault();
            }
        });
    });
});