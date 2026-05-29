<?php

	namespace App\Services\Warehouse;

	use App\Models\Warehouse;
	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Support\Str;

	class WarehouseFilterService {

		/**
		 * Provides all dropdown options for the warehouse map filters
		 */
		public function getFilterOptions(Warehouse $warehouse): array {
			return [
				'zones'   => $this->generateRange($warehouse->zones, 'Zone '),
				'aisles'  => $this->generateRange($warehouse->aisles, 'Aisle '),
				'racks'   => $this->generateRange($warehouse->racks, 'Rack '),
				'shelves' => $this->generateRange($warehouse->shelves, 'Shelf '),
			];
		}

		private function generateRange(int $end, string $prefix = ''): array {
			return collect(range(1, $end))->map(fn($i) => [
				'value' => $i,
				'text'  => $prefix . $i,
			])->toArray();
		}

		/**
		 * Applies the request filters to the Location query
		 */
		public function applyFilters(Builder $query, array $filters): Builder {
			return $query->when($filters['zone'] ?? null, function($q, $v) {
				return $q->where('zone', $v);
			})->when($filters['aisle'] ?? null, function($q, $v) {
				return $q->where('aisle', $v);
			})->when($filters['rack'] ?? null, function($q, $v) {
				return $q->where('rack', $v);
			})->when($filters['shelf'] ?? null, function($q, $v) {
				return $q->where('shelf', $v);
			});
		}
	}