<?php

	namespace App\Observers\Purchases;

	use App\Enums\Purchases\PurchaseOrderEvent;
	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\Purchases\PurchaseOrderHistory;
	use Carbon\Carbon;
	use Exception;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Log;

	class PurchaseOrderObserver {

		public function created(PurchaseOrder $order): void {
			$this->logOrderAction($order, 'created', ['initial_status' => $order->status_id->value ?? 'pending']);
		}

		protected function logOrderAction(PurchaseOrder $order, string $action, array $details): void {
			try {
				$details = array_merge($details, [
					'logged_at'  => Carbon::now()->toISOString(),
					'ip_address' => request()->ip(),
				]);

				// Παίρνουμε ένα τυχαίο Enum case
				$eventEnum = fake()->randomElement(PurchaseOrderEvent::cases());

				PurchaseOrderHistory::query()->create([
					'purchase_order_id' => $order->id,
					'user_id'           => Auth::id() ?? null,
					'action'            => $action,
					'event'             => $eventEnum->value, // String value για τη βάση
					'details'           => $details,
					'description'       => $eventEnum->description(), // Κλήση της μεθόδου πάνω στο Enum Object
				]);

			} catch (Exception $e) {
				Log::error('Failed to log purchase order history: ' . $e->getMessage(), [
					'purchase_order_id' => $order->id,
				]);
			}
		}

		public function updated(PurchaseOrder $order): void {
			$changes = [];

			if ($order->isDirty('status_id')) {
				$changes['status_id'] = [
					'old' => $order->getOriginal('status_id'),
					'new' => $order->status_id,
				];
				$this->handleStatusTransition($order);
			}

			$financialFields = ['grand_total', 'tax_amount', 'discount_amount', 'subtotal'];
			foreach ($financialFields as $field) {
				if ($order->isDirty($field)) {
					$changes[$field] = [
						'old' => $order->getOriginal($field),
						'new' => $order->$field,
					];
				}
			}

			$dateFields = ['expected_date', 'order_date'];
			foreach ($dateFields as $field) {
				if ($order->isDirty($field)) {
					$changes[$field] = [
						'old' => $order->getOriginal($field),
						'new' => $order->$field,
					];
				}
			}

			if (!empty($changes)) {
				$this->logOrderAction($order, 'updated', ['changes' => $changes]);
			}
		}

		protected function handleStatusTransition(PurchaseOrder $order): void {
			$oldStatus = $order->getOriginal('status_id');
			$newStatus = $order->status_id;

			// Έλεγχος αν είναι αντικείμενα Enum ή integers και λήψη της raw τιμής
			$newRaw = $newStatus instanceof PurchaseOrderStatus ? $newStatus->value : $newStatus;

			switch ($newRaw) {
				case 2: // Assuming 2 = Approved/Partially Received βάσει Seeder
					break;
				case 3: // Received
					$this->handleReceipt($order);
					break;
				case 4: // Cancelled
					$this->handleCancellation($order);
					break;
			}
		}

		protected function handleReceipt(PurchaseOrder $order): void {
			$unreceivedItems = $order->items()
				->whereRaw('quantity_received < quantity_ordered')
				->count();

			if ($unreceivedItems > 0) {
				Log::warning("PO #{$order->id} marked as received but has unreceived items.");
			}
		}

		protected function handleCancellation(PurchaseOrder $order): void {
			$oldStatus = $order->getOriginal('status_id');
			$oldRaw = $oldStatus instanceof PurchaseOrderStatus ? $oldStatus->value : $oldStatus;

			if ($oldRaw === 3) {
				throw new Exception('Cannot cancel a received purchase order');
			}

			foreach ($order->items as $item) {
				Log::info("PO #{$order->id} Item {$item->id}: {$item->quantity_ordered} units de-allocated.");
			}
		}

		public function deleted(PurchaseOrder $order): void {
			$this->logOrderAction($order, 'deleted', [
				'final_status' => $order->status_id,
				'items_count'  => $order->items()->count(),
			]);
		}

		public function restored(PurchaseOrder $order): void {
			$this->logOrderAction($order, 'restored', ['status_id' => $order->status_id]);
		}

		public function forceDeleted(PurchaseOrder $order): void {
			Log::critical("Purchase order #{$order->id} force deleted.");
		}

		public function creating(PurchaseOrder $order): void {
			if (empty($order->status_id)) {
				$order->status_id = PurchaseOrderStatus::DRAFT;
			}

			if (empty($order->order_date)) {
				$order->order_date = now()->subDays(mt_rand(1, 7));
			}

			if (empty($order->po_number)) {
				$order->po_number = $this->generateOrderNumber();
			}
		}

		protected function generateOrderNumber(): string {
			$prefix = 'PO-' . date('Ymd-');
			$lastOrder = PurchaseOrder::query()->where('po_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
			$sequence = $lastOrder ? (int) str_replace($prefix, '', $lastOrder->po_number) + 1 : 1;

			return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
		}
	}