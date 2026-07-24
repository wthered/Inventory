<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('suppliers', function (Blueprint $table) {
				$table->increments('id');
				$table->string('code')->unique();
				$table->string('name');
				$table->string('company_name')->nullable();
				$table->string('email')->nullable();
				$table->string('phone');
				$table->string('website')->nullable();
				$table->string('tax_number')->nullable();
				$table->text('address')->nullable();
				$table->string('city')->nullable();
				$table->string('state')->nullable();
				$table->string('country')->nullable();
				$table->string('postal_code')->nullable();
				$table->string('contact_person')->nullable();
				$table->string('contact_phone')->nullable();
				$table->decimal('credit_limit', 12, 2)->default(0);
				$table->enum('payment_terms', [
					'cash',
					'credit_7',
					'credit_15',
					'credit_30',
					'credit_60',
					'credit_90'
				])->default('cash');
				$table->text('notes')->nullable();
				$table->boolean('is_active')->default(true);
				$table->timestamps();
				$table->softDeletes();

				$table->index([
					'code',
					'is_active'
				]);
			});

			Schema::create('product_supplier', function (Blueprint $table) {
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
			Schema::dropIfExists('product_supplier');
			Schema::dropIfExists('suppliers');
		}
	};
