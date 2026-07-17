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
				// Primary Key
				$table->increments('id')->comment('Primary key: unique inventory record identifier');

				// Foreign Keys (Relations)
				$table->unsignedInteger('product_id')->comment('Linked product from products table');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

				$table->unsignedInteger('warehouse_id')->comment('Linked warehouse facility from warehouses table');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();

				$table->unsignedInteger('location_id')->comment('Specific physical storage slot (Bin) inside the warehouse');
				$table->foreign('location_id')->references('id')->on('warehouse_locations')->cascadeOnDelete();

				// Quantities & Availability
				$table->integer('quantity')->default(0)->comment('Total physical stock count currently present in this specific location');
				$table->integer('reserved_quantity')->default(0)->comment('Stock volume booked for pending sales, orders, or in-transit stock transfers');
				$table->integer('available_quantity')->virtualAs('quantity - reserved_quantity')->comment('Computed stock volume instantly available for new sales or actions');

				// Valuation Snapshots
				$table->decimal('unit_cost', 15)->nullable()->comment('The cost value per single unit at the exact time of the transaction');
				$table->decimal('total_cost', 15)->nullable()->comment('Total financial valuation change (unit_cost * quantity)');

				// Polymorphic Source Tracking
//				$table->string('reference_type')->comment('Class name of the triggering model: e.g., App\\Models\\StockTransferItem');
//				$table->unsignedInteger('reference_id')->comment('The database ID of the specific source item that caused this movement');

				// Batch & Traceability
				$table->string('batch_number')->nullable()->comment('Optional batch or lot number for tracking product groups');
				$table->date('manufacturing_date')->nullable()->comment('Production or manufacturing date of the specific batch');
				$table->date('expiry_date')->nullable()->comment('Expiry date for perishable goods or batch lifecycle tracking');

				// Timestamps
				$table->timestamps();

				// Constraints & Performance Indices
				// Μοναδικότητα: Σε ένα συγκεκριμένο Bin μιας Αποθήκης, για ένα Προϊόν, υπάρχει μόνο ΜΙΑ εγγραφή ανά Batch!
				$table->unique(['product_id', 'warehouse_id', 'location_id', 'batch_number'], 'inventories_product_location_batch_unique');

				// Index για γρήγορα reports και φιλτράρισμα live αποθεμάτων
				$table->index(['product_id', 'quantity'], 'inventories_product_qty_idx');

				// Performance Indices
//				$table->index(['reference_type', 'reference_id'], 'inv_transactions_poly_source_idx');

				// --- HIGH PERFORMANCE OPTIMIZATION INDEX INJECTION ---
				$table->index(['warehouse_id', 'product_id', 'quantity'], 'inv_perf_balance_idx');

				$table->comment('Tracks real-time live stock status and product placement. Connects products to physical warehouse slots (Bins) while maintaining batch tracking and reservations.');
			});

			Schema::create('inventory_transactions', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique stock transaction ledger identifier');

				// The "Where" (Relations)
				$table->unsignedInteger('product_id')->comment('The product being moved or adjusted');
				$table->unsignedInteger('warehouse_id')->comment('The warehouse facility where the transaction takes place');
				$table->unsignedInteger('location_id')->nullable()->comment('The specific warehouse location (Bin) involved, if applicable');

				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
				$table->foreign('warehouse_id')->references('id')->on('warehouses');
				$table->foreign('location_id')->references('id')->on('warehouse_locations');

				// The "Action" & Auditing
				$table->string('type', 16)->comment('Transaction vector type: e.g., IN, OUT, ADJUST');
				$table->string('reason')->comment('The business logic behind the movement: e.g., purchase, sale, transfer, adjustment');

				// The "Math"
				$table->integer('quantity')->comment('The net change in stock quantity (e.g., +10 for receive, -5 for dispatch)');
				$table->integer('quantity_before')->comment('Snapshot of physical stock quantity right before this transaction');
				$table->integer('quantity_after')->comment('Snapshot of physical stock quantity right after this transaction was committed');

				// Traceability & Financials
				$table->string('batch_number')->comment('The product batch number targeted by this transaction');
				$table->decimal('unit_cost', 15, 2)->nullable()->comment('The cost value per single unit at the exact time of the transaction');
				$table->decimal('total_cost', 15, 2)->nullable()->comment('Total financial valuation change (unit_cost * quantity)');

				// Polymorphic Source Tracking
				$table->string('reference_type')->comment('Class name of the triggering model: e.g., App\\Models\\StockTransferItem');
				$table->unsignedInteger('reference_id')->comment('The database ID of the specific source item that caused this movement');

				// Accountability
				$table->unsignedInteger('created_by')->nullable()->comment('The user ID responsible for committing or authorizing this record');
				$table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

				$table->text('notes')->nullable()->comment('Internal operator comments or reference details regarding the transaction');

				// Timestamps
				$table->timestamps();

				// Performance Indices
				$table->index(['product_id', 'warehouse_id', 'created_at'], 'inv_transactions_report_idx');
				$table->index(['reference_type', 'reference_id'], 'inv_transactions_poly_source_idx');

				$table->comment('The single source of truth ledger for audit trails. Permanently logs every stock modification event, change delta, financial snapshot, and historical reference.');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('inventories');
		}
	};
