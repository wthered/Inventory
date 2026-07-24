<?php

	namespace App\Http\Requests\Brands;

	use App\Models\Brand;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Str;

	class BrandUpdateRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('update', Brand::class);
		}

		/**
		 * 1. PREPARE FOR VALIDATION
		 * Εκτελείται ΠΡΙΝ ελεγχθούν οι κανόνες. Καθαρίζει και προετοιμάζει τα inputs.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				// Αν το checkbox is_active δεν πατήθηκε, εξαναγκάζουμε την τιμή να είναι 0 αντί για null
				'is_active' => $this->has('is_active'),

				// Δημιουργούμε αυτόματα ένα καθαρό URL slug από το όνομα αν δεν έχει πληκτρολογηθεί ήδη κάτι custom
				'slug'      => Str::slug($this->input('slug') ?: $this->input('name')),
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			// Παίρνουμε το id του brand από το route (π.χ. /brands/{brand}) για να το εξαιρέσουμε από το unique check του slug
			$brandId = $this->route('brand')?->id ?? $this->route('brand');

			return [
				'name'         => ['required', 'string', 'max:255'],
				'slug'         => ['required', 'string', 'max:255', 'unique:brands,slug,'.$brandId],
				'website'      => ['nullable', 'url', 'max:255'],
				'description'  => ['nullable', 'string'],
				'logo'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'], // Μέγιστο 4MB
				'is_active'    => ['required', 'boolean'],
				'categories'   => ['nullable', 'array'],
				'categories.*' => ['exists:categories,id'], // Κάθε id πρέπει να υπάρχει στον πίνακα categories
			];
		}

		/**
		 * Custom μηνύματα σφαλμάτων στα ελληνικά.
		 */
		public function messages(): array {
			return [
				'name.required'       => 'Το όνομα του brand είναι υποχρεωτικό.',
				'slug.unique'         => 'Αυτό το URL slug χρησιμοποιείται ήδη από άλλο brand.',
				'website.url'         => 'Η διεύθυνση ιστοσελίδας δεν είναι έγκυρη.',
				'logo.image'          => 'Το αρχείο πρέπει να είναι εικόνα.',
				'logo.max'            => 'Το λογότυπο δεν μπορεί να ξεπερνά τα 4MB.',
				'categories.*.exists' => 'Μία ή περισσότερες από τις επιλεγμένες κατηγορίες δεν είναι έγκυρες.',
			];
		}

		/**
		 * 2. PASSED VALIDATION
		 * Εκτελείται ΑΦΟΥ πετύχει το validation. Καθαρίζει tags για προστασία από XSS.
		 */
		protected function passedValidation(): void {
			if ($this->filled('description')) {
				$this->merge([
					'description' => strip_tags($this->input('description'))
				]);
			}
		}
	}
