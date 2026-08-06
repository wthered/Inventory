<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Category extends Model {
		use SoftDeletes;

		protected $fillable = [
			'name',
			'slug',
			'description',
			'parent_id',
			'image',
			'sort_order',
			'is_active',
		];

		public function products(): HasMany {
			return $this->hasMany(Product::class);
		}

		public function brands(): BelongsToMany {
			// ->withTimestamps() adds {created, updated}_at values in $category->brands()->sync(....)
			return $this->belongsToMany(Brand::class, 'brand_category', 'category_id', 'brand_id')->withTimestamps();
		}

		public function children(): HasMany {
			// in CategoryGlobalScope, I already define a default orderBy
			return $this->hasMany(Category::class, 'parent_id');
		}

		public function parent(): BelongsTo {
			return $this->belongsTo(Category::class, 'parent_id');
		}
	}
