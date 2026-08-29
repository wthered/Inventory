<?php

	namespace App\Enums\HumanResources;

	enum LeaveStatus: string {
		case PENDING   = 'pending';
		case APPROVED  = 'approved';
		case REJECTED  = 'rejected';
		case CANCELLED = 'cancelled';

		/**
		 * Φιλική ονομασία για εμφάνιση στο UI.
		 */
		public function label(): string {
			return match ($this) {
				self::PENDING   => 'Εκκρεμεί',
				self::APPROVED  => 'Εγκρίθηκε',
				self::REJECTED  => 'Απορρίφθηκε',
				self::CANCELLED => 'Aκυρώθηκε',
			};
		}

		/**
		 * Hex Color Code για φόντο / σήμανση.
		 */
		public function color(): string {
			return match ($this) {
				self::PENDING   => '#f59e0b',   // Amber / Warning
				self::APPROVED  => '#10b981',  // Emerald / Success
				self::REJECTED  => '#ef4444',  // Red / Danger
				self::CANCELLED => '#6b7280', // Gray / Muted
			};
		}

		/**
		 * Hex Color Code για το κείμενο (π.χ. αν το βάλεις πάνω από το background color).
		 */
		public function textColor(): string {
			return match ($this) {
				self::PENDING, self::APPROVED, self::REJECTED, self::CANCELLED => '#ffffff',
			};
		}

		// Inside App\Enums\HumanResources\LeaveStatus

		public function badgeClass(): string {
			return match ($this) {
				self::APPROVED  => 'badge-success',
				self::PENDING   => 'badge-warning',
				self::REJECTED  => 'badge-danger',
				self::CANCELLED => 'badge-secondary',
			};
		}

		public function icon(): string {
			return match ($this) {
				self::APPROVED  => 'fas fa-check',
				self::PENDING   => 'fas fa-hourglass-half',
				self::REJECTED  => 'fas fa-times',
				self::CANCELLED => 'fas fa-ban',
			};
		}
	}
