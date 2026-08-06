<?php

	namespace App\Http\Requests\Products;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Validation\Rule;

	class ProductUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 * Reads ProductPolicy for authorization.
		 */
		public function authorize(): bool {
			return $this->user()?->can('update', $this->route('product')) ?? false;
		}

		/**
		 * Prepare inputs for validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'specifications' => $this->filled('specifications') && $this->input('specifications') !== '&hellip;'
					? $this->input('specifications')
					: null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			$productId = $this->route('product')?->id ?? $this->product->id ?? null;

			return [
				'name' => ['required', 'string', 'max:255'],
				'sku'  => ['required', 'string', 'max:255'],

				'cost_price'     => ['required', 'numeric', 'min:0'],
				'selling_price'  => ['required', 'numeric', 'min:0'],
				'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:selling_price'],

				'barcode'     => ['nullable', 'string', 'max:255'],
				'slug'        => [
					'nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)
				],
				'description' => ['nullable', 'string', 'max:4096'],

				'specifications' => ['nullable', 'json'],

				'parent_category' => [
					'nullable',
					Rule::exists('categories', 'id')->whereNull('parent_id'),
				],
				'child_category'  => [
					'nullable',
					Rule::exists('categories', 'id')->where('parent_id', $this->input('parent_category')),
				],

				'brand_id' => [
					'nullable',
					'integer',
					Rule::exists('brands', 'id'),
					Rule::exists('brand_category', 'brand_id')->where('category_id', $this->input('child_category')),
				],

				'unit'            => ['required', 'string', Rule::in(['kg', 'liter', 'pack', 'pcs'])],
				'min_stock_level' => ['required_if:track_inventory,1', 'integer', 'min:0'],
				'max_stock_level' => ['nullable', 'integer', 'min:0', 'gte:min_stock_level'],
				'reorder_point'   => ['required_if:track_inventory,1', 'integer', 'min:0'],

				'track_inventory' => ['nullable', Rule::in(['on', '1', true])],
				'is_active'       => ['nullable', Rule::in(['on', '1', true])],

				'current_stock' => ['nullable', 'integer', 'min:0'],

				'product_supplier' => [
					'required',
					'integer',
					Rule::exists('suppliers', 'id'),
				],

				'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
			];
		}

		/**
		 * Get custom error messages for validator errors.
		 */
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
				'description.max'    => 'The description may not exceed 4096 characters.',

				'specifications.json' => 'The specifications field must contain valid JSON data.',

				'barcode.string' => 'The barcode must be a valid string.',
				'barcode.max'    => 'The barcode may not exceed 255 characters.',

				'parent_category.exists' => 'The selected parent category is invalid.',
				'child_category.exists'  => 'The selected subcategory is invalid for the chosen parent category.',

				'brand_id.integer' => 'The brand must be a valid ID.',
				'brand_id.exists'  => 'The selected brand is invalid or not associated with the selected subcategory.',

				'unit.required' => 'The unit is required.',
				'unit.string'   => 'The unit must be a valid string.',
				'unit.in'       => 'The unit must be one of the following: kg, liter, pack, pcs.',

				'min_stock_level.required_if' => 'The minimum stock level is required when tracking inventory.',
				'min_stock_level.integer'     => 'The minimum stock level must be a whole number.',
				'min_stock_level.min'         => 'The minimum stock level cannot be negative.',

				'max_stock_level.integer' => 'The maximum stock level must be a whole number.',
				'max_stock_level.min'     => 'The maximum stock level cannot be negative.',
				'max_stock_level.gte'     => 'The maximum stock level must be greater than or equal to the minimum stock level.',

				'reorder_point.required_if' => 'The reorder point is required when tracking inventory.',
				'reorder_point.integer'     => 'The reorder point must be a whole number.',
				'reorder_point.min'         => 'The reorder point cannot be negative.',

				'images.*.image' => 'Each file must be a valid image.',
				'images.*.mimes' => 'Images must be in one of the following formats: JPG, JPEG, PNG, GIF, or WEBP.',
				'images.*.max'   => 'Each image may not be larger than 5MB.',
			];
		}

		/**
		 * Handle a passed validation attempt.
		 *
		 * Transform or structure the validated data before it reaches the controller.
		 */
		protected function passedValidation(): void {
			$validated = $this->validator->getData();

			// 1. Normalize checkboxes into boolean values
			$validated['track_inventory'] = $this->has('track_inventory');
			$validated['is_active'] = $this->has('is_active');

			// 2. Map form input names to DB column attributes
			if (isset($validated['child_category'])) {
				$validated['category_id'] = $validated['child_category'];
				unset($validated['child_category'], $validated['parent_category']);
			}

			if (isset($validated['product_supplier'])) {
				$validated['supplier_id'] = $validated['product_supplier'];
				unset($validated['product_supplier']);
			}

			// 3. Decode JSON string into PHP array (or set null)
			if (!empty($validated['specifications']) && $validated['specifications'] !== '&hellip;') {
				$validated['specifications'] = is_string($validated['specifications'])
					? json_decode($validated['specifications'], true)
					: $validated['specifications'];
			} else {
				$validated['specifications'] = null;
			}

			// Replace the validated array in the validator instance
			$this->validator->setData(array_merge($this->validator->getData(), $validated));
		}
	}