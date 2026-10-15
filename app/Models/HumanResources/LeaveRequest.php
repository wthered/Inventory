<?php

	namespace App\Models\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Models\User;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class LeaveRequest extends Model {
		use HasFactory;

		protected $fillable = [
			'employee_id',
			'leave_type',
			'start_date',
			'end_date',
			'total_days',
			'reason',
			'status',
			'approved_by',
			'action_at',
		];

		protected $casts = [
			'leave_type' => LeaveType::class,
			'status'     => LeaveStatus::class,
			'start_date' => 'date',
			'end_date'   => 'date',
			'total_days' => 'decimal:1',
			'action_at'  => 'datetime',
		];

		public function employee(): BelongsTo {
			return $this->belongsTo(Employee::class);
		}

		public function approver(): BelongsTo {
			return $this->belongsTo(User::class, 'approved_by');
		}
	}
