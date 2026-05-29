<?php

	use App\Enums\Financial\PaymentStatus;
	use App\Enums\Sales\SalesOrderStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('purchase_orders', function (Blueprint $table) {
				$table->increments('id');
				$table->string('order_number')->unique();
				$table->unsignedInteger('supplier_id');
				$table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
				$table->unsignedInteger('warehouse_id');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
				$table->date('order_date');
				$table->date('expected_delivery_date')->nullable();
				$table->date('actual_delivery_date')->nullable();

				// Math (Προσθήκη δεκαδικών 12, 2)
				$table->decimal('subtotal', 12)->default(0);
				$table->decimal('tax_amount', 12)->default(0);
				$table->decimal('discount_amount', 12)->default(0);
				$table->decimal('shipping_cost', 12)->default(0);
				$table->decimal('total_amount', 12)->default(0);

				// Το status της παραγγελίας (Draft, Approved κλπ)
				$table->unsignedTinyInteger('status')->default(SalesOrderStatus::DRAFT->value);

				// Η κατάσταση πληρωμής (Unpaid, Paid κλπ)
				$table->unsignedTinyInteger('payment_status')->default(PaymentStatus::UNPAID->value);

				$table->text('notes')->nullable();
				$table->string('reference_number')->nullable();
				$table->unsignedInteger('created_by');
				$table->foreign('created_by')->references('id')->on('users');
				$table->unsignedInteger('approved_by')->nullable();
				$table->foreign('approved_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
				$table->timestamp('approved_at')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->index([
					'supplier_id',
					'warehouse_id',
					'status'
				]);
				$table->index([
					'order_date',
					'status'
				]);
			});

			Schema::create('purchase_order_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('purchase_order_id');
				$table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
				$table->integer('quantity_ordered');
				$table->integer('quantity_received')->default(0);
				$table->decimal('unit_cost', 10);
				$table->decimal('discount_percent', 5)->default(0);
				$table->decimal('discount_amount', 10)->default(0);
				$table->decimal('tax_amount', 10)->default(0);
				$table->decimal('tax_percent', 5)->default(0);
				$table->decimal('subtotal', 12);
				$table->decimal('total', 12);
				$table->text('notes')->nullable();
				$table->timestamps();

				$table->index([
					'purchase_order_id',
					'product_id'
				]);
			});

			Schema::create('purchase_order_histories', function (Blueprint $table) {

				// 1. Primary Key: Use the fluent 'id()' (which is unsignedBigInteger)
				$table->increments('id');

				// 2. Foreign Key: Use unsignedBigInteger for consistency
				$table->unsignedInteger('purchase_order_id');
				$table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');

				// Προσθήκη action & event
				$table->string('action')->nullable()->index()->comment('Για προγραμματιστική χρήση (π.χ. updated)');
				$table->string('event')->nullable()->comment('Για τον τίτλο (π.x. Status Changed)');

				// Προσθήκη details & description
				$table->text('details')->nullable();
				$table->text('description')->nullable();

				// 3. User ID: This is already correct as unsignedBigInteger
				$table->unsignedInteger('user_id')->nullable();
				$table->foreign('user_id')->references('id')->on('users');

				$table->timestamps();
				$table->softDeletes();

				// 4. Index: Index on the foreign key is good practice
				$table->index('purchase_order_id');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('purchase_orders');
		}
	};
