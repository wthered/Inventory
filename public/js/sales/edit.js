document.addEventListener('DOMContentLoaded', function () {
    const totalsContainer = document.querySelector('.sidebar-totals');
    const discountInput = document.getElementById('discount_amount');
    const grandTotalElement = document.getElementById('grand_total');
    const subtotalDisplay = document.getElementById('subtotal_display');
    const taxAmountHidden = document.getElementById('tax_hidden');
    const itemsTable = document.getElementById('order_items_table');

    if (!totalsContainer || !discountInput || !grandTotalElement) return;

    const taxAmount = parseFloat(totalsContainer.dataset.tax) || 0;

    function updateGrandTotal() {
        // 1. Get live subtotal from current item rows
        const liveSubtotal = typeof window.calculateLiveSubtotal === 'function'
            ? window.calculateLiveSubtotal()
            : 0;

        // 2. Update subtotal display
        if (subtotalDisplay) {
            subtotalDisplay.textContent = '€' + window.formatter.format(liveSubtotal);
        }

        // 3. Read and clamp discount value boundaries
        let discount = parseFloat(discountInput.value) || 0;

        if (discount < 0) {
            discount = 0;
            discountInput.value = '0.00';
        } else if (discount > liveSubtotal) {
            discount = liveSubtotal;
            discountInput.value = liveSubtotal.toFixed(2);
        }

        // 4. Calculate grand total
        const grandTotal = Math.max(0, (liveSubtotal - discount) + taxAmount);

        // 5. Update DOM UI & Hidden Inputs
        grandTotalElement.textContent = '€' + window.formatter.format(grandTotal);
        if (taxAmountHidden) {
            taxAmountHidden.value = taxAmount.toFixed(2);
        }
    }

    // Listen for discount input changes
    discountInput.addEventListener('input', updateGrandTotal);

    // Listen for line item updates (Qty, Unit Price, Item Discount %)
    if (itemsTable) {
        itemsTable.addEventListener('input', function (e) {
            if (
                e.target.classList.contains('input-qty') ||
                e.target.classList.contains('input-price') ||
                e.target.classList.contains('input-discount')
            ) {
                updateGrandTotal();
            }
        });

        // Listen for added or deleted rows
        const observer = new MutationObserver(updateGrandTotal);
        const tbody = itemsTable.querySelector('tbody');
        if (tbody) {
            observer.observe(tbody, {childList: true});
        }
    }

    // Initial run on page load
    updateGrandTotal();
});