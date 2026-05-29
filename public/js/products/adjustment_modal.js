class AdjustmentModal {
	constructor() {
		this.modal = document.getElementById('adjustmentModal');
		this.adjustmentForm = document.getElementById('adjustmentForm');
		this.token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
		this.currentData = {
			product: null, warehouse: null, location: null, inventory: null, currentQuantity: 0, maximumQuantity: 0,
		};
		this.STOCK_LEVELS = {
			OUT_OF_STOCK: 0,
			CRITICAL: 0.1,    // 0-10%
			LOW: 0.25,        // 11-25%
			MEDIUM: 0.5,      // 26-50%
			GOOD: 0.75,       // 51-75%
			EXCELLENT: 1      // 76-100%
		};
		this.productData = {};

		this.init().then(() => {
			console.log('Adjustment Modal initialized.');
		});
	}

	async init() {
		await this.initEventListeners();
	}

	async initEventListeners() {
		// Close modal events
		this.modal.querySelector('.close').addEventListener('click', () => this.close());
		document.getElementById('adjustCancelBtn').addEventListener('click', () => this.close());

		// Click outside modal to close
		this.modal.addEventListener('click', (e) => {
			if (e.target === this.modal) {
				this.close();
			}
		});

		// Form submission
		this.adjustmentForm.addEventListener('submit', (e) => this.handleSubmit(e));

		// Adjustment button clicks
		document.addEventListener('click', (e) => {
			const adjustBtn = e.target.closest('.btn-adjust');
			if (adjustBtn) {
				this.open(adjustBtn);
			}
		});

		// Quantity input validation
		const quantityInput = document.getElementById('adjustQuantity');
		quantityInput.addEventListener('input', (e) => {
			this.validateQuantity(e.target);
			quantityInput.value = Math.min(e.target.value, e.target.max);
		});

		// Adjustment type change
		document.querySelectorAll('input[name="type"]').forEach(radio => {
			radio.addEventListener('change', () => this.updatePreview());
		});

		// Reason change
		document.getElementById('adjustReason').addEventListener('change', (e) => {
			this.updatePreview();
		});

		// Quantity input for preview
		quantityInput.addEventListener('input', () => this.updatePreview());
	}

	async open(button) {
		console.log("Opening AdjustmentModal...");

		// Extract data from button attributes
		this.currentData = {
			product: button.dataset.product,
			warehouse: button.dataset.warehouse,
			location: button.dataset.location,
			inventory: button.dataset.inventory,
			currentQuantity: parseInt(button.dataset.currentQty),
			maximumQuantity: parseInt(button.dataset.maxQuantity)
		};

		// Populate form fields
		await this.populateFormData().then(() => {
			// Update stock status indicator
			this.updateStockStatus();
		}).then( () => {
			this.updateAdjustmentReasons();
		});

		// Reset and update preview
		this.resetForm();
		this.updatePreview();

		// Show modal
		this.modal.style.display = 'block';
		document.body.style.overflow = 'hidden';
	}

	async updateAdjustmentReasons() {
		await this.postRequest(`/inventories/inventory/adjustment/reasons`).then(response => {
			document.querySelector('#adjustmentReason').innerHTML = response;
		});
	}

	close() {
		this.modal.style.display = 'none';
		document.body.style.overflow = 'auto';
		this.adjustmentForm.reset();
		this.currentData = {
			product: null, location: null, inventory: null, currentQuantity: 0, maxStockLevel: 0,
		};
		this.adjustmentForm.querySelector('.adjustment-btn').disabled = false;
		document.getElementById('errors').innerHTML = '&nbsp;';
	}

	async populateFormData() {
		await this.postRequest(`/products/${this.currentData.product}/information`).then((response) => {
			this.productData = response.product.product;
			let locationData = response.product.locations.find((location) => {
				return location.id === parseInt(this.currentData.location);
			});
			document.getElementById('adjustLocationName').value = locationData.name;
		});

		// Set hidden inputs
		document.getElementById('adjustProduct').value = this.currentData.product;
		document.getElementById('adjustLocation').value = this.currentData.location;

		// Set display fields
		document.getElementById('adjustProductName').value = this.productData.name; //this.currentData.productName;
		// document.getElementById('adjustLocationName').value = "Location Name"; // this.currentData.locationName;
		document.getElementById('adjustCurrentQty').value = this.currentData.currentQuantity;
	}

	getStockStatus(qty, maxQuantity) {
		const percentage = maxQuantity > 0 ? qty / maxQuantity : 0;

		if (qty === 0) {
			return {text: 'Out of Stock', className: 'out-of-stock'};
		} else if (percentage <= this.STOCK_LEVELS.CRITICAL) {
			return {text: 'Critical Stock', className: 'out-of-stock'};
		} else if (percentage <= this.STOCK_LEVELS.LOW) {
			return {text: 'Low Stock', className: 'low-stock'};
		} else if (percentage <= this.STOCK_LEVELS.MEDIUM) {
			return {text: 'Medium Stock', className: 'medium-stock'};
		} else if (percentage <= this.STOCK_LEVELS.GOOD) {
			return {text: 'Good Stock', className: 'high-stock'};
		} else {
			return {text: 'Excellent Stock', className: 'very-high-stock'};
		}
	}

	// Usage in your updateStockStatus method:
	updateStockStatus() {
		const stockStatus = document.getElementById('adjustStockStatus');
		stockStatus.className = 'available-quantity';

		const qty = this.currentData.currentQuantity;
		const maxQty = this.currentData.maximumQuantity || 100; // Default if not set

		const status = this.getStockStatus(qty, maxQty);

		stockStatus.textContent = status.text;
		stockStatus.classList.add(status.className);

		// Optional: Add percentage display
		const percentage = maxQty > 0 ? Math.round((qty / maxQty) * 100) : 0;
		stockStatus.title = `${qty} units (${percentage}% of ${maxQty} max)`;
	}

	resetForm() {
		// Reset to default values
		document.getElementById('adjustQuantity').value = 1;
		document.getElementById('adjustReason').value = '';
		document.getElementById('adjustNotes').value = '';

		// Set default type to increase
		document.querySelector('input[name="type"][value="increase"]').checked = true;

		// Enable submit button
		document.querySelector('.transfer-btn').disabled = false;
	}

	updatePreview() {
		const quantity = parseInt(document.getElementById('adjustQuantity').value) || 0;
		const type = document.querySelector('input[name="type"]:checked').value;
		const currentQty = this.currentData.currentQuantity;

		let newQuantity;
		let change;

		if (type === 'increase') {
			newQuantity = currentQty + quantity;
			change = quantity;
		} else {
			newQuantity = Math.max(0, currentQty - quantity);
			change = -quantity;
		}

		// Update preview displays
		document.getElementById('previewCurrent').textContent = currentQty;
		document.getElementById('previewNew').textContent = newQuantity;

		const changeElement = document.getElementById('previewChange');
		changeElement.textContent = change > 0 ? `+${change}` : change.toString();
		changeElement.style.color = change >= 0 ? '#28a745' : '#dc3545';

		// Validate quantity for decrease
		if (type === 'decrease' && quantity > currentQty) {
			document.getElementById('adjustQuantity').style.borderColor = '#dc3545';
			document.querySelector('.transfer-btn').disabled = true;
			changeElement.textContent = 'Insufficient stock!';
			changeElement.style.color = '#dc3545';
		} else {
			document.getElementById('adjustQuantity').style.borderColor = '';
			document.querySelector('.transfer-btn').disabled = false;
		}

		// Clear the error message from the previous validation
		document.getElementById('adjustReason').addEventListener('change', (e) => {
			const selectedReason = e.target.value;
			console.log('[Line 225] Adjustment Reason changed to',selectedReason);
			document.getElementById('errors').innerHTML = "&nbsp;";
		});
	}

	validateQuantity(input) {
		const value = parseInt(input.value);
		const min = parseInt(input.min) || 1;

		console.log("[Line 215] The value of " + value + " is greater than " + min + " and less than " + input.max);
		if (value < min) {
			input.value = min;
			this.showMessage(`Minimum quantity is ${min}`, 'warning');
		}

		this.updatePreview();
	}

	async handleSubmit(e) {
		e.preventDefault();

		const formData = {
			product: this.currentData.product,
			location: this.currentData.location,
			type: document.querySelector('input[name="type"]:checked').value,
			quantity: document.getElementById('adjustQuantity').value,
			reason: document.getElementById('adjustReason').value,
			notes: document.getElementById('adjustNotes').value
		};

		// Validation
		if (!this.validateAdjustment(formData)) {
			return;
		}

		// Show loading state
		const submitBtn = this.adjustmentForm.querySelector('.adjustment-btn');
		const originalText = submitBtn.innerHTML;
		submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
		submitBtn.disabled = true;

		try {
			await this.performAdjustment(formData, submitBtn).then(adjustmentResponse => {
				console.log("[Line 268]",adjustmentResponse);
				submitBtn.disabled = false;
				submitBtn.innerHTML = originalText;
			}).then(() => {
				this.showMessage('Adjustment completed successfully!', 'success');
				this.close();
			}).then( () => {
				// Refresh page or update UI
				setTimeout(() => {
					window.location.reload();
					console.log('Adjustment completed successfully! Reloading Window in line 276');
				}, 1000);
			});

		} catch (error) {
			this.showMessage(`Adjustment failed: ${error.message}`, 'error');
			console.error("[Line 282] Adjustment error:",error.message);

			// Re-enable button on error
			submitBtn.innerHTML = originalText;
			submitBtn.disabled = false;
		}
	}

	validateAdjustment(formData) {
		// Check if quantity is valid
		if (!formData.quantity || parseInt(formData.quantity) < 1) {
			this.showMessage('Please enter a valid quantity', 'error');
			return false;
		}

		// Check for decrease beyond available quantity
		if (formData.type === 'decrease' && parseInt(formData.quantity) > this.currentData.currentQuantity) {
			this.showMessage(`Cannot decrease more than current quantity (${this.currentData.currentQuantity})`, 'error');
			return false;
		}

		// Check if reason is selected
		if (!formData.reason) {
			this.showMessage('Please select a reason for adjustment', 'error');
			return false;
		}

		return true;
	}

	async performAdjustment(formData, button) {
		await this.postRequest(`/inventories/inventory/${this.currentData.inventory}/adjust`, formData).then(response => {
			console.log("[Line 315] Adjustment response:", response);
			button.disabled = false;
			button.innerHTML = "Line 317";
		}).then( (response) => {
			return response;
		});
		// button.disabled = false;
	}

	showMessage(message, type) {
		// Reuse the same message display logic from TransferModal
		const alertClass = type === 'error' ? 'alert-danger' : type === 'warning' ? 'alert-warning' : 'alert-success';

		// Create notification element (same style as TransferModal)
		const notification = document.createElement('div');
		notification.classList.add(alertClass);
		notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
        `;

		if (type === 'error') {
			notification.style.background = '#dc3545';
		} else if (type === 'warning') {
			notification.style.background = '#ffc107';
			notification.style.color = '#000';
		} else {
			notification.style.background = '#28a745';
		}

		notification.innerHTML = message;
		document.body.appendChild(notification);

		// Remove notification after 5 seconds
		setTimeout(() => {
			notification.remove();
		}, 5000);
	}

	/**
	 * Performs a POST request to the specified URL
	 * @param {string} url - The endpoint URL
	 * @param {object} data - The data to send in the request body
	 * @returns {Promise} Promise resolving to response data
	 */
	async postRequest(url, data = {}) {
		try {
			// Merge options with defaults
			const fetchOptions = {
				method: 'POST', headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': this.token,
				}, body: JSON.stringify(data),
			};

			const response = await fetch(url, fetchOptions);

			// Laravel CSRF expired (commonly 419)
			if (response.status === 419) {
				console.warn('CSRF token expired. Attempting automatic recovery...');

				// Instead of just throwing, you could trigger a page refresh
				// or call a function to fetch a new token.
				window.location.reload();
				return ;
			}

			if (!response.ok) {
				const text = await response.text().catch(() => '');
				this.showMessage(`Error ${response.status}: ${text}`, 'error');
				const errorsDiv = document.getElementById("errors");
				const errorList = document.createElement("ul");

				const failure = JSON.parse(text);
				console.info("[Line 384] Failure is ",failure);
				for (const [tag, errors] of Object.entries(failure['errors'])) {
					errors.forEach(error => {
						const errorItem = document.createElement("li");
						errorItem.textContent = error;
						errorItem.className = "alert alert-warning";
						errorList.appendChild(errorItem);
					});
				}

				errorList.className = "error-list";
				errorsDiv.innerHTML = "";
				errorsDiv.appendChild(errorList);

				return undefined; // Stop execution without throwing
			}

			// 204 No Content => return undefined
			if (response.status === 204) {
				return undefined;
			}

			// Parse according to Content-Type
			const contentType = response.headers.get('Content-Type') || '';

			if (contentType.includes('application/json')) {
				return await response.json();
			}

			// If server responded as text or unknown content-type, return text
			return await response.text();

		} catch (error) {
			console.error('POST request failed:', error);
			throw error;
		}
	}
}

// Initialize the modal when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	console.log('Initializing Adjustment Modal...');
	window.adjustmentModal = new AdjustmentModal();
	console.log('Adjustment Modal initialized successfully');
});

// Keyboard support (Escape to close)
document.addEventListener('keydown', (e) => {
	const modal = document.getElementById('adjustmentModal');
	if (modal && modal.style.display === 'block' && e.key === 'Escape') {
		modal.style.display = 'none';
		document.body.style.overflow = 'auto';
	}
});
