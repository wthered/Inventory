<?php

	use App\Enums\Financial\TransactionStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('payments', function (Blueprint $table) {
				$table->increments('id')->comment('Primary key: unique payment transaction ID');

				// Μοναδικός Αριθμός Παραστατικού Πληρωμής / Είσπραξης (π.χ. PAY-2026-0001 / REC-2026-0001)
				$table->string('payment_number')->unique()->comment('Unique payment receipt or reference code');

				// Τύπος Συναλλαγής: Inflow (Είσπραξη από Πελάτη) ή Outflow (Πληρωμή σε Προμηθευτή)
				$table->boolean('is_incoming')->comment('Financial direction: inflow (customer payment) or outflow (supplier payout)');

				// Polymorphic Relationship: Σύνδεση είτε με SalesOrder, είτε με PurchaseOrder (ή απευθείας Customer/Supplier)
				// Παράγει τα: payable_type (string) & payable_id (unsignedInteger)
				$table->nullableMorphs('payable');

				// Σύνδεση με τον Πελάτη ή τον Προμηθευτή (για γρήγορα queries & υπόλοιπα)
				$table->unsignedInteger('customer_id')->nullable()->comment('Linked customer if customer payment');
				$table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();

				$table->unsignedInteger('supplier_id')->nullable()->comment('Linked supplier if vendor payment');
				$table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();

				// Οικονομικά στοιχεία
				$table->decimal('amount', 15, 2)->comment('Monetary amount processed in this transaction');
				$table->string('currency', 3)->default('EUR')->comment('ISO currency code (e.g., EUR, USD)');

				// Τρόπος Πληρωμής & Κατάσταση
				$table->string('payment_method')->default('bank_transfer')->comment('Method used: cash, bank_transfer, credit_card, cheque');
				$table->unsignedInteger('status')->default(TransactionStatus::PENDING)->comment('Transaction status: pending, completed, failed, refunded, reconciled');

				// Ημερομηνίες & Στοιχεία Τράπεζας
				$table->timestamp('paid_at')->comment('Timestamp when the funds were transferred/received');
				$table->string('transaction_reference')->nullable()->comment('Bank transaction reference / Check number / POS approval code');

				// Λογοδοσία (Ποιος υπάλληλος καταχώρησε την πληρωμή)
				$table->unsignedInteger('created_by')->comment('User ID who executed or registered the payment');
				$table->foreign('created_by')->references('id')->on('users');

				$table->text('notes')->nullable()->comment('Internal accounting remarks or comments');
				$table->timestamps();
				$table->softDeletes();

				// Indexes για γρήγορα reports & audits
				$table->index(['type', 'status', 'paid_at'], 'payments_type_status_date_idx');
				$table->comment('Master ledger for payments, handling customer inflows, supplier payouts, and payment method trackings.');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('payments');
		}
	};
