<?php

	use App\Enums\Customers\CustomerType;
	use App\Enums\Financial\PaymentTerms;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('customers', function (Blueprint $table) {
				$table->increments('id');
				$table->string('code')->unique();
				$table->string('name');
				$table->string('email')->nullable();
				$table->string('phone');
				$table->string('company_name')->nullable();
				$table->string('tax_number')->nullable()->comment('ΑΦΜ πελάτη');
				$table->text('billing_address')->nullable();
				$table->text('shipping_address')->nullable();
				$table->unsignedInteger('city_id')->nullable();
				$table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
				$table->string('state')->nullable();
				$table->unsignedInteger('country_id')->nullable();
				$table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
				$table->string('postal_code')->nullable();
				$table->string('customer_type')->default(CustomerType::INDIVIDUAL->value);
				$table->decimal('credit_limit', 12)->default(0);
				$table->string('payment_terms')->default(PaymentTerms::CASH->value);
				$table->text('notes')->nullable();
				$table->boolean('is_active')->default(true);
				$table->timestamps();
				$table->softDeletes();

				$table->index([
					'code',
					'is_active'
				]);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('customers');
		}
	};
