<?php

	namespace App\DataTransferObjects\Warehouse;

	use App\DataTransferObjects\UserDTO;
	use App\Enums\WarehouseType;
	use App\Models\Warehouse;
	use App\Models\WarehouseLocation;
	use Illuminate\Support\Collection;
	use Carbon\Carbon;

	readonly class WarehouseDTO {
		public function __construct(
			public int $id,
			public string $code,
			public string $name,
			public WarehouseType $type,
			public ?string $description,
			public ?string $address,
			public ?string $city,
			public ?string $postal_code,
			public ?string $country,
			public string $fullAddress,
			public ?string $phone,
			public ?string $email,
			public bool $isPrimary,
			public bool $is_active,
			public bool $under_maintenance,
			public float $capacity,
			public float $current_capacity,
			public int $totalItems,
			public float $occupancyPercentage,
			public Collection $locations,
			public UserDTO $manager,
			public Carbon $created_at,    // Χρησιμοποιούμε Carbon για να δουλεύει το ->format()
			public Carbon $updated_at,    // Χρησιμοποιούμε Carbon για το ->diffForHumans()
		) {

		}

		public static function fromModel(Warehouse $warehouse): self {
			$capacity = (float) ($warehouse->capacity ?? 0);
			$current = (float) ($warehouse->current_capacity ?? 0);
			$occupancy = $capacity > 0 ? min(100, ($current / $capacity) * 100) : 0;

			return new self(
				id: $warehouse->id,
				code: $warehouse->code,
				name: $warehouse->name,
				type: $warehouse->type,
				description: $warehouse->description,
				address: $warehouse->address,
				city: $warehouse->city,
				postal_code: $warehouse->postal_code,
				country: $warehouse->country ?? 'N/A',
				fullAddress: trim($warehouse->address.", ".$warehouse->city." ".$warehouse->postal_code),
				phone: $warehouse->phone ?? 'N/A',
				email: $warehouse->email ?? 'N/A',
				isPrimary: (bool) $warehouse->is_primary,
				is_active: $warehouse->deleted_at == null,
				under_maintenance: (bool) $warehouse->under_maintenance,
				capacity: $capacity,
				current_capacity: $current,
				totalItems: $warehouse->inventories_count ?? 0,
				occupancyPercentage: round($occupancy, 2),
				locations: $warehouse->locations ? $warehouse->locations->map(function(WarehouseLocation $location) {
					return LocationDTO::fromModel($location);
				}) : collect(),
				manager: $warehouse->manager ? UserDTO::fromModel($warehouse->manager) : null,
				created_at: $warehouse->created_at,
				updated_at: $warehouse->updated_at,
			);
		}
	}