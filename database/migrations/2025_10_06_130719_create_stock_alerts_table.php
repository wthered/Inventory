<?php

	use App\Enums\Inventory\AlertStatus;
	use App\Enums\Inventory\AlertType;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('stock_alerts', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
				$table->unsignedInteger('warehouse_id');
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
				$table->string('alert_type')->default(AlertType::OUT_OF_STOCK);
				$table->integer('current_quantity');
				$table->integer('threshold_quantity')->nullable();
				$table->date('expiry_date')->nullable();
				$table->text('message');
				$table->string('status')->default(AlertStatus::ACTIVE);
				$table->unsignedInteger('resolved_by')->nullable();
				$table->foreign('resolved_by')->references('id')->on('users');
				$table->timestamp('resolved_at')->nullable();
				$table->timestamps();

				$table->index([
					'product_id',
					'warehouse_id',
					'alert_type',
					'status'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('stock_alerts');
		}
	};
