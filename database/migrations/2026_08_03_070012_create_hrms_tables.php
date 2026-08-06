<?php

	use App\Enums\HumanResources\LeaveStatus;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			// 1. Departments
			Schema::create('departments', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name');
				$table->string('code')->unique();
				$table->text('description')->nullable();

				// Foreign Key προς τον User (Manager του τμήματος)
				$table->unsignedInteger('manager_id')->nullable();
				$table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();

				$table->timestamps();
			});

			// 2. Positions
			Schema::create('positions', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title');
				$table->unsignedInteger('department_id');
				$table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
				$table->text('description')->nullable();
				$table->timestamps();
			});

			// 3. Employees (Κεντρικός πίνακας HR)
			Schema::create('employees', function (Blueprint $table) {
				$table->increments('id');

				// Σύνδεση με User / Account
				$table->unsignedInteger('user_id')->nullable()->unique();
				$table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

				// Σύνδεση με HR & Warehouse
				$table->unsignedInteger('department_id')->nullable();
				$table->unsignedInteger('position_id')->nullable();
				$table->unsignedInteger('warehouse_id')->nullable(); // Η βασική αποθήκη του υπαλλήλου

				$table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
				$table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
				$table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();

				$table->string('employee_code')->unique(); // e.g., EMP-2026-001
				$table->string('first_name');
				$table->string('last_name');
				$table->string('phone')->nullable();
				$table->date('hire_date');
				$table->boolean('is_active')->default(true);

				$table->timestamps();
				$table->softDeletes();

				$table->index(['warehouse_id', 'department_id', 'is_active'], 'emp_wh_dept_active_idx');
			});

			// 4. Employee Details (Προσωπικά / Ευαίσθητα Στοιχεία - 1-to-1)
			Schema::create('employee_details', function (Blueprint $table) {
				$table->unsignedInteger('employee_id')->unique();
				$table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();

				$table->string('afm', 15)->nullable();
				$table->string('social_security', 16)->nullable();
				$table->string('id_card_number', 20)->nullable();
				$table->date('birth_date')->nullable();
				$table->string('address')->nullable();
				$table->string('city')->nullable();
				$table->string('postal_code', 10)->nullable();
				$table->string('iban', 34)->nullable();
				$table->string('emergency_contact_name')->nullable();
				$table->string('emergency_contact_phone')->nullable();

				$table->timestamps();
			});

			// 5. Leave Requests
			Schema::create('leave_requests', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('employee_id');
				$table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();

				$table->string('leave_type');

				$table->date('start_date');
				$table->date('end_date');
				$table->decimal('total_days', 4, 1);
				$table->text('reason')->nullable();

				// Status: e.g. pending, approved, rejected
				$table->string('status')->default(LeaveStatus::PENDING->value);

				$table->unsignedInteger('approved_by')->nullable();
				$table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
				$table->timestamp('action_at')->nullable();

				$table->timestamps();
			});

			// 6. Attendances (Παρουσιολόγιο Αποθήκης)
			Schema::create('attendances', function (Blueprint $table) {
				$table->unsignedInteger('employee_id');
				$table->unsignedInteger('warehouse_id'); // Σε ποια αποθήκη έγινε το check-in

				$table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
				$table->foreign('warehouse_id')->references('id')->on('warehouses');

				$table->date('work_date');
				$table->dateTime('check_in');
				$table->dateTime('check_out')->nullable();
				$table->decimal('overtime_hours', 4)->default(0);

				$table->timestamps();

				$table->index(['employee_id', 'work_date']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('attendances');
			Schema::dropIfExists('leave_requests');
			Schema::dropIfExists('employee_details');
			Schema::dropIfExists('employees');
			Schema::dropIfExists('positions');
			Schema::dropIfExists('departments');
		}
	};
