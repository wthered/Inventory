<?php

	namespace App\Enums\Purchases;

	enum PurchaseOrderEvent: string {
		// 1. Αλλαγές Κατάστασης (Status Changes)
		case CREATED          = 'Purchase Order Created';
		case SUBMITTED        = 'Order Submitted for Approval';
		case APPROVED         = 'Purchase Order Approved';
		case SENT_TO_SUPPLIER = 'Sent to Supplier';
		case CANCELLED        = 'Order Cancelled';

		// 2. Διαχείριση Προϊόντων / Γραμμών (Item Modifications)
		case ITEM_ADDED            = 'Item Added to Order';
		case ITEM_QUANTITY_UPDATED = 'Item Quantity Updated';
		case ITEM_REMOVED          = 'Item Removed';

		// 3. Διαδικασία Παραλαβής στην Αποθήκη (Receiving & Put-away)
		case PARTIAL_RECEIPT = 'Partial Shipment Received';
		case FULL_RECEIPT    = 'Order Fully Received';
		case BATCH_ASSIGNED  = 'Batch Number Assigned';
		case ITEMS_PLACED    = 'Items Placed in Location';

		// 4. Οικονομικά & Γενικά Στοιχεία
		case TOTALS_RECALCULATED = 'Financial Totals Recalculated';
		case NOTES_UPDATED       = 'Internal Notes Updated';

		/**
		 * Επιστρέφει μια δυναμική ή στατική περιγραφή για το κάθε event.
		 * Περνάμε προαιρετικά $arguments για τα δυναμικά strings.
		 */
		public function description(array $arguments = []): string {
			return match ($this) {
				// Στατικά descriptions
				self::CREATED => 'The purchase order was initialized as a draft in the system.',
				self::SUBMITTED => 'The order was locked and sent to procurement managers for approval workflow.',
				self::FULL_RECEIPT => 'All items from this purchase order have been fully received and verified at the warehouse.',

				// Δυναμικά descriptions (χρήση %s, %d κλπ με τη sprintf)
				self::APPROVED => sprintf('The purchase order was approved by manager (User ID: %s).', $arguments['user_id'] ?? 'Unknown'),

				self::ITEM_ADDED => sprintf('Product "%s" (Qty: %d) was appended to the purchase order lines.', $arguments['product_name'] ?? 'Unknown', $arguments['quantity'] ?? 0),

				self::ITEM_QUANTITY_UPDATED => sprintf('Line item "%s" quantity was modified from %d to %d.', $arguments['product_name'] ?? 'Unknown', $arguments['old_qty'] ?? 0, $arguments['new_qty'] ?? 0),

				self::BATCH_ASSIGNED => sprintf('Batch number "%s" was successfully assigned to product "%s".', $arguments['batch_number'] ?? 'N/A', $arguments['product_name'] ?? 'Unknown'),

				// Fallback για όσα δεν έχουν οριστεί ακόμα
				default => 'No additional description provided for this event.',
			};
		}
	}