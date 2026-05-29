<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('invoices', function (Blueprint $table) {
				$table->increments('id');
				$table->string('invoice_number')->unique();
				$table->unsignedInteger('customer_id');
				$table->date('invoice_date');
				$table->date('due_date')->nullable();
				$table->decimal('subtotal', 12)->default(0);
				$table->decimal('tax', 12)->default(0);
				$table->decimal('total', 12)->default(0);
				$table->enum('status', [
					'draft',
					'sent',
					'paid',
					'cancelled'
				])->default('draft');
				$table->text('notes')->nullable();
				$table->timestamps();

				$table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
			});

			Schema::create('invoice_items', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('invoice_id');
				$table->unsignedInteger('product_id');
				$table->integer('quantity')->default(1);
				$table->decimal('unit_price', 12)->default(0);
				$table->decimal('total', 12)->default(0);
				$table->timestamps();

				$table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('invoices');
		}
	};
