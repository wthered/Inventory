document.addEventListener('DOMContentLoaded', function () {
    const rootRows = document.querySelectorAll('.root-row.has-children');

    rootRows.forEach(row => {
        row.addEventListener('click', function (e) {
            // Avoid triggering toggle if clicking action buttons or forms
            if (e.target.closest('.action-buttons-group') || e.target.closest('form')) {
                return;
            }

            const parentId = this.getAttribute('data-id');
            const childRows = document.querySelectorAll(`.child-of-${parentId}`);

            // Toggle the rotation indicator state
            this.classList.toggle('is-expanded');

            // Show or hide the related child nodes
            childRows.forEach(child => {
                child.classList.toggle('hidden');
            });
        });
    });
});