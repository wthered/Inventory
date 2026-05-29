<?php

	namespace App\Enums;

	enum TransferStatus: int {

		case PENDING         = 1;
		case IN_TRANSIT      = 2;
		case COMPLETED       = 3;
		case CANCELED        = 4;
		case FAILED          = 5;
		case ON_HOLD         = 6;
		case EXPIRED         = 7;
		case REFUNDED        = 8;
		case ACTION_REQUIRED = 9;
		case APPROVED        = 10;
		case PARTIAL         = 11; // Added: Not all items arrived at destination
		case RETURNED        = 12; // Added: Goods sent back to source
		case DRAFT           = 13;  // Added: Still being edited, not yet official

		public function label(): string {
			return match ($this) {
				self::DRAFT           => 'Draft',
				self::PENDING         => 'Pending',
				self::APPROVED        => 'Approved',
				self::IN_TRANSIT      => 'In Transit',
				self::COMPLETED       => 'Completed',
				self::CANCELED        => 'Canceled',
				self::FAILED          => 'Failed',
				self::ON_HOLD         => 'On Hold',
				self::EXPIRED         => 'Expired',
				self::REFUNDED        => 'Refunded',
				self::ACTION_REQUIRED => 'Action Required',
				self::PARTIAL         => 'Partially Received',
				self::RETURNED        => 'Returned',
			};
		}

		public function color(): string {
			return match ($this) {
				self::DRAFT           => '#94a3b8', // Slate
				self::PENDING         => '#6b7280', // Gray
				self::APPROVED        => '#14b8a6', // Teal
				self::IN_TRANSIT      => '#3b82f6', // Blue
				self::COMPLETED       => '#22c55e', // Green
				self::CANCELED, self::FAILED, self::EXPIRED => '#ef4444', // Red
				self::ON_HOLD, self::ACTION_REQUIRED => '#f97316', // Orange
				self::REFUNDED, self::RETURNED => '#a855f7', // Purple
				self::PARTIAL         => '#84cc16', // Lime
			};
		}
	}