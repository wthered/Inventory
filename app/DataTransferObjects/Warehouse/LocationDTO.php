<?php

	// LocationDTO.php refactor
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

		// LocationDTO.php
		public static function fromModel(WarehouseLocation $location): self {
			// Φιλτράρουμε τα δεδομένα για να περάσουμε μόνο ό,τι χρειάζεται το UI
			$items = $location->inventories->map(fn($inv) => [
				'product_name' => $inv->product->name ?? 'Unknown',
				'sku'          => $inv->product->sku ?? '-',
				'quantity'     => $inv->quantity,
				'batch'        => $inv->batch_number,
			])->toArray();

			return new self(
				id: $location->id,
				code: $location->code,
				displayName: $location->name,
				coordinates: sprintf("Z: %s | A: %s | R: %d", $location->zone, $location->aisle, $location->rack),
				items: $items
			);
		}
	}