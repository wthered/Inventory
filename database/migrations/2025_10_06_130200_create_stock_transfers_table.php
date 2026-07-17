<?php

	use App\Enums\Inventory\MovementStatus;
	use App\Enums\Inventory\TransferStatus;
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
				$table->unsignedTinyInteger('status_id')->default(MovementStatus::PENDING->value);

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
				$table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->cascadeOnDelete();

				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products');

				// Παρτίδα (Batch)
				$table->string('batch_number')->nullable()->comment('The specific product batch being transferred');

				// Τοποθεσίες (Bins) - Σύνδεση με τον πίνακα warehouse_locations
				$table->unsignedInteger('source_location_id')->comment('Source Bin (Bin A)');
				$table->unsignedInteger('target_location_id')->comment('Target Bin (Bin B)');
				$table->foreign('source_location_id')->references('id')->on('warehouse_locations');
				$table->foreign('target_location_id')->references('id')->on('warehouse_locations');

				// Ποσότητες (Κρατάμε το δικό σου εξαιρετικό σκεπτικό για το audit trail)
				$table->integer('quantity_requested'); // Τι ζητήθηκε αρχικά
				$table->integer('quantity_delivered')->default(0); // Τι έφυγε πραγματικά από το Bin A
				$table->integer('quantity_received')->default(0);  // Τι έφτασε πραγματικά στο Bin B

				// Λογοδοσία & Σημειώσεις
				$table->unsignedInteger('processed_by')->nullable(); // Ποιος έκανε τη φυσική μετακίνηση
				$table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
				$table->dateTime('processed_at')->nullable();

				$table->text('notes')->nullable();
				$table->timestamps();

				// Σύνθετο index για γρήγορο search ανά παρτίδα ή τοποθεσία κατά τη μεταφορά
				$table->index(['stock_transfer_id', 'product_id', 'batch_number'], 'transfer_item_batch_idx');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('stock_transfers');
		}
	};
