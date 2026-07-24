/**
 * TransferModal Class
 * Handles stock transfer between warehouse locations
 */
class TransferModal {
    constructor() {
        this.modal = document.getElementById('transferModal');
        this.form = document.getElementById('transferForm');
        this.closeBtn = this.modal.querySelector('.close');
        this.cancelBtn = document.getElementById('cancelTransfer');
        this.confirmBtn = document.getElementById('confirmTransfer');

        // Form inputs
        this.productInfo = document.getElementById('productInfo');
        this.transferQuantity = document.getElementById('transferQuantity');
        this.availableQuantity = document.getElementById('availableQuantity');
        this.transferNotes = document.getElementById('transferNotes');

        // Source location dropdowns
        this.sourceWarehouse = document.getElementById('sourceWarehouse');
        this.sourceZone = document.getElementById('sourceZone');
        this.sourceAisle = document.getElementById('sourceAisle');
        this.sourceRack = document.getElementById('sourceRack');
        this.sourceShelf = document.getElementById('sourceShelf');
        this.sourceBin = document.getElementById('sourceBin');

        // Destination location dropdowns
        this.destinationWarehouse = document.getElementById('destinationWarehouse');
        this.destinationZone = document.getElementById('destinationZone');
        this.destinationAisle = document.getElementById('destinationAisle');
        this.destinationRack = document.getElementById('destinationRack');
        this.destinationShelf = document.getElementById('destinationShelf');
        this.destinationBin = document.getElementById('destinationBin');

        this.currentProduct = {};
        this.productInventory = null;
        this.sourceDropdowns = {};
        this.destinationRequestData = {
            product: null, warehouse: null,
        }
        this.token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        this.init();
    }

    /**
     * Initialize event listeners
     */
    init() {
        // Transfer button clicks
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.transfer-btn');
            if (btn) {
                e.preventDefault();
                this.open(btn).then(r => {
                    console.log("[Line 56] Just opened the Modal and got response:", r);
                });
            }
        });

        // Close events
        this.closeBtn.addEventListener('click', () => this.close());
        this.cancelBtn.addEventListener('click', () => this.close());
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'block') {
                this.close();
            }
        });

        // Source cascading dropdowns
        this.sourceWarehouse.addEventListener('change', () => this.fetchSourceLocations());
        this.sourceZone.addEventListener('change', () => this.loadSourceAisles());
        this.sourceAisle.addEventListener('change', () => this.loadSourceRacks());
        this.sourceRack.addEventListener('change', () => this.loadSourceShelves());
        this.sourceShelf.addEventListener('change', () => this.loadSourceBins());
        this.sourceBin.addEventListener('change', () => this.updateAvailableQuantity());

        // Destination cascading dropdowns
        this.destinationWarehouse.addEventListener('change', () => this.loadDestinationZones());
        this.destinationZone.addEventListener('change', () => this.loadDestinationAisles());
        this.destinationAisle.addEventListener('change', () => this.loadDestinationRacks());
        this.destinationRack.addEventListener('change', () => this.loadDestinationShelves());
        this.destinationShelf.addEventListener('change', () => this.loadDestinationBins());

        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Quantity validation
        this.transferQuantity.addEventListener('input', () => this.validateQuantity());
    }

    /**
     * Open modal
     */
    async open(button) {
        this.currentProduct = {
            productId: parseInt(button.dataset.product),
            warehouseId: parseInt(button.dataset.warehouse),
            locationId: parseInt(button.dataset.location),
        };

        this.form.reset();
        this.resetAllDropdowns();
        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        try {
            await this.loadWarehouses().then(warehouses => {
                this.productInventory = warehouses.inventory[0];
                console.log("[Line 114]", this.productInventory);
                this.enableTargetDropdowns()
            });
            await this.loadProductData().then(response => {
                this.productInfo.value = `${response.name} (SKU: ${response['sku']})`;
            });

            if (this.currentProduct.warehouseId && this.currentProduct.locationId) {
                this.sourceWarehouse.value = this.currentProduct.warehouseId;
                await this.fetchSourceLocations().then(response => {
                    const availableQuantity = response['inventory'].find(inventory => inventory.warehouse === this.currentProduct.warehouseId && inventory.location === this.currentProduct.locationId);
                    console.log("[Line 124]", response);
                    document.getElementById("sourceZone").innerHTML = response['zone'];
                    this.availableQuantity.textContent = availableQuantity.available;
                    this.transferQuantity.value = availableQuantity.available;
                    this.transferQuantity.max = availableQuantity.available;
                });
            }
        } catch (error) {
            console.error('Error opening modal:', error);
            this.showError('Failed to load transfer data');
        }
        return this.currentProduct;
    }

    /**
     * Close modal
     */
    close() {
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.form.reset();
        this.resetAllDropdowns();
        this.currentProduct = null;
    }

    /**
     * Reset all dropdowns
     */
    resetAllDropdowns() {
        this.resetDropdown(this.sourceZone, 'Select Zone');
        this.resetDropdown(this.sourceAisle, 'Select Aisle');
        this.resetDropdown(this.sourceRack, 'Select Rack');
        this.resetDropdown(this.sourceShelf, 'Select Shelf');
        this.resetDropdown(this.sourceBin, 'Select Bin');

        this.resetDropdown(this.destinationZone, 'Select Zone');
        this.resetDropdown(this.destinationAisle, 'Select Aisle');
        this.resetDropdown(this.destinationRack, 'Select Rack');
        this.resetDropdown(this.destinationShelf, 'Select Shelf');
        this.resetDropdown(this.destinationBin, 'Select Bin');

        this.availableQuantity.textContent = '0';
    }

    enableTargetDropdowns() {
        this.enableDropdown(this.destinationZone, "", 'Select Zone');
        this.enableDropdown(this.destinationAisle, "", 'Select Aisle');
        this.enableDropdown(this.destinationRack, "", 'Select Rack');
        this.enableDropdown(this.destinationShelf, "", 'Select Shelf');
        this.enableDropdown(this.destinationBin, "", 'Select Bin');
    }

    /**
     * Reset single dropdown
     */
    resetDropdown(dropdown, placeholder) {
        dropdown.innerHTML = `<option value="">${placeholder}</option>`;
        dropdown.disabled = true;
    }

    enableDropdown(dropdown, value, placeholder) {
        dropdown.innerHTML = `<option value="${value}">${placeholder}</option>`;
    }

    /**
     * Fetch helper with error handling
     */
    async fetchAPI(url, postData = null, options = {}) {
        const defaultOptions = {
            method: 'POST', headers: {
                'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.token
            }
        };

        // Only add body if postData exists
        if (postData !== null) {
            defaultOptions.body = JSON.stringify(postData);
        }

        const response = await fetch(url, {...defaultOptions, ...options});

        if (!response.ok) {
            if (response.status === 419) {
                // window.location.reload();
                console.log("Window will be reloaded in this use case");
            }
            const error = await response.json();
            // throw new Error(error.message || 'Request failed');
            throw new Error(JSON.stringify(error));
        }

        return await response.json();
    }

    /**
     * Load product data
     */
    async loadProductData() {
        const data = await this.fetchAPI(`/products/${this.currentProduct.productId}/information`);
        console.info(data);
        return data.product;
    }

    /**
     * Load warehouses
     */
    async loadWarehouses() {
        const warehouses = await this.fetchAPI('/warehouses', {
            product_id: this.currentProduct.productId,
            warehouse_id: this.currentProduct.warehouseId,
            location_id: this.currentProduct.locationId
        });

        console.log(warehouses);

        if (warehouses) {
            this.sourceWarehouse.innerHTML = '<option value="" disabled>Select Source Warehouse</option>';
            this.destinationWarehouse.innerHTML = '<option value="">Select Destination Warehouse</option>';

            warehouses.source.forEach(location => {
                const sourceOption = new Option(location.text, location.value);
                sourceOption.selected = location.selected;
                this.sourceWarehouse.add(sourceOption);
            });

            warehouses.target.forEach(warehouse => {
                const destOption = new Option(warehouse.name, warehouse.id);
                this.destinationWarehouse.add(destOption);
            });

            this.productInventory = warehouses['inventory'];
            return warehouses;
        }
        return {source: [], target: []};
    }

    /**
     * Pre-select source location
     */
    async fetchSourceLocations() {
        const location = {
            warehouse: this.currentProduct.warehouseId, location: this.currentProduct.locationId,
        }
        try {
            return await this.fetchAPI(`/products/${this.currentProduct.productId}/locations`, location).then(response => {
                this.sourceDropdowns = response.inventory[0];
                // console.info("Response:", response.inventory[0]);
                this.sourceWarehouse.innerHTML = this.sourceDropdowns.warehouse;
                this.sourceZone.innerHTML = this.sourceDropdowns.zone;
                this.sourceAisle.innerHTML = this.sourceDropdowns.aisle;
                this.sourceRack.innerHTML = this.sourceDropdowns.rack;
                this.sourceShelf.innerHTML = this.sourceDropdowns.shelf;
                this.sourceBin.innerHTML = this.sourceDropdowns.bin;
                return response;
            });
        } catch (error) {
            // Use standard try/catch to handle network failures or API errors
            console.error("Failed to fetch source locations:", error);
            // Throw or return a default state to prevent app failure
            throw error;
        }
    }

    /******************************************************************************************
     * Load source zones after I change source Warehouse dropdown
     * Deleted as source Warehouse never changes as of now
     *****************************************************************************************/
    // async loadSourceZones() {
    // 	console.info('[Line 262] Selected Inventory:', this.sourceWarehouse.value);
    // 	this.productInventory = this.sourceWarehouse.value;
    // 	if (!this.productInventory) {
    // 		this.resetDropdown(this.sourceZone, 'Select Zone');
    // 		return;
    // 	}
    //
    // 	const data = await this.fetchAPI(`/products/${this.currentProduct.productId}/inventory`, {inventory: this.productInventory});
    // 	console.log("[Line 267] Selected Inventory:", data);
    //
    // 	this.sourceZone.innerHTML = '<option value="">Select Zone</option>';
    // 	if (data.success && data.inventory.zone) {
    // 		data.inventory.zone.forEach(zone => {
    // 			console.log("[Line 270]", zone);
    // 			this.sourceZone.add(new Option(`Zone ${zone}`, zone));
    // 		});
    // 		this.sourceZone.disabled = false;
    // 	}
    //
    // 	this.resetDropdown(this.sourceAisle, 'Select Aisle');
    // 	this.resetDropdown(this.sourceRack, 'Select Rack');
    // 	this.resetDropdown(this.sourceShelf, 'Select Shelf');
    // 	this.resetDropdown(this.sourceBin, 'Select Bin');
    //
    // 	this.sourceZone.innerHTML = data.inventory.zone;
    // 	this.sourceAisle.innerHTML = data.inventory.aisle;
    // 	this.sourceRack.innerHTML = data.inventory.rack;
    // 	this.sourceShelf.innerHTML = data.inventory.shelf;
    // 	this.sourceBin.innerHTML = data.inventory.bin;
    //
    // 	return this.updateAvailableQuantity();
    // }

    /**
     * Load source aisles
     */
    async loadSourceAisles() {
        const warehouseId = this.sourceWarehouse.value;
        const zone = this.sourceZone.value;

        if (!warehouseId || !zone) {
            this.resetDropdown(this.sourceAisle, 'Select Aisle');
            return;
        }

        const data = await this.fetchAPI(`/warehouses/warehouse/${warehouseId}/locations/aisles?zone=${zone}`);

        this.sourceAisle.innerHTML = '<option value="">Select Aisle</option>';
        if (data.success && data.aisles) {
            data.aisles.forEach(aisle => {
                this.sourceAisle.add(new Option(`Aisle ${aisle}`, aisle));
            });
            this.sourceAisle.disabled = false;
        }

        this.resetDropdown(this.sourceRack, 'Select Rack');
        this.resetDropdown(this.sourceShelf, 'Select Shelf');
        this.resetDropdown(this.sourceBin, 'Select Bin');
    }

    /**
     * Load source racks
     */
    async loadSourceRacks() {
        const warehouseId = this.sourceWarehouse.value;
        const zone = this.sourceZone.value;
        const aisle = this.sourceAisle.value;

        if (!warehouseId || !zone || !aisle) {
            this.resetDropdown(this.sourceRack, 'Select Rack');
            return;
        }

        const location = await this.fetchAPI(`/warehouses/warehouse/${warehouseId}/locations/racks?zone=${zone}&aisle=${aisle}`);

        this.sourceRack.innerHTML = '<option value="">Select Rack</option>';
        if (location.success && location.rack) {
            location.rack.forEach(rack => {
                this.sourceRack.add(new Option("Rack " + rack.text, rack.value));
            });
            this.sourceRack.disabled = false;
        }

        this.resetDropdown(this.sourceShelf, 'Select Shelf');
        this.resetDropdown(this.sourceBin, 'Select Bin');
    }

    /**
     * Load source shelves
     */
    async loadSourceShelves() {
        const warehouseId = this.sourceWarehouse.value;
        const zone = this.sourceZone.value;
        const aisle = this.sourceAisle.value;
        const rack = this.sourceRack.value;

        if (!warehouseId || !zone || !aisle || !rack) {
            this.resetDropdown(this.sourceShelf, 'Select Shelf');
            return;
        }

        const data = await this.fetchAPI(`/warehouses/${warehouseId}/locations/shelves?zone=${zone}&aisle=${aisle}&rack=${rack}`);

        this.sourceShelf.innerHTML = '<option value="">Select Shelf</option>';
        if (data.success && data.shelves) {
            data.shelves.forEach(shelf => {
                this.sourceShelf.add(new Option(shelf, shelf));
            });
            this.sourceShelf.disabled = false;
        }

        this.resetDropdown(this.sourceBin, 'Select Bin');
    }

    /**
     * Load source bins
     */
    async loadSourceBins() {
        const warehouseId = this.sourceWarehouse.value;
        const zone = this.sourceZone.value;
        const aisle = this.sourceAisle.value;
        const rack = this.sourceRack.value;
        const shelf = this.sourceShelf.value;

        if (!warehouseId || !zone || !aisle || !rack || !shelf) {
            this.resetDropdown(this.sourceBin, 'Select Bin');
            return;
        }

        const data = await this.fetchAPI(`/warehouses/${warehouseId}/locations/bins?zone=${zone}&aisle=${aisle}&rack=${rack}&shelf=${shelf}`);

        this.sourceBin.innerHTML = '<option value="">Select Bin</option>';
        if (data.success && data.bins) {
            data.bins.forEach(bin => {
                this.sourceBin.add(new Option(bin, bin));
            });
            this.sourceBin.disabled = false;
        }
    }

    /**
     * Update available quantity
     */
    async updateAvailableQuantity() {

        if (!this.currentProduct.productId || !this.sourceBin.value) {
            this.availableQuantity.textContent = '0';
            this.transferQuantity.max = 0;
            return;
        }

        const productInventory = await this.fetchAPI(`/products/${this.currentProduct.productId}/inventory?location=${this.currentProduct.locationId}`);
        console.log("[Line 438] Product Inventory", productInventory);

        const available = productInventory['inventory'].available_quantity || 0;
        if (productInventory.success && productInventory['inventory']) {
            this.availableQuantity.textContent = available;
            this.transferQuantity.max = available;

            if (available === 0) {
                this.showWarning('No stock available at this location');
            }
        }
        return available;
    }

    /**
     * Load destination zones (mirrors source logic)
     */
    async loadDestinationZones() {
        const warehouseId = this.destinationWarehouse.value;
        if (!warehouseId) {
            this.resetDropdown(this.destinationZone, 'Select Zone');
            return;
        }

        this.destinationRequestData.warehouse = this.currentProduct.warehouseId;
        this.destinationRequestData.product = this.currentProduct.productId;
        const destinations = await this.fetchAPI(`/warehouses/${warehouseId}/locations/zones`, this.destinationRequestData);

        if (destinations.success && destinations['zone']) {
            this.destinationZone.innerHTML = '<option value="" selected>Select Zone</option>';
            destinations['zone'].forEach(zone => {
                this.destinationZone.add(new Option(zone.text, zone.value));
            });
            this.destinationZone.disabled = false;
        }

        this.resetDropdown(this.destinationAisle, 'Select Aisle');
        this.resetDropdown(this.destinationRack, 'Select Rack');
        this.resetDropdown(this.destinationShelf, 'Select Shelf');
        this.resetDropdown(this.destinationBin, 'Select Bin');
    }

    async loadDestinationAisles() {
        const warehouseId = this.destinationWarehouse.value;
        const zone = this.destinationZone.value;

        this.destinationRequestData.warehouse = warehouseId;
        this.destinationRequestData.product = this.currentProduct.productId;

        if (!warehouseId || !zone) {
            this.resetDropdown(this.destinationAisle, 'Select Aisle');
            return;
        }

        const response = await this.fetchAPI(`/warehouses/${warehouseId}/locations/aisles?zone=${zone}`, this.destinationRequestData);

        // 1. Καθαρίζουμε το select και το απενεργοποιούμε αρχικά
        this.destinationAisle.innerHTML = '<option value="">Select Aisle</option>';
        this.destinationAisle.disabled = true;

        // 2. Ελέγχουμε αν η απάντηση είναι επιτυχής
        if (response && response.success) {

            // Αν το fetchAPI επιστρέφει ήδη τα data, χρησιμοποιείς το response.aisles.
            // Αν επιστρέφει το raw Response, κάνεις πρώτα: const data = await response.json(); και μετά data.aisles
            const aisles = response.aisles || response.data || response['aisles'];

            // 3. Ενεργοποιούμε το select μόνο αν βρέθηκαν aisles
            this.destinationAisle.disabled = false;
            this.destinationAisle.innerHTML = '<option value="">Select Aisle</option>' + aisles.options;
        }

        this.resetDropdown(this.destinationRack, 'Select Rack');
        this.resetDropdown(this.destinationShelf, 'Select Shelf');
        this.resetDropdown(this.destinationBin, 'Select Bin');
    }

    async loadDestinationRacks() {
        const warehouseId = this.destinationWarehouse.value;
        const zone = this.destinationZone.value;
        const aisle = this.destinationAisle.value;

        if (!warehouseId || !zone || !aisle) {
            this.resetDropdown(this.destinationRack, 'Select Rack');
            return;
        }

        this.destinationRequestData.product = this.destinationRequestData.product ?? this.currentProduct.productId;

        const locations = await this.fetchAPI(`/warehouses/${warehouseId}/locations/racks?zone=${zone}&aisle=${aisle}`, this.destinationRequestData);

        this.destinationRack.innerHTML = '<option value="">Select Rack</option>';
        if (locations.success && locations.options.length > 0) {
            locations.options.forEach(rack => {
                this.destinationRack.add(new Option("Rack " + rack.text, rack.value));
            });
            this.destinationRack.disabled = false;
        }

        this.resetDropdown(this.destinationShelf, 'Select Shelf');
        this.resetDropdown(this.destinationBin, 'Select Bin');
    }

    async loadDestinationShelves() {
        const warehouseId = this.destinationWarehouse.value;
        const zone = this.destinationZone.value;
        const aisle = this.destinationAisle.value;
        const rack = this.destinationRack.value;

        if (!warehouseId || !zone || !aisle || !rack) {
            this.resetDropdown(this.destinationShelf, 'Select Shelf');
            return;
        }

        const locations = await this.fetchAPI(`/warehouses/${warehouseId}/locations/shelves?zone=${zone}&aisle=${aisle}&rack=${rack}`, this.destinationRequestData);

        this.destinationShelf.innerHTML = '<option value="">Select Shelf</option>';
        if (locations.success && locations.options) {
            locations.options.forEach(shelf => {
                this.destinationShelf.add(new Option("Shelf " + shelf.text, shelf.value));
            });
            this.destinationShelf.disabled = false;
        }

        this.resetDropdown(this.destinationBin, 'Select Bin');
    }

    async loadDestinationBins() {
        const warehouseId = this.destinationWarehouse.value;
        const zone = this.destinationZone.value;
        const aisle = this.destinationAisle.value;
        const rack = this.destinationRack.value;
        const shelf = this.destinationShelf.value;

        if (!warehouseId || !zone || !aisle || !rack || !shelf) {
            this.resetDropdown(this.destinationBin, 'Select Bin');
            return;
        }

        const response = await this.fetchAPI(`/warehouses/${warehouseId}/locations/bins?zone=${zone}&aisle=${aisle}&rack=${rack}&shelf=${shelf}`, this.destinationRequestData);

        this.destinationBin.innerHTML = '<option value="">Select Bin</option>';
        if (response.success && response.options.length > 0) {
            response.options.forEach(bin => {
                this.destinationBin.add(new Option("Bin " + bin.text, bin.value));
            });
            this.destinationBin.disabled = false;
        }
    }

    /**
     * Validate quantity
     */
    validateQuantity() {
        const max = parseInt(this.transferQuantity.max) || 0;
        const value = parseInt(this.transferQuantity.value) || 0;

        if (value > max) {
            this.transferQuantity.value = max;
            this.showWarning(`Maximum available quantity is ${max}`);
        }

        if (value < 1) {
            this.transferQuantity.value = 1;
        }
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();

        if (!this.form.checkValidity()) {
            this.form.reportValidity();
            return;
        }

        // Validate same location
        if (this.sourceWarehouse.value === this.destinationWarehouse.value &&
            this.sourceZone.value === this.destinationZone.value &&
            this.sourceAisle.value === this.destinationAisle.value &&
            this.sourceRack.value === this.destinationRack.value &&
            this.sourceShelf.value === this.destinationShelf.value &&
            this.sourceBin.value === this.destinationBin.value
        ) {
            this.showError('Source and destination cannot be the same');
            return;
        }

        const transferData = {
            product_id: this.currentProduct.productId,
            sourceLocation: {
                warehouse: this.currentProduct.warehouseId,
                zone: this.sourceZone.value,
                aisle: this.sourceAisle.value,
                rack: this.sourceRack.value,
                shelf: this.sourceShelf.value,
                bin: this.sourceBin.value,
            },
            targetLocation: {
                warehouse: this.destinationWarehouse.value,
                zone: this.destinationZone.value,
                aisle: this.destinationAisle.value,
                rack: this.destinationRack.value,
                shelf: this.destinationShelf.value,
                bin: this.destinationBin.value,
            },
            location_id: this.currentProduct.locationId,
            quantity: this.transferQuantity.value,
            notes: this.transferNotes.value
        };

        this.confirmBtn.disabled = true;
        this.confirmBtn.textContent = 'Processing Transfer...';

        try {
            const data = await this.fetchAPI(`/inventories/${this.productInventory.id}/transfer`, transferData).then(response => {
                console.log("[Line 665] Transferring Response:", response);
                return response;
            });

            if (data.success) {
                this.showSuccess('Stock transfer created successfully!');
                setTimeout(() => {
                    this.close();
                    window.location.reload();
                }, 1500);
            } else {
                this.showError(data.message || 'Failed to create transfer');
            }
        } catch (error) {
            console.error('Transfer error:', error.message);

            try {
                // Προσπαθούμε να κάνουμε parse το error μήπως είναι το JSON από την fetchAPI
                const laravelError = JSON.parse(error.message);
                if (laravelError.message) {
                    // Εμφανίζει το "The sourceLocation.shelf must not be greater than 5 for rack 5"
                    this.showError(laravelError.message);
                    return;
                }
            } catch (e) {
                // Αν δεν είναι JSON (π.χ. network crash), συνεχίζει στο default alert
            }

            this.showError(error.message || 'An error occurred');
        } finally {
            this.confirmBtn.disabled = false;
            this.confirmBtn.textContent = 'Transfer Products';
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        alert(message); // Replace with your notification system
    }

    /**
     * Show error message
     */
    showError(message) {
        alert('Error: ' + message); // Replace with your notification system
    }

    /**
     * Show warning message
     */
    showWarning(message) {
        console.warn(message); // Replace with your notification system
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.transferModal = new TransferModal();
    });
} else {
    window.transferModal = new TransferModal();
}
