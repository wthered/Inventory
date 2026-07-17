document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Αρχικοποίηση των υπαρχουσών γραμμών με AJAX βάσει του location_id
    const containers = document.querySelectorAll('.warehouse-cascade-container');
    containers.forEach(container => {
        initializeRowWithAjax(container).then(r => {});
    });

    // --- ΑΡΧΙΚΟΠΟΙΗΣΗ ΜΕΣΩ AJAX (Μόνο με το location_id) ---
    async function initializeRowWithAjax(container) {
        const hiddenInput = container.querySelector('input[type="hidden"]');
        const selects = Array.from(container.querySelectorAll('.level-select'));

        // Παίρνουμε το location_id από το hidden input ή χρησιμοποιούμε το default placeholder 1605
        let locationId = hiddenInput ? hiddenInput.value : 1605;
        console.log("Location ID", locationId);

        try {
            // Στέλνουμε AJAX μόνο με το location_id
            const response = await fetch('/warehouses/location/details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    location_id: parseInt(locationId)
                })
            });

            if (!response.ok) throw new Error("Αποτυχία ανάκτησης στοιχείων θέσης");

            const result = await response.json();

            // Αναμενόμενη δομή απάντησης (response) από τον Controller:
            // {
            //     "levels": {
            //         "zone":  { "html": "<option value='...'>...</option>", "disabled": false},
            //         "aisle": { "html": "<option value='...'>...</option>", "disabled": false},
            //         "rack":  { "html": "<option value='...'>...</option>", "disabled": false},
            //         "shelf": { "html": "<option value='...'>...</option>", "disabled": true,},
            //         "bin":   { "html": "<option value='...'>...</option>", "disabled": true,}
            //     },
            //     "raw_locations": [...] // Όλα τα locations της αποθήκης για μελλοντικά on-the-fly φιλτραρίσματα
            // }
            if (result && result.levels) {
                populateAndSetupDropdowns(container, result);
            }

        } catch (error) {
            console.error("Σφάλμα κατά την αρχικοποίηση της γραμμής:", error);
        }
    }

    // --- ΤΟΠΟΘΕΤΗΣΗ ΤΩΝ HTML OPTIONS ΣΤΑ SELECTS ---
    function populateAndSetupDropdowns(container, data) {
        // Αποθηκεύουμε τα raw options στο container για όταν ο χρήστης αλλάξει χειροκίνητα κάποια τιμή
        if (data.raw_locations) {
            container.setAttribute('data-raw-options', JSON.stringify(data.raw_locations));
        }

        const hiddenInput = container.querySelector('input[type="hidden"]');
        const selects = Array.from(container.querySelectorAll('.level-select'));
        const levelKeys = ['zone', 'aisle', 'rack', 'shelf', 'bin'];

        // 1. Τοποθέτηση των options που έφτιαξε ο Controller απευθείας στα divs
        selects.forEach((sel, idx) => {
            const key = levelKeys[idx];
            const levelData = data.levels[key];

            if (levelData) {
                // Εμφάνιση / Απόκρυψη
                sel.style.display = levelData.visible ? 'block' : 'none';

                // Εισαγωγή έτοιμου HTML
                sel.innerHTML = levelData.html;

                // Κατάσταση Disabled
                sel.disabled = levelData.disabled;
            }
        });

        // 2. Setup Event Listeners για αλλαγές (Cascade) από τον χρήστη
        // Αντικατάσταση των selects για καθαρισμό προηγούμενων listeners
        selects.forEach(select => select.replaceWith(select.cloneNode(true)));
        const freshSelects = Array.from(container.querySelectorAll('.level-select'));

        freshSelects.forEach((select, index) => {
            select.addEventListener('change', function () {
                const currentLevel = parseInt(this.getAttribute('data-level'));
                const rawOptions = JSON.parse(container.getAttribute('data-raw-options') || '[]');

                // α) Καθαρισμός επόμενων επιπέδων
                for (let i = currentLevel + 1; i < freshSelects.length; i++) {
                    const key = levelKeys[i];
                    const nextSel = freshSelects[i];

                    // Επαναφορά στο placeholder option (το 1ο option)
                    if (nextSel.options.length > 0) {
                        nextSel.selectedIndex = 0;
                    }
                    nextSel.disabled = true;
                }

                hiddenInput.value = "";
                if (!this.value) return;

                // β) Εύρεση επόμενου ενεργού επιπέδου
                const nextLevelIdx = getNextActiveLevelIndex(currentLevel, levelKeys, data.levels);

                // Αν είμαστε στο τελευταίο επίπεδο, βρίσκουμε το τελικό location id
                if (nextLevelIdx === -1) {
                    const activeSelects = freshSelects.filter((s, idx) => data.levels[levelKeys[idx]]?.visible);
                    const finalCode = activeSelects.map(s => s.value).join('-');
                    const found = rawOptions.find(opt => opt.code === finalCode);
                    if (found) {
                        hiddenInput.value = found.id;
                    }
                    return;
                }

                // γ) Φιλτράρισμα επόμενου select (on-the-fly client side)
                const prefix = freshSelects.slice(0, currentLevel + 1).map(s => s.value).join('-');
                const validNextValues = [...new Set(
                    rawOptions.filter(opt => opt.code.startsWith(prefix + '-')).map(opt => opt.code.split('-')[nextLevelIdx])
                )];

                const nextSelect = freshSelects[nextLevelIdx];
                filterAndEnableSelect(nextSelect, validNextValues);
            });
        });
    }

    function getNextActiveLevelIndex(currentIndex, levelKeys, levelsConfig) {
        for (let i = currentIndex + 1; i < levelKeys.length; i++) {
            if (levelsConfig[levelKeys[i]] && levelsConfig[levelKeys[i]].visible) {
                return i;
            }
        }
        return -1;
    }

    function filterAndEnableSelect(selectElement, validValues) {
        // Κρατάμε μόνο τα options που ταιριάζουν με το prefix
        Array.from(selectElement.options).forEach((opt, idx) => {
            if (idx === 0) return; // Παρακάμπτουμε το placeholder (π.χ. "Επιλέξτε...")

            if (validValues.includes(opt.value)) {
                opt.style.display = 'block';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        });

        selectElement.selectedIndex = 0;
        selectElement.disabled = false;
    }
});