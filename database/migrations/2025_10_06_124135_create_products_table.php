<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('products', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: Unique product identifier');
				$table->string('sku')->unique()->comment('Stock Keeping Unit: unique product code');
				$table->string('barcode')->nullable()->unique()->comment('Optional barcode number for scanning');
				$table->string('name')->comment('Product name');
				$table->string('slug')->unique()->comment('Unique URL-friendly identifier');
				$table->text('description')->nullable()->comment('Detailed product description');

				$table->unsignedInteger('category_id')->nullable()->comment('Foreign key referencing categories table');
				$table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();

				$table->unsignedInteger('brand_id')->nullable()->comment('Foreign key referencing brands table');
				$table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();

				$table->decimal('cost_price', 10)->default(0)->comment('Purchase or manufacturing cost');
				$table->decimal('selling_price', 10)->default(0)->comment('Regular selling price');
				$table->decimal('discount_price', 10)->nullable()->comment('Optional discounted price');
				$table->string('unit')->default('pcs')->comment('Unit of measurement: pcs, kg, liter, etc.');

				$table->integer('min_stock_level')->default(0)->comment('Minimum stock threshold before alert');
				$table->integer('max_stock_level')->nullable()->comment('Maximum allowed stock limit');
				$table->integer('reorder_point')->default(0)->comment('Quantity at which to reorder');
				$table->integer('current_stock')->default(0)->comment('Current available quantity in inventory');

				$table->boolean('track_inventory')->default(true)->comment('Enable inventory tracking');
				$table->boolean('is_active')->default(true)->comment('Product active status');

				$table->json('specifications')->nullable()->comment('Additional product attributes in JSON format');

				$table->timestamps();
				$table->softDeletes();

				$table->index(['sku', 'category_id', 'is_active']);
				$table->fullText(['name', 'description', 'sku']);
			});

			Schema::create('product_images', function (Blueprint $table) {
				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
				$table->string('image_location')->nullable();
				$table->boolean('is_default')->default(false);
				$table->timestamps();
			});

			Schema::create('product_history', function (Blueprint $table) {

				// Κλειδί σύνδεσης με το προϊόν που αλλάχθηκε.
				// Αν το προϊόν διαγραφεί, διαγράφονται και τα ιστορικά του (cascade).
				$table->unsignedInteger('product_id');
				$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

				// Κλειδί σύνδεσης με τον χρήστη που έκανε την αλλαγή.
				// Αν ο χρήστης διαγραφεί, το πεδίο γίνεται null (set null).
				$table->unsignedInteger('user_id')->nullable();
				$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

				// Τύπος ενέργειας που έγινε (π.χ. 'price_updated', 'stock_adjusted', 'archived').
				$table
					->string('action', 50)
					->index();

				// Αποθήκευση των λεπτομερειών της αλλαγής.
				// Χρησιμοποιούμε JSON για να αποθηκεύσουμε δεδομένα όπως old_value, new_value.
				$table->json('details')->nullable();

				$table->ipAddress()->nullable();
				$table->uuid('reference')->nullable()->unique();

				// Χρονοσφραγίδες (created_at, updated_at). Το created_at είναι η στιγμή της αλλαγής.
				$table->timestamps();
				$table->softDeletes();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('products');
		}
	};
