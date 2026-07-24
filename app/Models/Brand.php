<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Brand extends Model {
		use SoftDeletes;

		/**
		 * Τα πεδία που είναι διαθέσιμα για μαζική καταχώρηση (Mass Assignment).
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'name',
			'slug',
			'description',
			'logo',
			'website',
			'is_active',
		];

		/**
		 * Καθορισμός των data casts του μοντέλου (Laravel 11/12 method style).
		 *
		 * @return array<string, string>
		 */
		protected function casts(): array {
			return [
				'is_active' => 'boolean',
			];
		}

		/**
		 * Ανάκτηση του μοντέλου για route model binding,
		 * παρακάμπτοντας τα soft deletes και τα global scopes.
		 *
		 * @param  mixed        $value
		 * @param  string|null  $field
		 *
		 * @return Model
		 */
		public function resolveRouteBinding($value, $field = null): Model {
			return $this->where($field ?? $this->getRouteKeyName(), $value)
			            ->withTrashed()         // Παράκαμψη του SoftDeletes
			            ->withoutGlobalScopes() // Παράκαμψη του BrandScope (is_active)
			            ->firstOrFail();
		}

		/**
		 * Τα προϊόντα που ανήκουν σε αυτή τη μάρκα.
		 *
		 * @return HasMany<Product, $this>
		 */
		public function products(): HasMany {
			return $this->hasMany(Product::class, 'brand_id', 'id');
		}

		/**
		 * Οι κατηγορίες στις οποίες ανήκει αυτή η μάρκα.
		 *
		 * @return BelongsToMany<Category, $this>
		 */
		public function categories(): BelongsToMany {
			// ->withTimestamps() adds {created, updated}_at values in $category->brands()->sync(....)
			return $this->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id')
			            ->withTimestamps();
		}
	}
