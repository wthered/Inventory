<?php

	namespace App\Policies;

	use App\Models\Product;
	use App\Models\User;

	class ProductPolicy {
		/**
		 * Create a new policy instance.
		 */
		public function __construct() {
			//
		}

		/**
		 * Determine whether the user can update the product model.
		 */
		public function update(User $user, Product $product): bool {
			return $user->can('product.update');
		}
	}
