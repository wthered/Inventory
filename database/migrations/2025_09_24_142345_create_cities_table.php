<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('cities', function (Blueprint $table) {
				$table->increments('id');

				// Link to the Countries table
				$table->unsignedInteger('country_id');
				$table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();

				$table->string('name', 255);
				$table->string('state', 255)->nullable()->comment('State, Region, or Province name');
				$table->string('postal_code', 20)->nullable()->comment('Default / Primary postal code');
				$table->boolean('is_active')->default(true);

				$table->timestamps();
				$table->softDeletes();

				$table->index(['country_id', 'name', 'is_active']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('cities');
		}
	};
