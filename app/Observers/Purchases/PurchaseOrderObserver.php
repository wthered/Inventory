<?php

	namespace App\Observers\Purchases;

	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderHistory;
	use Carbon\Carbon;
	use Exception;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Log;

	class PurchaseOrderObserver {
		/**
		 * Καταγράφει τη δημιουργία μιας νέας εντολής αγοράς.
		 */
		public function created(PurchaseOrder $order): void {
			// Υποθέτουμε ότι έχετε ένα μοντέλο PurchaseOrderHistory.
			// Εάν δεν υπάρχει, μπορείτε να καταγράφετε στο ProductHistory με action 'purchase_order_created'.
			// Εδώ υποθέτουμε ότι υπάρχει ξεχωριστό ιστορικό για την παραγγελία.
			$this->logOrderAction($order, 'created', ['initial_status' => $order->status ?? 'pending']);
		}

		/**
		 * Βοηθητική μέθοδος για την καταγραφή του ιστορικού της εντολής αγοράς.
		 * (Υποθέτει ότι έχετε ένα μοντέλο PurchaseOrderHistory)
		 */
		protected function logOrderAction(PurchaseOrder $order, string $action, array $details): void {
			// *****************************************************************
			// ΣΗΜΕΙΩΣΗ: Πρέπει να αντικαταστήσετε το 'PurchaseOrderHistory::create'
			// με τη δική σας λογική καταγραφής, αν δεν έχετε αυτό το μοντέλο.
			// *****************************************************************
			try {
				// Add common metadata
				$details = array_merge($details, [
					'logged_at'  => Carbon::now()
						->toISOString(),
					'ip_address' => request()->ip(),
				]);

				PurchaseOrderHistory::query()
					->create([
						'purchase_order_id' => $order->id,
						'user_id'           => Auth::id() ?? null,
						// Allow null for system actions
						'action'            => $action,
						'details'           => $details,
						'timestamp'         => Carbon::now(),
					]);

			} catch (Exception $e) {
				// Fallback logging if history fails
				Log::error('Failed to log purchase order history', [
					'purchase_order_id' => $order->id,
					'action'            => $action,
					'error'             => $e->getMessage(),
				]);
			}
		}

		/**
		 * Καταγράφει την αλλαγή στην κατάσταση της εντολής αγοράς.
		 *
		 * @throws Exception
		 */
		public function updated(PurchaseOrder $order): void {
			$changes = [];

			// Status changes
			if ($order->isDirty('status')) {
				$changes['status'] = [
					'old' => $order->getOriginal('status'),
					'new' => $order->status,
				];

				// Trigger status-specific logic
				$this->handleStatusTransition($order);
			}

			// Monitor critical financial fields
			$financialFields = [
				'total_amount',
				'tax_amount',
				'discount_amount'
			];
			foreach ($financialFields as $field) {
				if ($order->isDirty($field)) {
					$changes[$field] = [
						'old' => $order->getOriginal($field),
						'new' => $order->$field,
					];
				}
			}

			// Monitor dates
			$dateFields = [
				'expected_delivery_date',
				'order_date'
			];
			foreach ($dateFields as $field) {
				if ($order->isDirty($field)) {
					$changes[$field] = [
						'old' => $order->getOriginal($field),
						'new' => $order->$field,
					];
				}
			}

			// Log all changes if any occurred
			if (!empty($changes)) {
				$this->logOrderAction($order, 'updated', ['changes' => $changes]);
			}
		}

		/**
		 * Handle specific status transitions with business logic
		 *
		 * @throws Exception
		 */
		protected function handleStatusTransition(PurchaseOrder $order): void {
			$oldStatus = $order->getOriginal('status');
			$newStatus = $order->status;

			switch ($newStatus) {
				case 'approved':
					$this->handleApproval($order, $oldStatus);
					break;

				case 'received':
					$this->handleReceipt($order, $oldStatus);
					break;

				case 'cancelled':
					$this->handleCancellation($order, $oldStatus);
					break;
			}
		}

		/**
		 * Handle order approval
		 */
		protected function handleApproval(PurchaseOrder $order, string $previousStatus): void {
			// Validate transition is allowed
			if (!in_array($previousStatus, [
				'pending',
				'draft'
			])) {
				Log::warning('Invalid status transition to approved', [
					'purchase_order_id' => $order->id,
					'from_status'       => $previousStatus,
				]);
			}

			// Additional approval logic here
			// - Notify suppliers
			// - Update inventory expectations
			// - Trigger financial processes
		}

		/**
		 * Handle order receipt/completion
		 */
		protected function handleReceipt(PurchaseOrder $order, string $previousStatus): void {
			// Validate all items are properly received
			$unreceivedItems = $order
				->items()
				->whereRaw('quantity_received < quantity_ordered')
				->count();

			if ($unreceivedItems > 0) {
				Log::warning('Purchase order marked as received with unreceived items', [
					'purchase_order_id' => $order->id,
					'unreceived_items'  => $unreceivedItems,
				]);
			}

			// Additional receipt logic
			// - Finalize inventory updates
			// - Trigger accounting processes
		}

		/**
		 * Handle order cancellation
		 *
		 * @throws Exception
		 */
		protected function handleCancellation(PurchaseOrder $order, string $previousStatus): void {
			// Prevent cancellation of already received orders
			if ($previousStatus === 'received') {
				// Throwing an exception here will halt the model's update process.
				throw new Exception('Cannot cancel a received purchase order');
			}

			// --- 1. Reverse any Pre-Allocations / Expected Inventory ---

			// In a system where stock from a pending PO is "soft-allocated"
			// to fulfill future Sales Orders, or recorded in an 'expected_receipts' table,
			// that allocation must be reversed.

			// Iterating over line items to find which products need to be de-allocated.
			foreach ($order->items as $item) {
				$productId = $item->product_id;
				$quantity  = $item->quantity;
				// Assuming $item also carries a warehouse_id if the expectation was warehouse-specific.
				$warehouseId = $item->warehouse_id ?? $order->warehouse_id;

				// 1.1 Reverse the expectation entry.
				// If you had a separate table to track incoming PO quantities (ExpectedReceipts):
				// ExpectedReceipts::where('purchase_order_item_id', $item->id)->delete();

				// 1.2 In the inventory table, update any fields used for future stock reservation.
				// Since your 'inventories' table has 'reserved_quantity', we must assume this
				// reservation is for sales orders, which cancellation doesn't affect directly.
				// We log the failure to fulfill the sales order instead.

				// This is a placeholder for complex logic: notify sales orders that the expected
				// supply source has been cancelled.
				// Event::dispatch(new PurchaseOrderSupplyCancelled($productId, $warehouseId, $quantity));

				// Log the de-allocation action
				Log::info("PO #{$order->id} Item {$item->id}: {$quantity} units of Product {$productId} de-allocated from future supply.");
			}

			// --- 2. Notify Stakeholders ---

			// Dispatch an event to handle asynchronous notifications (email, Slack, etc.)
			// This keeps the observer fast and clean.
			// Ensure you have an event listener set up for this event.
			// Event::dispatch(new PurchaseOrderCancelled($order, $previousStatus));

			// Or, for simple logging:
			Log::warning("Purchase Order #{$order->id} successfully cancelled. Previous status: {$previousStatus}.");

			// --- 3. Update Reporting ---

			// Flag the order as cancelled for reporting purposes (already done by model update).
			// The key here is to ensure the cancellation reason is stored if needed.
			// If you had a 'cancellation_reason' field on the order, you'd update it here.
		}

		/**
		 * Handle the PurchaseOrder "deleted" event.
		 */
		public function deleted(PurchaseOrder $order): void {
			$this->logOrderAction($order, 'deleted', [
				'final_status' => $order->status,
				'items_count'  => $order
					->items()
					->count(),
			]);
		}

		/**
		 * Handle the PurchaseOrder "restored" event.
		 */
		public function restored(PurchaseOrder $order): void {
			$this->logOrderAction($order, 'restored', [
				'status' => $order->status,
			]);
		}

		/**
		 * Handle the PurchaseOrder "force deleted" event.
		 */
		public function forceDeleted(PurchaseOrder $order): void {
			// Critical: Log force deletions for audit purposes
			Log::critical('Purchase order force deleted', [
				'purchase_order_id' => $order->id,
				'supplier_id'       => $order->supplier_id,
				'status'            => $order->status,
			]);
		}

		/**
		 * Handle the PurchaseOrder "creating" event.
		 */
		public function creating(PurchaseOrder $order): void {
			// Set default values
			if (empty($order->status)) {
				$order->status = 'draft';
			}

			if (empty($order->order_date)) {
				$order->order_date = now();
			}

			// Generate order number if not set
			if (empty($order->order_number)) {
				$order->order_number = $this->generateOrderNumber();
			}
		}

		/**
		 * Generate unique purchase order number
		 */
		protected function generateOrderNumber(): string {
			$prefix    = 'PO-' . date('Ymd-');
			$lastOrder = PurchaseOrder::query()
				->where('order_number', 'like', $prefix . '%')
				->orderBy('id', 'desc')
				->first();

			$sequence = $lastOrder ? (int) str_replace($prefix, '', $lastOrder->order_number) + 1 : 1;

			return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
		}
	}
