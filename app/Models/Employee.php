<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\Relations\HasOne;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Employee extends Model {
		use SoftDeletes;

		protected $fillable = [
			'user_id',
			'department_id',
			'position_id',
			'warehouse_id',
			'employee_code',
			'first_name',
			'last_name',
			'phone',
			'hire_date',
			'is_active',
		];

		protected $casts = [
			'hire_date' => 'date',
			'is_active' => 'boolean',
		];

		/**
		 * Get the employee's full name.
		 */
		public function getFullNameAttribute(): string {
			return $this->first_name." ".$this->last_name;
		}

		// --- Σχέσεις ---

		public function user(): BelongsTo {
			return $this->belongsTo(User::class);
		}

		public function department(): BelongsTo {
			return $this->belongsTo(Department::class);
		}

		public function position(): BelongsTo {
			return $this->belongsTo(Position::class);
		}

		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}

		public function detail(): HasOne {
			return $this->hasOne(EmployeeDetail::class);
		}

		public function leaveRequests(): HasMany {
			return $this->hasMany(LeaveRequest::class);
		}

		public function attendances(): HasMany {
			return $this->hasMany(Attendance::class);
		}
	}
