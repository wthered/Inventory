<?php

	namespace App\Services\Warehouse;

	use App\Models\Warehouse;
	use Illuminate\Support\Collection;

	class WarehouseLayoutService {
		public function getLayoutOptions(Warehouse $warehouse): array {
			return [
				'zone'  => $this->createOptions($warehouse->zones, 'Z'),
				'aisle' => $this->createOptions($warehouse->aisles, 'A'),
				'rack'  => $this->createOptions($warehouse->racks),
				'shelf' => $this->createOptions($warehouse->shelves),
				'bin'   => $this->createOptions($warehouse->bins),
			];
		}

		public function createOptions(int $count, string $prefix = ''): Collection {
			return Collection::range(1, $count)->map(fn($item) => [
				'value' => $prefix . $item,
				'text'  => $prefix . $item
			]);
		}
	}