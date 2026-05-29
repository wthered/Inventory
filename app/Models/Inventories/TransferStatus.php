<?php

	namespace App\Models\Inventories;

	use App\Models\StockTransfer;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Carbon;

	/**
	 * Class TransferStatus
	 * This model is required for defining the lookup table that holds all
	 * possible statuses for an inventory transfer (e.g., Pending, Completed).
	 *
	 * @property int $id
	 * @property string $name
	 * @property string $slug
	 * @property bool $is_active
	 * @property Carbon $created_at
	 * @property Carbon $updated_at
	 */
	class TransferStatus extends Model {
		use softDeletes;

		// Specifies the database table name
		protected $table = 'transfer_statuses';

		protected $fillable = [
			'name',
			'slug',
		];

		/**
		 * Define the relationship: A status can be applied to many transfers.
		 */
		public function transfers(): HasMany {
			return $this->hasMany(StockTransfer::class, 'status_id');
		}
	}
