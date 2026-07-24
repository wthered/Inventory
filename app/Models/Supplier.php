<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;

	class Supplier extends Model {
		use HasFactory;

		public function products(): BelongsToMany {
			return $this
				->belongsToMany(Product::class, 'product_supplier')->withPivot([
					'price',
					'lead_time_days',
					'is_preferred',
					'moq',
				]);
		}
	}
