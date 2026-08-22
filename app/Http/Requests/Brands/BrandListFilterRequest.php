<?php

	namespace App\Http\Requests\Brands;

	use App\Models\Brand;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class BrandListFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('viewAny', Brand::class);
		}

		/**
		 * Sanitize and format input parameters prior to validation.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'search'             => $this->input('search') ? trim($this->input('search')) : null,
				'parent_category_id' => $this->input('parent_category_id') ? (int) $this->input('parent_category_id') : null,
				'category_id'        => $this->input('category_id') ? (int) $this->input('category_id') : null,
				'warehouse_id'       => $this->input('warehouse_id') ? (int) $this->input('warehouse_id') : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'category_id'        => [
					'required',
					'integer',
					Rule::exists('categories', 'id')->whereNotNull('parent_id'),
				],
				'parent_category_id' => [
					'nullable',
					'integer',
					Rule::exists('categories', 'id')->whereNull('parent_id'),
				],
				'search'             => [
					'nullable',
					'string',
					'max:255',
				],
				'brand_id'           => [
					'nullable',
					'integer',
					Rule::exists('brands', 'id'),
				],
				'warehouse_id'       => [
					'nullable',
					'integer',
					Rule::exists('warehouses', 'id'),
				]
			];
		}

		/**
		 * Custom validation error messages in Greek.
		 */
		public function messages(): array {
			return [
				'category_id.required'       => 'Η υποκατηγορία είναι υποχρεωτική.',
				'category_id.integer'        => 'Το αναγνωριστικό υποκατηγορίας πρέπει να είναι ακέραιος αριθμός.',
				'category_id.exists'         => 'Η επιλεγμένη υποκατηγορία δεν βρέθηκε στο σύστημα.',
				'parent_category_id.integer' => 'Το αναγνωριστικό γονικής κατηγορίας πρέπει να είναι ακέραιος αριθμός.',
				'parent_category_id.exists'  => 'Η επιλεγμένη γονική κατηγορία δεν είναι έγκυρη.',
				'search.string'              => 'Το πεδίο αναζήτησης πρέπει να είναι αλφαριθμητικό.',
				'search.max'                 => 'Η αναζήτηση δεν μπορεί να υπερβαίνει τους 255 χαρακτήρες.',
			];
		}
	}
