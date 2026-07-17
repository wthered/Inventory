<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Category extends Model {
		use softDeletes;

		public function products(): HasMany {
			return $this->hasMany(Product::class);
		}

		/**
		 * Οι μάρκες που ανήκουν σε αυτή την κατηγορία.
		 */
		public function brands(): BelongsToMany {
			return $this->belongsToMany(Brand::class, 'brand_category', 'category_id', 'brand_id');
		}

		/**
		 * Οι υποκατηγορίες που ανήκουν σε αυτή την κατηγορία.
		 */
		public function children(): HasMany {
			return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
		}

		/**
		 * Η γονική κατηγορία στην οποία ανήκει αυτή.
		 */
		public function parent(): BelongsTo {
			return $this->belongsTo(Category::class, 'parent_id');
		}
	}
