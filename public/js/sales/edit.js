// Greek locale currency formatter
const formatter = new Intl.NumberFormat('el-GR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    useGrouping: true
});

document.addEventListener('DOMContentLoaded', function () {
    const totalsContainer = document.querySelector('.sidebar-totals');
    const discountInput = document.getElementById('discount_amount');
    const grandTotalElement = document.getElementById('grand_total');

    if (!totalsContainer || !discountInput || !grandTotalElement) return;

    const subtotal = parseFloat(totalsContainer.dataset.subtotal) || 0;
    const taxAmount = parseFloat(totalsContainer.dataset.tax) || 0;

    function updateGrandTotal() {
        let discount = parseFloat(discountInput.value) || 0;
        let grandTotal = Math.max(0, (subtotal - discount) + taxAmount);

        grandTotalElement.textContent = '€' + formatter.format(grandTotal);
    }

    // Recalculate live on input
    discountInput.addEventListener('input', updateGrandTotal);

    // Initial run on page load (in case a discount is pre-filled/restored by Laravel)
    updateGrandTotal();
});