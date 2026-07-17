<?php

	use App\Enums\Purchases\PurchaseOrderStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('purchase_orders', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique purchase order identifier');
				$table->string('po_number')->unique()->comment('Unique purchase order reference number (e.g., PO-2026-0001)');

				// Σύνδεση με τον Προμηθευτή
				$table->unsignedInteger('supplier_id')->comment('The supplier from whom the goods are ordered');
				$table->foreign('supplier_id')->references('id')->on('suppliers');

				// Σύνδεση με την Αποθήκη Παραλαβής
				$table->unsignedInteger('warehouse_id')->comment('The target warehouse where goods will be delivered');
				$table->foreign('warehouse_id')->references('id')->on('warehouses');

				// Κατάσταση (Pending, Partially Received, Received, Cancelled)
				// Μπορείς να χρησιμοποιήσεις ένα Enum (π.χ. App\Enums\PurchaseOrderStatus)
				$table->unsignedTinyInteger('status_id')->default(PurchaseOrderStatus::DRAFT->value)->comment('Current status of the order');

				// Ημερομηνίες
				$table->date('order_date')->comment('The date when the order was placed');
				$table->date('expected_date')->nullable()->comment('Estimated delivery date from supplier');
				$table->timestamp('received_at')->nullable()->comment('Timestamp when the order was completely or partially received');

				// Οικονομικά Στοιχεία (Σύνολα)
				$table->decimal('subtotal', 15, 2)->default(0)->comment('Total cost before taxes and discounts');
				$table->decimal('tax_amount', 15, 2)->default(0)->comment('Calculated tax amount');
				$table->decimal('discount_amount', 15, 2)->default(0)->comment('Applied discount amount');
				$table->decimal('grand_total', 15, 2)->default(0)->comment('Final total cost to be paid (subtotal + tax - discount)');

				// Λογοδοσία
				$table->unsignedInteger('created_by')->comment('The user who created the purchase order');
				$table->foreign('created_by')->references('id')->on('users');

				$table->text('notes')->nullable()->comment('Internal notes or instructions for the supplier/warehouse');
				$table->timestamps();
				$table->softDeletes();

				$table->index(['supplier_id', 'status_id'], 'po_supplier_status_idx');
				$table->comment('Master table for purchase orders, tracking vendor relationships, procurement statuses, and financial totals.');
			});

			Schema::create('purchase_order_items', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique purchase order item line identifier');

				$table->unsignedInteger('purchase_order_id')->comment('Linked purchase order master record');
				$table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();

				$table->unsignedInteger('product_id')->comment('The product being ordered');
				$table->foreign('product_id')->references('id')->on('products');

				// === ΕΔΩ ΕΙΝΑΙ ΤΟ BATCH & LOCATION ===
				$table->string('batch_number')->nullable()->comment('The batch number assigned upon receipt. Can be null during planning.');
				$table->date('manufacturing_date')->nullable()->comment('Batch manufacturing date provided by vendor');
				$table->date('expiry_date')->nullable()->comment('Batch expiry date for shelf life tracking');

				$table->unsignedInteger('location_id')->nullable()->comment('The specific warehouse slot (Bin) where this item is put away');
				$table->foreign('location_id')->references('id')->on('warehouse_locations');

				// Ποσότητες (Για έλεγχο ελλειμμάτων/πλεονασμάτων)
				$table->integer('quantity_ordered')->comment('Quantity originally requested from the supplier');
				$table->integer('quantity_received')->default(0)->comment('Quantity actually delivered and accepted into inventory');

				// Οικονομικά ανά γραμμή
				$table->decimal('unit_price', 15)->comment('Purchase cost per single unit before discount');

				// Προσθήκη του discount_rate ως ποσοστό (π.χ. 10.50 για 10.5%)
				$table->decimal('discount_rate', 5)->default(0.00)->comment('Percentage discount applied to this specific line item');

				// === ΕΝΗΜΕΡΩΣΗ VIRTUAL ΣΤΗΛΩΝ ===
				// Πλέον η MySQL υπολογίζει το σύνολο αφαιρώντας αυτόματα το ποσοστό έκπτωσης!
				$table->decimal('total_ordered_price', 15)->virtualAs('quantity_ordered * unit_price * (1 - (discount_rate / 100))')->comment('Computed line total price based on order volume and discount');
				$table->decimal('total_received_price', 15)->virtualAs('quantity_received * unit_price * (1 - (discount_rate / 100))')->comment('Computed line total price based on actual received volume and discount');

				$table->timestamps();

				$table->index(['purchase_order_id', 'product_id', 'batch_number'], 'po_items_product_batch_idx');
				$table->comment('Details line items for purchase orders. Captures quantity expectations vs actual physical receipts, while assigning items to batches and warehouse bins.');
			});

			Schema::create('purchase_order_histories', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique purchase order history logs');

				$table->unsignedInteger('purchase_order_id')->comment('The linked purchase order');
				$table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();

				$table->string('action')->nullable()->index()->comment('Programmatic action key (e.g., status_changed, item_added)');
				$table->string('event')->nullable()->comment('Friendly title for logs (e.g., Status Changed to Received)');

				// Μετατρέψαμε το details σε JSON για ομοιομορφία με το product_history σου!
				$table->json('details')->nullable()->comment('JSON payload capturing old and new state changes');
				$table->text('description')->nullable()->comment('Human readable description of what happened');

				$table->unsignedInteger('user_id')->nullable()->comment('The user who triggered this history log');
				$table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

				$table->timestamps();
				$table->softDeletes();

				$table->index('purchase_order_id', 'po_history_master_idx');
				$table->comment('Maintains an immutable audit trail of all state modifications, approvals, and physical receipt logs for purchase orders.');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('purchase_orders');
		}
	};
