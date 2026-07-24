<?php

	namespace App\Observers\Categories;

	use App\Models\Category;
	use Illuminate\Support\Str;

	class CategoryObserver {
		/**
		 * Handle the Category "saving" event.
		 */
		public function saving(Category $category): void {
			// 1. Generate slug automatically if it's new or the name changed
			if (!$category->exists || $category->isDirty('name')) {
				$category->slug = Str::slug($category->name);
			}

			// 2. Handle your parent structural sequencing automatically
			if (!$category->exists || $category->isDirty('parent_id')) {

				$query = Category::query();

				if (is_null($category->parent_id)) {
					$query->whereNull('parent_id');
				} else {
					$query->where('parent_id', $category->parent_id);
				}

				$maxSortOrder = $query->max('sort_order');

				$category->sort_order = is_null($maxSortOrder) ? 0 : ($maxSortOrder + 1);
			}
		}
	}