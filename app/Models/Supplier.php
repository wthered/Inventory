<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;

	class Supplier extends Model {
		public function products(): BelongsToMany {
			return $this
				->belongsToMany(Product::class, 'suppliers_products')->withPivot([
					'price',
					'lead_time_days',
					'is_preferred',
					'moq',
				]);
		}
	}
