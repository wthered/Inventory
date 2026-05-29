<?php

	namespace App\Models\Products;

	use App\Models\User;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class ProductHistory extends Model {

		public    $timestamps = true;
		protected $table      = 'product_history';

		// Βέλτιστη Πρακτική: Δεν ενημερώνουμε το updated_at σε πίνακες ιστορικού.
		protected $fillable = [
			'product_id',
			'user_id',
			'action',
			'details',
			'ip_address',
			'reference',
		];

		protected $casts = [
			'details'    => 'array',
		];

		/**
		 * Επιστρέφει τον χρήστη που πραγματοποίησε την ενέργεια.
		 */
		public function user(): BelongsTo {
			// Υποθέτουμε ότι το μοντέλο User βρίσκεται στο App\Models\User
			return $this->belongsTo(User::class);
		}
	}
