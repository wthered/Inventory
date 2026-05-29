<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('inventories', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique inventory record');

				$table->unsignedInteger('product_id')->nullable()->comment('Linked product');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

				$table->unsignedInteger('warehouse_id')->nullable()->comment('Linked warehouse');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();

				$table->unsignedInteger('location_id')->nullable()->comment('Specific location inside warehouse');
				$table->foreign('location_id')->references('id')->on('warehouse_locations')->nullOnDelete();

				$table->integer('quantity')->default(0)->comment('Total stock count in this location');
				$table->integer('reserved_quantity')->default(0)->comment('Quantity reserved for pending or unfulfilled orders');
				$table->integer('available_quantity')->virtualAs('quantity - reserved_quantity')->comment('Computed quantity available for sale');

				$table->string('batch_number')->nullable()->comment('Optional batch or lot number');
				$table->date('manufacturing_date')->nullable()->comment('Production or manufacturing date');
				$table->date('expiry_date')->nullable()->comment('Expiry date for perishable goods');

				$table->timestamps();

				$table->unique(['product_id', 'warehouse_id', 'location_id', 'batch_number'], 'inventories_unique');
				$table->index(['product_id', 'quantity']);

				$table->comment('Tracks real-time stock and product placement. This 3-way pivot connects a product_id to a warehouse_id and a precise location_id, detailing the stock quantity and relevant batch information.');
			});

			Schema::create('inventory_transactions', function (Blueprint $table) {
				$table->increments('id');

				// The "Where"
				$table->unsignedInteger('product_id');
				$table->unsignedInteger('warehouse_id');
				$table->unsignedInteger('location_id')->nullable();

				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
				$table->foreign('warehouse_id')->references('id')->on('warehouses');
				$table->foreign('location_id')->references('id')->on('warehouse_locations');

				// The "Action"
				$table->string('type', 16);
				$table->string('reason');    // 'transfer', 'adjustment', 'sale', etc.

				// The "Math"
				$table->integer('quantity'); // The change (e.g., -5 or +5)
				$table->integer('quantity_before');
				$table->integer('quantity_after');

				// The BATCH number
				$table->string('batch_number');

				// The cost per single unit at the time of transaction
				$table->decimal('unit_cost', 15)->nullable();

				// Total value (unit_cost * quantity)
				$table->decimal('total_cost', 15)->nullable();

				// The "Source" (Polymorphic)
				// This replaces your transaction_number and connects to
				// StockTransferItem, StockAdjustmentItem, etc.
				$table->string('reference_type');
				$table->unsignedInteger('reference_id');

				$table->unsignedInteger('created_by')->nullable();
				$table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

				$table->text('notes')->nullable();

				$table->timestamps();

				// Comprehensive Index for reporting
				$table->index([
					'product_id',
					'warehouse_id',
					'created_at'
				]);
				$table->index([
					'reference_type',
					'reference_id'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('inventories');
		}
	};
