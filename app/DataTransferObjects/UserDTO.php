<?php

	namespace App\DataTransferObjects;

	use App\Models\Account;
	use App\Models\User;
	use Carbon\Carbon;
	use Illuminate\Support\Str;

	class UserDTO {
		public function __construct(
			public int $id,
			public string $name,
			public string $email,
			public Account $account,
			public string $firstName,
			public string $lastName,
			public ?int $phone,
			public string $avatar,
			public bool $isActive,
			public ?Carbon $lastLoginAt,
		) {}

		public static function fromModel(User $user): self {
			// Το Laravel θα βρει τη σχέση αυτόματα εδώ
			$account = $user->account;

			return new self(
				id: $user->id,
				name: $user->name,
				email: $user->email,
				account: $account,
				firstName: $account?->first_name ?? 'N/A',
				lastName: $account?->last_name ?? '',
				phone: $account?->phone,
				avatar: $account?->avatar ?? 'https://robohash.org/'.Str::random(10).'.png',
				isActive: (bool) ($account?->is_active ?? true),
				lastLoginAt: $account?->last_login_at ? Carbon::parse($account->last_login_at) : null,
			);
		}
	}
