<?php

	namespace App\Http\Controllers;

	use App\Http\Requests\Invoices\StoreInvoiceRequest;
	use App\Models\Invoice;
	use App\Models\InvoiceItem;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;

	class InvoiceController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			//
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StoreInvoiceRequest $request): RedirectResponse {
			$validated = $request->validate([

			]);

			$invoice = Invoice::query()->create([
				'invoice_number' => $validated['invoice_number'],
				'customer_id'    => $validated['customer_id'],
				'invoice_date'   => $validated['invoice_date'],
				'due_date'       => $validated['due_date'] ?? null,
				'subtotal'       => 0,
				'tax'            => 0,
				'total'          => 0,
			]);

			$subtotal = 0;

			foreach ($validated['items'] as $item) {
				$total = $item['quantity'] * $item['unit_price'];
				$subtotal += $total;

				InvoiceItem::query()->create([
					'invoice_id' => $invoice->id,
					'product_id' => $item['product_id'],
					'quantity'   => $item['quantity'],
					'unit_price' => $item['unit_price'],
					'total'      => $total,
				]);
			}

			$tax = $subtotal * 0.2; // example 20% tax
			$invoice->update([
				'subtotal' => $subtotal,
				'tax'      => $tax,
				'total'    => $subtotal + $tax,
			]);

			return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created successfully.');
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			//
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(Request $request, Invoice $invoice): RedirectResponse {
			// Validate invoice fields and items
			$validated = $request->validate([
				'customer_id'        => 'required|exists:customers,id',
				'invoice_date'       => 'required|date',
				'due_date'           => 'nullable|date|after_or_equal:invoice_date',
				'status'             => 'required|in:draft,sent,paid,cancelled',
				'notes'              => 'nullable|string',
				'items.*.id'         => 'nullable|exists:invoice_items,id',
				'items.*.product_id' => 'required|exists:products,id',
				'items.*.quantity'   => 'required|integer|min:1',
				'items.*.unit_price' => 'required|numeric|min:0',
			]);

			// Update main invoice info
			$invoice->update([
				'customer_id'  => $validated['customer_id'],
				'invoice_date' => $validated['invoice_date'],
				'due_date'     => $validated['due_date'] ?? null,
				'status'       => $validated['status'],
				'notes'        => $validated['notes'] ?? null,
			]);

			$subtotal = 0;

			// Loop through items
			if (!empty($validated['items'])) {
				foreach ($validated['items'] as $itemData) {
					$total = $itemData['quantity'] * $itemData['unit_price'];
					$subtotal += $total;

					if (!empty($itemData['id'])) {
						// Update existing item
						$item = $invoice->items()->find($itemData['id']);
						if ($item) {
							$item->update([
								'product_id' => $itemData['product_id'],
								'quantity'   => $itemData['quantity'],
								'unit_price' => $itemData['unit_price'],
								'total'      => $total,
							]);
						}
					} else {
						// Create new item
						$invoice->items()->create([
							'product_id' => $itemData['product_id'],
							'quantity'   => $itemData['quantity'],
							'unit_price' => $itemData['unit_price'],
							'total'      => $total,
						]);
					}
				}
			}

			// Recalculate tax and total
			$tax = $subtotal * 0.2; // example 20% tax
			$invoice->update([
				'subtotal' => $subtotal,
				'tax'      => $tax,
				'total'    => $subtotal + $tax,
			]);

			return redirect()
				->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
		}

		/**
		 * Display the specified resource.
		 */
		public function show(string $id) {
			//
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(string $id) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(string $id) {
			//
		}
	}
