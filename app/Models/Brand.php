<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class Brand extends Model {
		/**
		 * Τα προϊόντα που ανήκουν σε αυτή τη μάρκα.
		 */
		public function products(): Brand|HasMany {
			return $this->hasMany(Product::class, 'brand_id', 'id');
		}

		/**
		 * Οι κατηγορίες στις οποίες ανήκει αυτή η μάρκα.
		 */
		public function categories(): BelongsToMany {
			return $this
				->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id')
				->withTimestamps();
		}
	}
