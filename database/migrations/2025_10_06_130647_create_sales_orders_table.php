<?php

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Inventory\MovementStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('sales_orders', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique sales order identifier');
				$table->string('order_number')->unique()->comment('Unique sales order reference (e.g., SO-2026-0001)');

				// Σύνδεση με τον Πελάτη (Χρησιμοποιεί increments('id') στο customers table)
				$table->unsignedInteger('customer_id')->comment('Linked customer from customers table');
				$table->foreign('customer_id')->references('id')->on('customers');

				// Από ποια αποθήκη θα γίνει η εξυπηρέτηση/αποστολή
				$table->unsignedInteger('warehouse_id')->comment('The warehouse facility fulfilling this order');
				$table->foreign('warehouse_id')->references('id')->on('warehouses');

				// Καταστάσεις (Σύνδεση με Enums)
				$table->unsignedTinyInteger('status_id')->default(MovementStatus::DRAFT->value)->comment('Order status (e.g., Pending, Processing, Shipped, Cancelled)');
				$table->unsignedTinyInteger('payment_status_id')->default(1)->comment('Payment lifecycle status (e.g., Unpaid, Partially Paid, Paid)');

				// Ημερομηνίες
				$table->date('order_date')->comment('The date when the customer placed the order');
				$table->date('shipping_date')->nullable()->comment('The date when the order is scheduled to be or was shipped');

				// Οικονομικά Στοιχεία (Σύνολα πώλησης)
				$table->decimal('subtotal', 15, 2)->default(0)->comment('Total sales value before taxes and discounts');
				$table->decimal('tax_amount', 15, 2)->default(0)->comment('Total calculated VAT/Tax for this order');
				$table->decimal('discount_amount', 15, 2)->default(0)->comment('Total discount deducted from subtotal');
				$table->decimal('grand_total', 15, 2)->default(0)->comment('Final amount to be paid by customer (subtotal + tax - discount)');

				// Λογοδοσία
				$table->unsignedInteger('created_by')->comment('The employee who registered the sales order');
				$table->foreign('created_by')->references('id')->on('users');

				$table->text('notes')->nullable()->comment('Customer remarks or delivery instructions');
				$table->timestamps();
				$table->softDeletes();

				// Ευρετήρια (Indices) για γρήγορα reports πωλήσεων
				$table->index(['customer_id', 'status_id'], 'so_customer_status_idx');
				$table->comment('Master table for customer sales orders, managing client billing totals, fulfillment states, and warehouse assignments.');
			});

			Schema::create('sales_order_items', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique sales order line item identifier');

				$table->unsignedInteger('sales_order_id')->comment('Linked sales order master record');
				$table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();

				$table->unsignedInteger('product_id')->comment('The product being sold');
				$table->foreign('product_id')->references('id')->on('products');

				// === ΣΥΝΔΕΣΗ ΜΕ BATCH & LOCATION (BIN) ===
				// Στις πωλήσεις, το batch & bin ορίζονται κατά το Picking (όταν ο αποθηκάριος μαζεύει τα προϊόντα)
				$table->string('batch_number')->nullable()->comment('The specific product batch allocated for this sale');
				$table->unsignedInteger('location_id')->nullable()->comment('The exact warehouse bin from which the product is picked');
				$table->foreign('location_id')->references('id')->on('warehouse_locations');

				// Ποσότητες
				$table->integer('quantity_ordered')->comment('Quantity requested by the customer');
				$table->integer('quantity_shipped')->default(0)->comment('Quantity physically packed and shipped out');

				// Τιμολόγηση ανά γραμμή
				$table->decimal('unit_price', 15)->comment('Selling price per single unit for this specific transaction');
				$table->decimal('discount_rate', 5)->default(0)->comment('Line item discount percentage (e.g., 10.00 for 10%)');
				$table->decimal('discount_amount', 15)->default(0)->comment('Line item discount amount (e.g., 10.00 for 10 Euros)');

				// Virtual Columns για αυτόματους υπολογισμούς χωρίς σφάλματα στρογγυλοποίησης
				$table->decimal('total_ordered_price', 15)->virtualAs('quantity_ordered * (unit_price * (1 - discount_rate / 100))')->comment('Computed net line total based on ordered quantity');
				$table->decimal('total_shipped_price', 15)->virtualAs('quantity_shipped * (unit_price * (1 - discount_rate / 100))')->comment('Computed net line total based on actual shipped quantity');

				$table->timestamps();

				$table->index(['sales_order_id', 'product_id', 'batch_number'], 'so_items_product_batch_idx');
				$table->comment('Details line items for sales orders. Tracks customer demand versus real dispatch volumes while targeting specific batches and picking bins.');
			});

			Schema::create('sales_order_histories', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique sales order history log');

				$table->unsignedInteger('sales_order_id')->comment('The linked sales order');
				$table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();

				$table->string('action')->nullable()->index()->comment('Programmatic action descriptor (e.g., status_changed, discount_applied)');
				$table->string('event')->nullable()->comment('Friendly title for logs (e.g., Order Processed)');

				// JSON για αποθήκευση old_value και new_value
				$table->json('details')->nullable()->comment('JSON payload capturing specific state mutations');
				$table->text('description')->nullable()->comment('Human readable description of the event');

				$table->unsignedInteger('user_id')->nullable()->comment('The user who performed the action');
				$table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

				$table->timestamps();
				$table->softDeletes();

				$table->index('sales_order_id', 'so_history_master_idx');
				$table->comment('Maintains an immutable audit trail of all status shifts, financial changes, and shipping actions for sales orders.');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('sales_order_histories');
			Schema::dropIfExists('sales_order_items');
			Schema::dropIfExists('sales_orders');
		}
	};
