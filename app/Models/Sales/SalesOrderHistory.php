<?php

	namespace App\Models\Sales;

	use App\Models\User;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class SalesOrderHistory extends Model {
		use SoftDeletes;

		// Το migration σου έχει softDeletes()

		protected $table = 'sales_order_histories';

		protected $fillable = [
			'sales_order_id',
			'action',
			'event',
			'details',
			'description',
			'user_id',
		];

		protected $casts = [
			// Το migration ορίζει JSON payload για τα mutations (old/new values)
			'details' => 'array',
		];

		/**
		 * Επιστροφή στην παραγγελία (Header)
		 */
		public function sale(): BelongsTo {
			return $this->belongsTo(SalesOrder::class, 'sales_order_id');
		}

		/**
		 * Ο χρήστης (υπάλληλος) που πραγματοποίησε την ενέργεια
		 */
		public function user(): BelongsTo {
			return $this->belongsTo(User::class, 'user_id');
		}
	}