// history.js

class ProductHistory {
	constructor() {
		this.currentFilters = {
			dateRange: 'all',
			startDate: '',
			endDate: '',
			transactionType: 'all',
			warehouse: 'all',
			user: 'all',
			search: ''
		};

		this.currentSort = {
			field: 'date',
			direction: 'desc'
		};

		this.init();
	}

	init() {
		this.initEventListeners();
		this.calculateStatistics();
		this.updateResultsCount();
	}

	initEventListeners() {
		// Filter toggle
		document.getElementById('filterToggle').addEventListener('click', () => {
			this.toggleFilters();
		});

		// Date range change
		document.getElementById('dateRange').addEventListener('change', (e) => {
			this.handleDateRangeChange(e.target.value);
		});

		// Filter application
		document.getElementById('applyFilters').addEventListener('click', () => {
			this.applyFilters();
		});

		// Clear filters
		document.getElementById('clearFilters').addEventListener('click', () => {
			this.clearFilters();
		});

		// Search functionality
		document.getElementById('searchHistory').addEventListener('input', (e) => {
			this.handleSearch(e.target.value);
		});

		// Table sorting
		document.querySelectorAll('.history-table th[data-sort]').forEach(th => {
			th.addEventListener('click', () => {
				this.handleSort(th.dataset.sort);
			});
		});

		// Export functionality
		document.getElementById('exportHistory').addEventListener('click', () => {
			this.exportHistory();
		});

		// View transaction details
		document.addEventListener('click', (e) => {
			if (e.target.closest('.view-details')) {
				const button = e.target.closest('.view-details');
				this.viewTransactionDetails(button.dataset.id);
			}
		});

		// Modal close
		document.querySelector('#transactionModal .close').addEventListener('click', () => {
			this.closeModal();
		});

		// Close modal on outside click
		document.getElementById('transactionModal').addEventListener('click', (e) => {
			if (e.target === document.getElementById('transactionModal')) {
				this.closeModal();
			}
		});
	}

	toggleFilters() {
		const filtersSection = document.getElementById('filtersSection');
		const filterToggle = document.getElementById('filterToggle');

		filtersSection.classList.toggle('active');

		if (filtersSection.classList.contains('active')) {
			filterToggle.innerHTML = '<i class="fas fa-times"></i> Close Filters';
			filterToggle.classList.add('btn-secondary');
		} else {
			filterToggle.innerHTML = '<i class="fas fa-filter"></i> Filters';
			filterToggle.classList.remove('btn-secondary');
		}
	}

	handleDateRangeChange(range) {
		const customRange = document.getElementById('customDateRange');

		if (range === 'custom') {
			customRange.style.display = 'grid';
			customRange.style.gridTemplateColumns = 'auto 1fr auto 1fr';
			customRange.style.gap = '0.5rem';
			customRange.style.alignItems = 'center';
		} else {
			customRange.style.display = 'none';
			this.updatePeriodDisplay(range);
		}
	}

	updatePeriodDisplay(range) {
		const periods = {
			'all': 'All Time',
			'today': 'Today',
			'yesterday': 'Yesterday',
			'week': 'This Week',
			'month': 'This Month',
			'quarter': 'This Quarter',
			'year': 'This Year',
			'custom': 'Custom Range'
		};

		document.getElementById('periodDisplay').textContent = periods[range] || 'All Time';
	}

	applyFilters() {
		this.currentFilters = {
			dateRange: document.getElementById('dateRange').value,
			startDate: document.getElementById('startDate').value,
			endDate: document.getElementById('endDate').value,
			transactionType: document.getElementById('transactionType').value,
			warehouse: document.getElementById('warehouseFilter').value,
			user: document.getElementById('userFilter').value,
			search: this.currentFilters.search
		};

		this.filterTransactions();
		this.calculateStatistics();
		this.toggleFilters();
	}

	clearFilters() {
		document.getElementById('dateRange').value = 'all';
		document.getElementById('transactionType').value = 'all';
		document.getElementById('warehouseFilter').value = 'all';
		document.getElementById('userFilter').value = 'all';
		document.getElementById('searchHistory').value = '';
		document.getElementById('customDateRange').style.display = 'none';

		this.currentFilters = {
			dateRange: 'all',
			startDate: '',
			endDate: '',
			transactionType: 'all',
			warehouse: 'all',
			user: 'all',
			search: ''
		};

		this.filterTransactions();
		this.calculateStatistics();
		this.updatePeriodDisplay('all');
	}

	handleSearch(searchTerm) {
		this.currentFilters.search = searchTerm.toLowerCase();
		this.filterTransactions();
		this.updateResultsCount();
	}

	filterTransactions() {
		const rows = document.querySelectorAll('#historyTableBody .transaction-row');
		let visibleCount = 0;

		rows.forEach(row => {
			const matchesSearch = this.matchesSearch(row);
			const matchesFilters = this.matchesFilters(row);

			if (matchesSearch && matchesFilters) {
				row.style.display = '';
				visibleCount++;
			} else {
				row.style.display = 'none';
			}
		});

		this.updateResultsCount(visibleCount);
	}

	matchesSearch(row) {
		if (!this.currentFilters.search) return true;

		const searchableText = row.textContent.toLowerCase();
		return searchableText.includes(this.currentFilters.search);
	}

	matchesFilters(row) {
		const type = row.dataset.type;
		const warehouse = row.dataset.warehouse;
		const user = row.dataset.user;
		const date = row.dataset.date;

		// Type filter
		if (this.currentFilters.transactionType !== 'all' && type !== this.currentFilters.transactionType) {
			return false;
		}

		// Warehouse filter
		if (this.currentFilters.warehouse !== 'all' && warehouse !== this.currentFilters.warehouse) {
			return false;
		}

		// User filter
		if (this.currentFilters.user !== 'all' && user !== this.currentFilters.user) {
			return false;
		}

		// Date filter
		if (this.currentFilters.dateRange !== 'all') {
			const rowDate = new Date(date);
			return this.matchesDateFilter(rowDate);
		}

		return true;
	}

	matchesDateFilter(date) {
		const today = new Date();
		const startOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate());

		switch (this.currentFilters.dateRange) {
			case 'today':
				return date >= startOfDay;
			case 'yesterday':
				const yesterday = new Date(startOfDay);
				yesterday.setDate(yesterday.getDate() - 1);
				return date >= yesterday && date < startOfDay;
			case 'week':
				const startOfWeek = new Date(startOfDay);
				startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
				return date >= startOfWeek;
			case 'month':
				const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
				return date >= startOfMonth;
			case 'quarter':
				const quarter = Math.floor(today.getMonth() / 3);
				const startOfQuarter = new Date(today.getFullYear(), quarter * 3, 1);
				return date >= startOfQuarter;
			case 'year':
				const startOfYear = new Date(today.getFullYear(), 0, 1);
				return date >= startOfYear;
			case 'custom':
				if (this.currentFilters.startDate && this.currentFilters.endDate) {
					const start = new Date(this.currentFilters.startDate);
					const end = new Date(this.currentFilters.endDate);
					end.setDate(end.getDate() + 1); // Include end date
					return date >= start && date < end;
				}
				return true;
			default:
				return true;
		}
	}

	handleSort(field) {
		// Toggle direction if same field
		if (this.currentSort.field === field) {
			this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
		} else {
			this.currentSort.field = field;
			this.currentSort.direction = 'desc';
		}

		this.sortTransactions();
		this.updateSortIndicators();
	}

	sortTransactions() {
		const tbody = document.getElementById('historyTableBody');
		const rows = Array.from(tbody.querySelectorAll('.transaction-row'));

		rows.sort((a, b) => {
			let aValue, bValue;

			switch (this.currentSort.field) {
				case 'date':
					aValue = new Date(a.dataset.date);
					bValue = new Date(b.dataset.date);
					break;
				case 'type':
					aValue = a.dataset.type;
					bValue = b.dataset.type;
					break;
				case 'reference':
					aValue = a.querySelector('.reference-number').textContent;
					bValue = b.querySelector('.reference-number').textContent;
					break;
				case 'warehouse':
					aValue = a.querySelector('.warehouse-info span')?.textContent || '';
					bValue = b.querySelector('.warehouse-info span')?.textContent || '';
					break;
				case 'quantity':
					aValue = parseInt(a.querySelector('.quantity').textContent.replace(/[+-]/g, ''));
					bValue = parseInt(b.querySelector('.quantity').textContent.replace(/[+-]/g, ''));
					break;
				case 'user':
					aValue = a.querySelector('.user-info span')?.textContent || '';
					bValue = b.querySelector('.user-info span')?.textContent || '';
					break;
				case 'notes':
					aValue = a.querySelector('.notes').textContent;
					bValue = b.querySelector('.notes').textContent;
					break;
				default:
					aValue = '';
					bValue = '';
			}

			if (this.currentSort.direction === 'asc') {
				return aValue > bValue ? 1 : -1;
			} else {
				return aValue < bValue ? 1 : -1;
			}
		});

		// Reappend sorted rows
		rows.forEach(row => tbody.appendChild(row));
	}

	updateSortIndicators() {
		// Remove all sort indicators
		document.querySelectorAll('.history-table th i').forEach(icon => {
			icon.className = 'fas fa-sort';
		});

		// Add sort indicator to current field
		const currentTh = document.querySelector(`.history-table th[data-sort="${this.currentSort.field}"]`);
		if (currentTh) {
			const icon = currentTh.querySelector('i');
			icon.className = this.currentSort.direction === 'asc'
				? 'fas fa-sort-up'
				: 'fas fa-sort-down';
		}
	}

	calculateStatistics() {
		const rows = document.querySelectorAll('#historyTableBody .transaction-row:not([style*="display: none"])');

		let totalIn = 0;
		let totalOut = 0;
		let totalTransfer = 0;

		rows.forEach(row => {
			const type = row.dataset.type;
			const quantityText = row.querySelector('.quantity').textContent;
			const quantity = parseInt(quantityText.replace(/[+-]/g, ''));

			switch (type) {
				case 'in':
					totalIn += quantity;
					break;
				case 'out':
					totalOut += quantity;
					break;
				case 'transfer':
					totalTransfer += quantity;
					break;
			}
		});

		document.getElementById('totalIn').textContent = totalIn.toLocaleString();
		document.getElementById('totalOut').textContent = totalOut.toLocaleString();
		document.getElementById('totalTransfer').textContent = totalTransfer.toLocaleString();
		document.getElementById('netChange').textContent = (totalIn - totalOut).toLocaleString();
	}

	updateResultsCount(visibleCount = null) {
		const totalRows = document.querySelectorAll('#historyTableBody .transaction-row').length;
		const showingCount = visibleCount !== null ? visibleCount : totalRows;

		const resultsCount = document.getElementById('resultsCount');

		if (this.currentFilters.search || this.hasActiveFilters()) {
			resultsCount.textContent = `Showing ${showingCount} of ${totalRows} transactions`;
		} else {
			resultsCount.textContent = `Showing all ${totalRows} transactions`;
		}

		// Update pagination info
		document.getElementById('showingFrom').textContent = '1';
		document.getElementById('showingTo').textContent = showingCount;
		document.getElementById('totalRecords').textContent = totalRows;
	}

	hasActiveFilters() {
		return this.currentFilters.dateRange !== 'all' ||
			this.currentFilters.transactionType !== 'all' ||
			this.currentFilters.warehouse !== 'all' ||
			this.currentFilters.user !== 'all';
	}

	async viewTransactionDetails(transactionId) {
		try {
			// Show loading state
			document.getElementById('transactionDetails').innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--color-brand-accent);"></i>
                    <p style="margin-top: 1rem; color: var(--color-text-muted);">Loading transaction details...</p>
                </div>
            `;

			// Open modal
			document.getElementById('transactionModal').style.display = 'block';

			// Simulate API call - replace with actual endpoint
			const response = await fetch(`/api/transactions/${transactionId}`);
			const transaction = await response.json();

			// Display transaction details
			this.displayTransactionDetails(transaction);

		} catch (error) {
			console.error('Failed to load transaction details:', error);
			document.getElementById('transactionDetails').innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--color-status-error);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                    <p style="margin-top: 1rem;">Failed to load transaction details</p>
                </div>
            `;
		}
	}

	displayTransactionDetails(transaction) {
		// const detailsHtml = {{ view('partials.transactions.details', transaction )}};
		// document.getElementById('transactionDetails').innerHTML = detailsHtml;
	}

	closeModal() {
		document.getElementById('transactionModal').style.display = 'none';
	}

	exportHistory() {
		// Get filtered data
		const rows = document.querySelectorAll('#historyTableBody .transaction-row:not([style*="display: none"])');
		const exportData = [];

		rows.forEach(row => {
			exportData.push({
				date: row.querySelector('.date').textContent + ' ' + row.querySelector('.time').textContent,
				type: row.dataset.type,
				reference: row.querySelector('.reference-number').textContent,
				warehouse: row.querySelector('.warehouse-info span')?.textContent || 'N/A',
				quantity: row.querySelector('.quantity').textContent,
				user: row.querySelector('.user-info span')?.textContent || 'System',
				notes: row.querySelector('.notes').textContent
			});
		});

		// Convert to CSV
		const headers = ['Date', 'Type', 'Reference', 'Warehouse', 'Quantity', 'User', 'Notes'];
		const csvContent = [
			headers.join(','),
			...exportData.map(row => [
				`"${row.date}"`,
				`"${row.type}"`,
				`"${row.reference}"`,
				`"${row.warehouse}"`,
				`"${row.quantity}"`,
				`"${row.user}"`,
				`"${row.notes}"`
			].join(','))
		].join('\n');

		// Create and download file
		const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
		const link = document.createElement('a');
		const url = URL.createObjectURL(blob);

		link.setAttribute('href', url);
		link.setAttribute('download', `product_history_${new Date().toISOString().split('T')[0]}.csv`);
		link.style.visibility = 'hidden';

		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	window.productHistory = new ProductHistory();
});

// Keyboard support for modal
document.addEventListener('keydown', (e) => {
	const modal = document.getElementById('transactionModal');
	if (modal.style.display === 'block' && e.key === 'Escape') {
		window.productHistory.closeModal();
	}
});
