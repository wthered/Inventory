<?php

	namespace App\Http\Requests\Products;

	use App\DataTransferObjects\ProductDTO;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;

	class ProductInformationRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check();
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'product' => [
					'required',
					'integer',
					'exists:products,id'
				],
			];
		}

		public function passedValidation(): Collection {
			$data = Collection::make($this->validated());

			$product = intval($data->get('product'));

			return Collection::make([
				'product' => new ProductDTO($product),
			]);
		}

		/**
		 * Get custom messages for validator errors.
		 */
		public function messages(): array {
			return [
				'productId.required' => 'Product ID is required.',
				'productId.integer'  => 'Product ID must be a valid integer.',
				'productId.min'      => 'Product ID must be a positive number.',
				'productId.exists'   => 'The specified product does not exist.',
			];
		}

		/**
		 * Prepare the data for validation.
		 */
		protected function prepareForValidation(): void {
			// Get the productId from route and add it to request data for validation
			$this->merge([
				'product' => $this->route('product'),
			]);
		}
	}
