<?php

	use App\Enums\Sales\SalesOrderStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {

			Schema::create('inventory_audits', function (Blueprint $table) {
				$table->increments('id');
				$table->string('audit_number')->unique();

				$table->unsignedInteger('warehouse_id');
				$table->foreign('warehouse_id')->references('id')->on('warehouses');

				$table->unsignedInteger('created_by');
				$table->foreign('created_by')->references('id')->on('users');

				// Κατάσταση: 'draft' (προετοιμασία), 'in_progress' (μετρούν), 'completed' (ολοκληρώθηκε)
				$table->unsignedTinyInteger('status')->default(SalesOrderStatus::DRAFT);

				$table->timestamp('started_at')->nullable();
				$table->timestamp('completed_at')->nullable();
				$table->text('notes')->nullable();
				$table->timestamps();
			});

			Schema::create('inventory_audit_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('inventory_audit_id');
				$table->foreign('inventory_audit_id')->references('id')->on('inventory_audits')->onDelete('cascade');

				$table->unsignedInteger('product_id');
				$table->unsignedInteger('location_id');

				$table->integer('system_quantity')->comment('Τι λέει η βάση εκείνη τη στιγμή');
				$table->integer('physical_quantity')->comment('Τι μέτρησε ο χρήστης');
				$table->integer('discrepancy')->comment('physical - system (μπορεί να είναι +/-)');

				$table->string('adjustment_status')->default('pending')->comment('pending / adjusted / ignored');
				$table->timestamps();

				$table->foreign('product_id')->references('id')->on('products');
				$table->foreign('location_id')->references('id')->on('warehouse_locations');

				$table->unique(['inventory_audit_id', 'product_id', 'location_id'], 'audit_items_unique');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('inventory_audit_items');
			Schema::dropIfExists('inventory_audits');
		}
	};
