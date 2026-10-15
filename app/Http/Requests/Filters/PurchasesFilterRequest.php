<?php

	namespace App\Http\Requests\Filters;

	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Models\Purchases\PurchaseOrder;
	use Carbon\Carbon;
	use Illuminate\Contracts\Validation\ValidationRule;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class PurchasesFilterRequest extends FormRequest {
		/**
		 * Determine if the user is authorized to make this request.
		 */
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('view', PurchaseOrder::class);
		}

		/**
		 * Prepare the data for validation.
		 * Trims strings and strips empty string values to null.
		 */
		protected function prepareForValidation(): void {
			$this->merge([
				'search' => $this->filled('search') ? trim($this->input('search')) : null,
				'status' => $this->filled('status') ? intval($this->input('status')) : null,
				'order'  => $this->filled('order') ? $this->input('order') : null,
			]);
		}

		/**
		 * Get the validation rules that apply to the request.
		 *
		 * @return array<string, ValidationRule|array|string>
		 */
		public function rules(): array {
			return [
				'search' => ['nullable', 'string', 'max:255'],
				'status' => ['nullable', Rule::enum(PurchaseOrderStatus::class)],
				'order'  => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:today'],
			];
		}

		/**
		 * Custom error messages for validation failures.
		 */
		public function messages(): array {
			return [
				'search.max'             => 'The search term cannot exceed 255 characters.',
				'status.enum'            => 'The selected purchase order status is invalid.',
				'order.date_format'      => 'The date must be formatted as YYYY-MM-DD.',
				'order.after_or_equal'   => 'The order date cannot be earlier than 2000-01-01.',
				'order.before_or_equal'  => 'The order date cannot be in the future.',
			];
		}

		/**
		 * Handle actions after validation passes.
		 */
		protected function passedValidation(): void {
			// Example: Merge default fallback sorting parameters if needed by your query
			$this->merge([
				'per_page' => $this->input('per_page', $this->session()->get('per_page', 15)),
			]);
		}
	}
