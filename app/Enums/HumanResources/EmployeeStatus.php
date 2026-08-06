<?php

	namespace App\Enums\HumanResources;

	enum EmployeeStatus: string {
		case ACTIVE     = 'active';
		case ON_LEAVE   = 'on_leave';
		case SUSPENDED  = 'suspended';
		case TERMINATED = 'terminated';

		/**
		 * Επιστρέφει τη φιλική ονομασία για το UI.
		 */
		public function label(): string {
			return match ($this) {
				self::ACTIVE     => 'Ενεργός',
				self::ON_LEAVE   => 'Σε Άδεια',
				self::SUSPENDED  => 'Σε Αναστολή',
				self::TERMINATED => 'Αποχωρήσας / Απόλυση',
			};
		}

		/**
		 * Επιστρέφει χρώματα κατάλληλα για UI Badges (π.χ. Bootstrap / Tailwind).
		 */
		public function color(): string {
			return match ($this) {
				self::ACTIVE     => 'success',  // πράσινο
				self::ON_LEAVE   => 'warning',  // κίτρινο/πορτοκαλί
				self::SUSPENDED  => 'secondary',// γκρι
				self::TERMINATED => 'danger',   // κόκκινο
			};
		}

		/**
		 * Ελέγχει αν ο υπάλληλος θεωρείται ότι ανήκει ακόμα στο δυναμικό.
		 */
		public function isEmployed(): bool {
			return $this !== self::TERMINATED;
		}
	}
