<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('accounts', function (Blueprint $table) {
				$table->string('username')->primary();
				$table->string('first_name');
				$table->string('last_name');
				$table->unsignedBigInteger('phone')->nullable();
				$table->string('avatar')->nullable();
				$table->boolean('is_active')->default(true);
				$table->timestamp('last_login_at')->nullable();
				$table->timestamps();
				$table->foreign('username')->references('name')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('accounts');
		}
	};
