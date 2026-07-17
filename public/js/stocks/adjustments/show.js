// Ανοιγοκλείσιμο του Dropdown
function toggleSplitDropdown(event) {
    // Αν το event έχει περαστεί (ή υπάρχει global window.event), σταματάμε το propagation
    const e = event || window.event;
    if (e) {
        e.stopPropagation();
    }

    const menu = document.getElementById('split-dropdown-menu');
    if (menu) {
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
}

// Διαχείριση επιλογής status από τη λίστα
function selectStatus(event, value, label) {
    if (event) {
        event.preventDefault();
    }

    // 1. Ενημέρωση του κρυφού input της φόρμας
    const input = document.getElementById('selected-status-value');
    if (input) {
        input.value = value;
    }

    // 2. Δυναμική αλλαγή του κειμένου στο Main Button
    const mainBtn = document.getElementById('main-action-btn');
    if (mainBtn) {
        mainBtn.innerHTML = '💾 Σε ' + label;
    }

    // 3. Κλείσιμο του μενού
    const menu = document.getElementById('split-dropdown-menu');
    if (menu) {
        menu.style.display = 'none';
    }
}

// Περιμένουμε να φορτώσει το DOM για τα hover εφέ
document.addEventListener('DOMContentLoaded', function () {
    // Κλείσιμο του dropdown αν ο χρήστης κάνεις κλικ οπουδήποτε αλλού στην οθόνη
    window.addEventListener('click', function (e) {
        const menu = document.getElementById('split-dropdown-menu');
        if (menu && menu.style.display === 'block') {
            menu.style.display = 'none';
        }
    });

    // Εφέ hover για τα αντικείμενα του μενού
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('mouseenter', () => item.style.backgroundColor = '#f1f5f9');
        item.addEventListener('mouseleave', () => item.style.backgroundColor = 'transparent');
    });
});