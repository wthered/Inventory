<?php

	use App\Enums\TransferStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('stock_transfers', function (Blueprint $table) {
				$table->increments('id');
				$table->string('transfer_number')->unique(); // e.g., TRF-2026-0001

				// Locations
				$table->unsignedInteger('source_warehouse_id');
				$table->unsignedInteger('target_warehouse_id');
				$table->foreign('source_warehouse_id')->references('id')->on('warehouses');
				$table->foreign('target_warehouse_id')->references('id')->on('warehouses');

				// Use your Enum for status_id instead of string Enums
				// This connects to the TransferStatus Enum we built earlier
				$table->unsignedTinyInteger('status_id')->default(TransferStatus::PENDING->value);

				// Dates
				$table->date('transfer_date');
				$table->date('expected_delivery_date')->nullable();
				$table->timestamp('approved_at')->nullable();
				$table->timestamp('received_at')->nullable();

				// Accountability (The "Who")
				$table->unsignedInteger('created_by');
				$table->unsignedInteger('approved_by')->nullable();
				$table->unsignedInteger('received_by')->nullable();

				$table->foreign('created_by')->references('id')->on('users');
				$table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
				$table->foreign('received_by')->references('id')->on('users')->nullOnDelete();

				$table->text('notes')->nullable();
				$table->timestamps();
				$table->softDeletes();

				// High-performance indexing
				$table->index(['source_warehouse_id', 'target_warehouse_id', 'status_id'], 'stock_transfer_warehouse_status_idx');
			});

			Schema::create('stock_transfer_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('stock_transfer_id');
				$table->unsignedInteger('product_id');

				// Quantities (Crucial for audit trails)
				$table->integer('quantity_requested'); // What we intended to move
				$table->integer('quantity_delivered')->default(0); // What actually left Bin A
				$table->integer('quantity_received')->default(0);  // What actually arrived at Bin B

				// Accountability (Moved from your other table)
				$table->unsignedInteger('processed_by')->nullable(); // Who physically moved it
				$table->dateTime('processed_at')->nullable();

				$table->text('notes')->nullable();
				$table->timestamps();

				// Indexing
				$table->unique(['stock_transfer_id', 'product_id'], 'transfer_product_unique');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('stock_transfers');
		}
	};
