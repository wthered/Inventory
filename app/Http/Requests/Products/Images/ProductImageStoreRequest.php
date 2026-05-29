<?php

	namespace App\Http\Requests\Products\Images;

	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;

	class ProductImageStoreRequest extends FormRequest {
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
				'images'   => 'required|array',
				'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
			];
		}

		public function validated($key = null, $default = null) {
			return Collection::make($this->input());
		}

		public function messages(): array {
			return [
				'images.*.required' => 'Please select at least one image to upload.',
				'images.*.image'    => 'Each file must be a valid image.',
				'images.*.mimes'    => 'Images must be of type: JPEG, PNG, JPG, GIF or WEBP',
				'images.*.max'      => 'Each image must not be larger than 2 MB.',
			];
		}
	}
