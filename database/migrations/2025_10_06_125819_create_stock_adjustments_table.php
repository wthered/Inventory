<?php

	use App\Enums\Inventory\AdjustmentReason;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('stock_adjustments', function (Blueprint $table) {
				$table->increments('id');
				$table->string('adjustment_number')->unique(); // e.g., ADJ-2026-0001
				$table->unsignedInteger('warehouse_id');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();

				$table->date('adjustment_date');
				$table->text('notes')->nullable();

				// We can use an Enum class for this later too!

				$table->unsignedInteger('approved_by')->nullable();
				$table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
				$table->timestamp('approved_at')->nullable();

				$table->unsignedInteger('created_by');
				$table->foreign('created_by')->references('id')->on('users');
				$table->timestamps();
				$table->softDeletes();
			});

			Schema::create('stock_adjustment_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('stock_adjustment_id');
				$table->foreign('stock_adjustment_id')->references('id')->on('stock_adjustments')->cascadeOnDelete();

				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

				$table->unsignedInteger('location_id')->nullable();
				$table->foreign('location_id')->references('id')->on('warehouse_locations')->nullOnDelete()->cascadeOnUpdate();

				// Type and Reason at the item level (In case one batch has different reasons)
				$table->enum('reason', AdjustmentReason::cases());

				$table->integer('quantity');
				// Snapshot for audit
				$table->integer('quantity_before');
				// Final calculated stock
				$table->integer('quantity_after')->nullable();

				// Cost of the item at the time of adjustment
				$table->decimal('unit_cost', 15)->nullable();

				$table->text('notes')->nullable();
				$table->timestamps();

				$table->index([
					'stock_adjustment_id',
					'product_id'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('stock_adjustment_items');
			Schema::dropIfExists('stock_adjustments');
		}
	};
