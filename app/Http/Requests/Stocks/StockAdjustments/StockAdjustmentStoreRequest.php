<?php

	namespace App\Http\Requests\Stocks\StockAdjustments;

	use App\Enums\Inventory\AdjustmentReason;
	use App\Enums\Inventory\AdjustmentType;
	use App\Models\StockAdjustment;
	use Carbon\Carbon;
	use Illuminate\Foundation\Http\FormRequest;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Validation\Rule;

	class StockAdjustmentStoreRequest extends FormRequest {
		public function authorize(): bool {
			return Auth::check() && $this->user()->can('create', StockAdjustment::class);
		}

		public function rules(): array {
			return [
				'warehouse_id'        => ['required', 'integer', 'exists:warehouses,id'],
				'adjustment_date'     => ['required', 'date'],
				'notes'               => ['nullable', 'string', 'max:1000'],
				'items'               => ['required', 'array', 'min:1'],
				'items.*.product_id'  => ['required', 'integer', 'distinct', 'exists:products,id'],
				'items.*.location_id' => ['nullable', 'integer', 'exists:warehouse_locations,id'],
				'items.*.type'        => ['required', 'string', Rule::enum(AdjustmentType::class)],
				'items.*.quantity'    => ['required', 'integer', 'min:1'],
				'items.*.reason'      => ['required', Rule::enum(AdjustmentReason::class)],
			];
		}

		public function messages(): array {
			return [
				'warehouse_id.required'       => 'Πρέπει να επιλέξετε αποθήκη.',
				'items.required'              => 'Πρέπει να προσθέσετε τουλάχιστον ένα προϊόν.',
				'items.min'                   => 'Πρέπει να προσθέσετε τουλάχιστον ένα προϊόν.',
				'items.*.product_id.required' => 'Πρέπει να επιλέξετε προϊόν.',
				'items.*.product_id.exists'   => 'Το προϊόν που επιλέξατε δεν υπάρχει.',
				'items.*.quantity.min'        => 'Η ποσότητα πρέπει να είναι τουλάχιστον 1.',
				'items.*.reason.enum'         => 'Η αιτιολογία δεν είναι έγκυρη.',
			];
		}

		protected function passedValidation(): void {
			$items = Collection::make($this->validated('items', []))->map(function ($item) {
				return [
					'product_id'  => (int) $item['product_id'],
					'location_id' => !empty($item['location_id']) ? (int) $item['location_id'] : null,
					'type'        => $item['type'],
					'quantity'    => (int) $item['quantity'],
					'reason'      => $item['reason'],
				];
			})->toArray();

			$this->replace([
				'warehouse_id'    => (int) $this->input('warehouse_id'),
				'adjustment_date' => Carbon::parse($this->input('adjustment_date')),
				'notes'           => $this->filled('notes') ? trim($this->input('notes')) : null,
				'items'           => $items,
			]);
		}

		protected function prepareForValidation(): void {
			// Sanitize datetime format string to HTML datetime-local format if needed
			if ($this->filled('adjustment_date')) {
				$this->merge([
					'warehouse_id' => intval($this->input('warehouse_id')),
				]);
			}
		}
	}