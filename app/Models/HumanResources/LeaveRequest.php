<?php

	namespace App\Models\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Models\User;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class LeaveRequest extends Model {
		use HasFactory, SoftDeletes;

		protected $fillable = [
			'employee_id',
			'leave_type',
			'start_date',
			'end_date',
			'total_days',
			'reason',
			'rejection_reason',
			'status',
			'approved_by',
			'approved_at',
			'action_at',
		];

		protected $casts = [
			'leave_type'  => LeaveType::class,
			'start_date'  => 'date',
			'end_date'    => 'date',
			'status'      => LeaveStatus::class,
			'total_days'  => 'integer',
			// When approved_by is cast as User, the update model fails
			'approved_by' => 'integer',
			'approved_at' => 'datetime',
			'action_at'   => 'datetime',
		];

		public function employee(): BelongsTo {
			return $this->belongsTo(Employee::class);
		}

		public function approver(): BelongsTo {
			return $this->belongsTo(User::class, 'approved_by');
		}
	}
