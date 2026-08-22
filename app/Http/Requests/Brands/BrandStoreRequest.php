<?php

	namespace App\Http\Requests\Brands;

	use App\Models\Brand;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;
	use Illuminate\Validation\Rule;

	class BrandStoreRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', Brand::class);
		}

		/**
		 * Prepare data before validation runs.
		 * Useful for sanitizing strings, setting defaults, or formatting input.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				// Trim whitespace from string inputs
				'name'        => $this->input('name') ? trim($this->input('name')) : null,
				'website'     => $this->input('website') ? trim($this->input('website')) : null,
				'description' => $this->input('description') ? trim($this->input('description')) : null,

				// Ensure is_active is cast to a strict boolean
				'is_active'   => $this->boolean('is_active'),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 */
		public function rules(): array {
			return [
				'name'        => [
					'required',
					'string',
					'max:255',
					'unique:brands,name',
				],
				'website'     => [
					'nullable',
					'url',
					'max:255',
				],
				'logo'        => [
					'nullable',
					'image',
					'mimes:jpeg,png,jpg,gif,svg,webp',
					'max:2048',
				],
				'category_id' => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->whereNotNull('parent_id'),
				],
				'description' => [
					'nullable',
					'string',
				],
				'is_active'   => [
					'required',
					'boolean',
				],
			];
		}

		/**
		 * Actions to perform after validation passes.
		 * Useful for mutating validated data before it reaches the controller.
		 */
		protected function passedValidation(): void {
			// Example: Automatically generate a slug from the brand name if needed
			$this->merge([
				'slug' => Str::slug($this->validated('name')),
			]);
		}

		/**
		 * Custom validation error messages in Greek.
		 */
		public function messages(): array {
			return [
				'name.required'        => 'Το όνομα του brand είναι υποχρεωτικό.',
				'name.unique'          => 'Το brand με αυτό το όνομα υπάρχει ήδη στο σύστημα.',
				'name.max'             => 'Το όνομα του brand δεν μπορεί να υπερβαίνει τους 255 χαρακτήρες.',
				'website.url'          => 'Η διεύθυνση ιστοσελίδας πρέπει να είναι έγκυρο URL (π.χ. https://example.com).',
				'website.max'          => 'Η διεύθυνση ιστοσελίδας δεν μπορεί να υπερβαίνει τους 255 χαρακτήρες.',
				'logo.image'           => 'Το αρχείο λογότυπου πρέπει να είναι εικόνα.',
				'logo.mimes'           => 'Το λογότυπο πρέπει να είναι αρχείο τύπου: jpeg, png, jpg, gif, svg, webp.',
				'logo.max'             => 'Το μέγεθος του λογότυπου δεν πρέπει να ξεπερνά τα 2MB.',
				'category_id.required' => 'Παρακαλούμε επιλέξτε μια υποκατηγορία.',
				'category_id.integer'  => 'Η επιλεγμένη κατηγορία δεν είναι έγκυρη.',
				'category_id.exists'   => 'Η επιλεγμένη κατηγορία δεν βρέθηκε ή δεν είναι έγκυρη υποκατηγορία.',
			];
		}
	}
