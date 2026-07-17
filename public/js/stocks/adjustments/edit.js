document.addEventListener('DOMContentLoaded', function () {
    const itemsContainer = document.getElementById('items-container');
    const addItemBtn = document.getElementById('add-item-btn');
    const template = document.getElementById('new-item-template');

    // 1. Προσθήκη Νέας Γραμμής
    addItemBtn.addEventListener('click', function () {
        // Δημιουργία μοναδικού index βασισμένου στο timestamp
        const uniqueIndex = 'new_' + Date.now();

        // Λήψη του περιεχομένου του template
        let templateHtml = template.innerHTML;

        // Αντικατάσταση όλων των __INDEX__ placeholders με το uniqueIndex
        templateHtml = templateHtml.replace(/__INDEX__/g, uniqueIndex);

        // Μετατροπή string σε HTML Node και εισαγωγή στο container
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = templateHtml;
        const newRow = tempDiv.firstElementChild;

        itemsContainer.appendChild(newRow);

        // Ανανέωση των αριθμών των γραμμών (Γραμμή #1, #2 κλπ)
        renumberRows();
    });

    // 2. Αφαίρεση Γραμμής (Event Delegation για να πιάνει και τις νέες γραμμές)
    itemsContainer.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-item-btn')) {
            const rowCard = e.target.closest('.item-row-card');

            if (rowCard) {
                // Επιβεβαίωση πριν τη διαγραφή
                if (confirm('Είστε βέβαιοι ότι θέλετε να αφαιρέσετε αυτή τη γραμμή;')) {
                    rowCard.remove();
                    renumberRows();
                }
            }
        }
    });

    // 3. Συνάρτηση ανανέωσης αρίθμησης των γραμμών στο UI
    function renumberRows() {
        const rows = itemsContainer.querySelectorAll('.item-row-card');
        rows.forEach((row, index) => {
            const titleNode = row.querySelector('.item-row-title div');
            if (titleNode) {
                // Αν είναι ήδη καταχωρημένη γραμμή κρατάει το SKU, αλλιώς γράφει "Νέα Γραμμή"
                const isNew = row.classList.contains('new-item');
                if (isNew) {
                    titleNode.innerHTML = `📦 Γραμμή #${index + 1}: Νέα Γραμμή Προϊόντος`;
                } else {
                    // Για τις παλιές γραμμές, κρατάμε το κείμενο αλλά αλλάζουμε μόνο το νούμερο
                    titleNode.innerHTML = titleNode.innerHTML.replace(/📦 Γραμμή #\d+:/, `📦 Γραμμή #${index + 1}:`);
                }
            }
        });
    }
});
