<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('inventory_movement_logs', function (Blueprint $table) {
				$table->id();

				// Core references for the stock slot location
				$table->unsignedInteger('product_id');
				$table->unsignedInteger('warehouse_id');
				$table->unsignedInteger('location_id');

				// Movement parameters and status tracking
				$table->string('action', 50)->comment('e.g., STOCK_OUT_ATTEMPT');
				$table->string('status', 30)->comment('e.g., failed');
				$table->integer('requested_quantity');
				$table->integer('before_quantity')->default(0);
				$table->text('error_message')->nullable();

				// Polymorphic relation matching the $reference object context
				$table->nullableMorphs('loggable');

				// Audit trailing
				$table->unsignedInteger('user_id');
				$table->timestamps();

				// Foreign key constraints
				$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
				$table->foreign('location_id')->references('id')->on('warehouse_locations')->onDelete('cascade');
				$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

				// Compound indexing for optimized queries via $inventory->movementLogs()
				$table->index(['product_id', 'warehouse_id', 'location_id'], 'inv_movement_logs_lookup_idx');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('inventory_movement_logs');
		}
	};
