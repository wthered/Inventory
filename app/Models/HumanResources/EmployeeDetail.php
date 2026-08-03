<?php

	namespace App\Models\HumanResources;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class EmployeeDetail extends Model {
		use HasFactory;

		protected $fillable = [
			'employee_id',
			'afm',
			'amka',
			'id_card_number',
			'birth_date',
			'address',
			'city',
			'postal_code',
			'iban',
			'emergency_contact_name',
			'emergency_contact_phone',
		];

		protected $casts = [
			'birth_date' => 'date',
		];

		/**
		 * Get the employee that owns these details.
		 */
		public function employee(): BelongsTo {
			return $this->belongsTo(Employee::class);
		}
	}
