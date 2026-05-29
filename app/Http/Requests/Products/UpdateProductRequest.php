<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Validation\Rule;

	class UpdateProductRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return true;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'name'             => 'required|string|max:255',
				'sku'              => 'required|string|max:255',
				'cost_price'       => 'required|numeric|min:0',
				'selling_price'    => 'required|numeric|min:0',
				'discount_price'   => 'nullable|numeric|min:0|lte:selling_price',
				'barcode'          => 'nullable|string|max:255',
				'slug'             => 'nullable|string|max:255|unique:products,slug,'.($this->product->id ?? 'NULL'),
				'description'      => 'nullable|string|max:4096',
				'specifications'   => 'nullable|json',
				'category_id'      => 'nullable|integer|exists:categories,id',
				'brand_id'         => 'nullable|integer|exists:brands,id',
				'unit'             => 'required|string|in:kg,liter,pack,pcs',
				'min_stock_level'  => 'required_if:track_inventory,1|integer|min:0',
				'max_stock_level'  => 'nullable|integer|min:0|gte:min_stock_level',
				'reorder_point'    => 'required_if:track_inventory,1|integer|min:0',
				'track_inventory'  => 'nullable|in:on,',
				'is_active'        => 'nullable|in:on,',
				'parent_category'  => Rule::exists('categories', 'id')
					->whereNull('parent_id'),
				'child_category'   => Rule::exists('categories', 'id')
					->where('parent_id', $this->input('parent_category')),
				'current_stock'    => [
					'integer',
					'min:0'
				],
				'product_supplier' => [
					'required',
					'integer',
					Rule::exists('suppliers', 'id'),
				],
				'images.*'         => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
			];
		}

		public function messages(): array {
			return [
				'name.required' => 'The product name is required.',
				'name.string'   => 'The product name must be a valid string.',
				'name.max'      => 'The product name may not exceed 255 characters.',

				'sku.required' => 'The SKU is required.',
				'sku.string'   => 'The SKU must be a valid string.',
				'sku.max'      => 'The SKU may not exceed 255 characters.',

				'cost_price.required' => 'The cost price is required.',
				'cost_price.numeric'  => 'The cost price must be a valid number.',
				'cost_price.min'      => 'The cost price must be at least 0.',

				'selling_price.required' => 'The selling price is required.',
				'selling_price.numeric'  => 'The selling price must be a valid number.',
				'selling_price.min'      => 'The selling price must be at least 0.',

				'discount_price.numeric' => 'The discount price must be a valid number.',
				'discount_price.min'     => 'The discount price must be at least 0.',

				'description.string' => 'The description must be text.',
				'description.max'    => 'The description may not exceed 1000 characters.',

				'specifications.json' => 'The specifications field must contain valid JSON data.',

				'barcode.string' => 'The barcode must be a valid string.',
				'barcode.max'    => 'The barcode may not exceed 255 characters.',

				'category_id.integer' => 'The category must be a valid ID.',
				'category_id.exists'  => 'The selected category does not exist.',

				'brand_id.integer' => 'The brand must be a valid ID.',
				'brand_id.exists'  => 'The selected brand does not exist.',

				'unit.required' => 'The unit is required.',
				'unit.string'   => 'The unit must be a valid string.',
				'unit.in'       => 'The unit must be one of the following: kg, liter, pack, pcs.',

				'min_stock_level.required' => 'The minimum stock level is required.',
				'min_stock_level.integer'  => 'The minimum stock level must be a whole number.',
				'min_stock_level.min'      => 'The minimum stock level cannot be negative.',

				'max_stock_level.integer' => 'The maximum stock level must be a whole number.',
				'max_stock_level.min'     => 'The maximum stock level cannot be negative.',
				'max_stock_level.gte'     => 'The maximum stock level must be greater than or equal to the minimum stock level.',

				'reorder_point.required' => 'The reorder point is required.',
				'reorder_point.integer'  => 'The reorder point must be a whole number.',
				'reorder_point.min'      => 'The reorder point cannot be negative.',

				'track_inventory.boolean' => 'The track inventory field must be true or false.',
				'is_active.boolean'       => 'The activation status must be activated or disabled.',

				'images.*.image' => 'Each file must be a valid image.',
				'images.*.mimes' => 'Images must be in one of the following formats: JPG, JPEG, PNG, GIF, or WEBP.',
				'images.*.max'   => 'Each image may not be larger than 5MB.',
			];
		}

		public function validated($key = null, $default = null) {
			return Collection::make($this->input());
		}
	}
