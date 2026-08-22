<?php

	namespace App\DataTransferObjects\Warehouse;

	use App\Models\WarehouseLocation;

	readonly class LocationDTO {
		public function __construct(
			public int $id,
			public string $code,
			public string $displayName,
			public string $coordinates,
			public array $items = [],
		) {}

		/**
		 * Create a DTO instance from a WarehouseLocation model instance, ID (int), or null.
		 */
		public static function fromModel(WarehouseLocation|int|null $location): ?self {
			// 1. Return null if no location passed
			if ($location === null) {
				return null;
			}

			// 2. Resolve model from DB when ID is supplied
			if (is_int($location)) {
				$location = WarehouseLocation::with(['inventories.product'])->find($location);

				if (!$location) {
					return null;
				}
			}

			// 3. Ensure inventories and products are eager loaded
			if (!$location->relationLoaded('inventories')) {
				$location->load(['inventories.product']);
			}

			// 4. Transform stored inventory records for UI rendering[cite: 11, 13]
			$items = $location->inventories->map(fn($inv) => [
				'product_name' => $inv->product->name ?? 'Unknown',
				'sku'          => $inv->product->sku ?? '-',
				'quantity'     => $inv->quantity,
				'batch'        => $inv->batch_number,
			])->toArray();

			// 5. Build coordinates string handling nullable zone/aisle/rack/shelf/bin columns[cite: 12]
			$coordinates = sprintf(
				"Z: %s | A: %s | R: %s | S: %s | B: %s",
				$location->zone ?? '-',
				$location->aisle ?? '-',
				$location->rack ?? '-',
				$location->shelf ?? '-',
				$location->bin ?? '-'
			);

			return new self(
				id: $location->id,
				code: $location->code,
				displayName: $location->name,
				coordinates: $coordinates,
				items: $items
			);
		}
	}