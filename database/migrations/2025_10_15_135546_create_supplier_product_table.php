<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('suppliers_products', function (Blueprint $table) {
				$table->unsignedInteger('supplier_id');
				$table->unsignedInteger('product_id');

				$table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

				$table->decimal('price', 10)->nullable();
				$table->integer('lead_time_days')->nullable()->comment('the number of days a supplier needs to deliver the product after you place an order');
				$table->unsignedSmallInteger('moq')->nullable()->comment('Minimum Order Quantity');
				$table->boolean('is_preferred')->default(false);

				$table->timestamps();

				$table->unique([
					'supplier_id',
					'product_id'
				], 'supplier_product_unique');
				$table->index([
					'product_id',
					'price'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('supplier_product');
		}
	};
