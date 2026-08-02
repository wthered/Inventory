<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('brands', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name');
				$table->string('slug')->unique();
				$table->text('description')->nullable();
				$table->string('logo')->nullable();
				$table->string('website')->nullable();
				$table->boolean('is_active')->default(true);
				$table->timestamps();
				$table->softDeletes();
			});

			Schema::create('brand_category', function(Blueprint $table) {
				$table->unsignedInteger('brand_id');
				$table->unsignedInteger('category_id');
				$table->unique(['brand_id', 'category_id']);
				$table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
				$table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('brands');
		}
	};
