<?php

	namespace App\Models\HumanResources;

	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class Attendance extends Model {
		use HasFactory;

		protected $fillable = [
			'employee_id',
			'warehouse_id',
			'work_date',
			'check_in',
			'check_out',
			'overtime_hours',
		];

		protected $casts = [
			'work_date'      => 'date',
			'check_in'       => 'datetime',
			'check_out'      => 'datetime',
			'overtime_hours' => 'decimal:2',
		];

		/**
		 * Ο εργαζόμενος στον οποίο ανήκει η καταγραφή παρουσίας.
		 */
		public function employee(): BelongsTo {
			return $this->belongsTo(Employee::class);
		}

		/**
		 * Η αποθήκη στην οποία πραγματοποιήθηκε το check-in.
		 */
		public function warehouse(): BelongsTo {
			return $this->belongsTo(Warehouse::class);
		}
	}
