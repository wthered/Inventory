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
				$table->string('name')->unique();
				$table->string('slug')->unique();
				$table->text('description')->nullable();
				$table->string('logo')->nullable();
				$table->string('website')->nullable();
				$table->boolean('is_active')->default(true);
				$table->unsignedInteger('category_id')->nullable()->comment('Category this brand belongs to');
				$table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
				$table->timestamps();
				$table->softDeletes();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('brands');
		}
	};
