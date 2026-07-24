<?php

	namespace App\Http\Requests\Products\Images;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;

	class ProductImageStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			// Authorizes against standard update capability or custom policy actions
//			return Auth::check() && $this->user()->can('update', ProductImage::class);
			return true;
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'images'   => ['required', 'array', 'min:1'],
				'images.*' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
			];
		}

		/**
		 * Custom attribute names for clear validation messages.
		 *
		 * @return array<string, string>
		 */
		public function attributes(): array {
			return [
				'images'   => 'uploaded image(s)',
				'images.*' => 'image',
			];
		}

		/**
		 * Get custom messages for validator errors.
		 *
		 * @return array<string, string>
		 */
		public function messages(): array {
			return [
				'images.required'   => 'Please select at least one image file to upload.',
				'images.min'        => 'At least one image must be provided.',
				'images.*.required' => 'The selected image file cannot be empty.',
				'images.*.file'     => 'The uploaded item must be a valid file.',
				'images.*.image'    => 'The chosen file must be an image (JPEG, PNG, JPG, GIF).',
				'images.*.mimes'    => 'Only JPEG, PNG, JPG, or GIF file types are supported.',
				'images.*.max'      => 'Each image must not exceed 5MB.',
			];
		}
	}