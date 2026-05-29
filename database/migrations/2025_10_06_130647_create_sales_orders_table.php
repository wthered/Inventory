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
			Schema::create('sales_orders', function (Blueprint $table) {
				$table->increments('id');
				$table->string('order_number')->unique();
				$table->unsignedInteger('customer_id');
				$table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
				$table->unsignedInteger('warehouse_id');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
				$table->date('order_date');
				$table->date('delivery_date')->nullable();
				$table->decimal('subtotal', 12)->default(0);
				$table->decimal('tax_amount', 12)->default(0);
				$table->decimal('discount_amount', 12)->default(0);
				$table->decimal('shipping_cost', 12)->default(0);
				$table->decimal('total_amount', 12)->default(0);
				$table->unsignedTinyInteger('status')->default(SalesOrderStatus::DRAFT->value);
				$table->unsignedTinyInteger('payment_status')->default(PaymentStatus::UNPAID->value);
				$table->text('shipping_address')->nullable();
				$table->text('notes')->nullable();
				$table->unsignedInteger('created_by');
				$table->foreign('created_by')->references('id')->on('users');
				$table->timestamps();
				$table->softDeletes();

				$table->index([
					'customer_id',
					'warehouse_id',
					'status'
				]);
				$table->index([
					'order_date',
					'status'
				]);
			});

			Schema::create('sales_order_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('sales_order_id');
				$table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
				$table->integer('quantity')->comment('Quantity Ordered');
				$table->integer('quantity_shipped')->default(0);
				$table->decimal('unit_price', 10);
				$table->decimal('discount_percent', 5)->default(0);
				$table->decimal('discount_amount', 10)->default(0);
				$table->decimal('tax_percent', 5)->default(0);
				$table->decimal('tax_amount', 10)->default(0);
				$table->decimal('subtotal', 12);
				$table->decimal('total', 12);
				$table->text('notes')->nullable();
				$table->timestamps();

				$table->index([
					'sales_order_id',
					'product_id'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('sales_orders');
		}
	};
