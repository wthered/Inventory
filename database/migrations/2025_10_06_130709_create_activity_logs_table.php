<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('activity_logs', function (Blueprint $table) {
				$table->increments('id');
				$table->string('log_name')->nullable();
				$table->text('description');
				$table->nullableMorphs('subject', 'subject');
				$table->nullableMorphs('causer', 'causer');
				$table->json('properties')->nullable();
				$table->string('event')->nullable();
				$table->string('ip_address', 45)->nullable();
				$table->string('user_agent')->nullable();
				$table->timestamps();

				$table->index([
					'log_name',
					'created_at'
				]);
				$table->index([
					'causer_type',
					'causer_id'
				], 'activity_logs_causer');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('activity_logs');
		}
	};
