<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('countries', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name', 255);
				$table->string('code', 2)->unique()->comment('ISO 3166-1 alpha-2 code (e.g. GR, US)');
				$table->string('code_alpha', 3)->nullable()->unique()->comment('ISO 3166-1 alpha-3 code (e.g. GRC, USA)');
				$table->string('phone_code', 10)->nullable()->comment('International calling code (e.g. +30)');
				$table->boolean('is_active')->default(true);
				$table->timestamps();
				$table->softDeletes();

				$table->index(['code', 'is_active']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('countries');
		}
	};
