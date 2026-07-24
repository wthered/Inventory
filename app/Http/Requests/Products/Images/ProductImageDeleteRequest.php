<?php

	namespace App\Http\Requests\Products\Images;

	use App\Models\Products\ProductImage;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class ProductImageDeleteRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('delete', ProductImage::class);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			// Retrieve the product model or ID from the route parameter /products/{product}/...
			$product = $this->route('product');
			$productId = is_object($product) ? $product->id : $product;

			return [
				'image' => [
					'required',
					'string',
					// Ensures the image location exists AND belongs to this exact product_id
					Rule::exists('product_images', 'image_location')->where(function ($query) use ($productId) {
						return $query->where('product_id', $productId);
					}),
				],
			];
		}

		/**
		 * Custom messages for validation errors.
		 *
		 * @return array<string, string>
		 */
		public function messages(): array {
			return [
				'image.required' => 'An image path is required.',
				'image.exists'   => 'The specified image does not belong to this product.',
			];
		}
	}
