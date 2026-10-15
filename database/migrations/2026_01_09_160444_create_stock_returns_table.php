<?php

	use App\Enums\Inventory\StockReturnStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			// The Header: One per RMA/Return Package
			Schema::create('stock_returns', function (Blueprint $table) {
				$table->increments('id');
				$table->string('return_number')->unique();
				$table->string('rma_number')->nullable()->unique();
				$table->string('returnable_type')->nullable();
				$table->unsignedInteger('returnable_id')->nullable();
				$table->unsignedInteger('warehouse_id');
				$table->string('status')->default(StockReturnStatus::PENDING->value);
				$table->date('return_date');
				$table->string('tracking_number')->nullable();
				$table->string('carrier')->nullable();
				$table->unsignedInteger('created_by');
				$table->timestamps();
				$table->softDeletes();

				$table->index([
					'returnable_id',
					'returnable_type'
				]);
			});

			// The Detail: One per Product in that RMA
			Schema::create('stock_return_items', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('stock_return_id');
				$table->unsignedInteger('product_id');
				$table->unsignedInteger('location_id')->nullable();
				$table->integer('quantity');
				$table->decimal('unit_cost', 15)->nullable();
				$table->string('quality_status')->default('new');
				$table->boolean('is_restockable')->default(true);
				$table->text('inspection_notes')->nullable();
				$table->timestamp('restocked_at')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('stock_return_id')->references('id')->on('stock_returns')->cascadeOnDelete();
				$table->foreign('product_id')->references('id')->on('products');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('stock_return_items');
			Schema::dropIfExists('stock_returns');
		}
	};
