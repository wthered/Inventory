<?php

	namespace App\Enums\Inventory;

	use Exception;
	use Illuminate\Support\Collection;

	enum AdjustmentReason: string {
		// Your existing reasons
		case DAMAGED         = 'damaged';
		case EXPIRED         = 'expired';
		case RETURNED        = 'returned';
		case FOUND           = 'found';
		case LOST            = 'lost';
		case THEFT           = 'theft';
		case COUNTING_ERROR  = 'counting_error';
		case QUALITY_CONTROL = 'quality_control';
		case PRODUCTION      = 'production';
		case PURCHASE        = 'purchase';
		case SALE            = 'sale';
		case TRANSFER_IN     = 'transfer_in';
		case TRANSFER_OUT    = 'transfer_out';
		case OTHER           = 'other';

		// New reasons from dropdown
		case STOCKTAKE  = 'stocktake';
		case WRITE_OFF  = 'write_off';
		case SPILLAGE   = 'spillage';
		case DATA_ENTRY = 'data_entry';
		case QC_REJECT  = 'qc_reject';
		case QC_SAMPLE  = 'qc_sample';
		case SAMPLE     = 'sample';
		case DONATION   = 'donation';
		case PROMO      = 'promo';
		case DEMO       = 'demo';
		case ADJUSTMENT = 'adjustment';

		/**
		 * Get all reasons grouped by category
		 *
		 * @throws Exception
		 */
		public static function grouped(): array {
			$grouped = [];

			foreach (self::cases() as $reason) {
				$grouped[$reason->category()][] = $reason;
			}

			return $grouped;
		}

		/**
		 * Get category/group for this reason
		 *
		 * @throws Exception
		 */
		public function category(): string {
			return match ($this) {
				self::PURCHASE, self::SALE, self::TRANSFER_IN, self::TRANSFER_OUT, self::RETURNED => 'core_operations',

				self::STOCKTAKE, self::COUNTING_ERROR, self::DATA_ENTRY, self::FOUND, self::ADJUSTMENT => 'stock_corrections',

				self::DAMAGED, self::EXPIRED, self::QC_REJECT, self::QC_SAMPLE, self::QUALITY_CONTROL, self::SPILLAGE => 'quality_issues',

				self::THEFT, self::LOST, self::WRITE_OFF => 'loss_theft',

				self::PRODUCTION, self::SAMPLE, self::DEMO, self::PROMO, self::DONATION => 'business_use',

				self::OTHER => 'other',
			};
		}

		/**
		 * Get reasons for dropdown with option groups
		 *
		 * @throws Exception
		 */
		public static function forDropdown(): Collection {
			// Παίρνουμε τις μεταφρασμένες κεφαλίδες των γκρουπ από το lang file
			$groupLabels = [
				'core_operations'   => __('inventory.categories.core_operations'),
				'stock_corrections' => __('inventory.categories.stock_corrections'),
				'quality_issues'    => __('inventory.categories.quality_issues'),
				'loss_theft'        => __('inventory.categories.loss_theft'),
				'business_use'      => __('inventory.categories.business_use'),
				'other'             => __('inventory.categories.other'),
			];

			$dropdown = [];

			// 1. Δημιουργούμε τις άδειες ομάδες στο array
			foreach ($groupLabels as $categoryKey => $translatedLabel) {
				$dropdown[$translatedLabel] = [];
			}

			// 2. Τοποθετούμε κάθε reason στη σωστή ομάδα
			foreach (self::cases() as $reason) {
				$categoryKey = $reason->category();
				$groupTitle  = $groupLabels[$categoryKey] ?? $groupLabels['other'];

				$dropdown[$groupTitle][$reason->value] = $reason->label();
			}

			// 3. Αφαιρούμε τυχόν άδεια γκρουπ για να είναι καθαρό το UI
			return Collection::make($dropdown)->filter(fn($group) => !empty($group));
		}

		public function label(): string {
			return __("inventory.reasons." . $this->value);
		}

		/**
		 * Get reasons that increase inventory
		 */
		public static function increaseReasons(): array {
			return array_filter(self::cases(), fn($reason) => $reason->isIncreaseReason());
		}

		/**
		 * Check if this reason typically increases inventory
		 */
		public function isIncreaseReason(): bool {
			return in_array($this, [
				self::PURCHASE,
				self::TRANSFER_IN,
				self::RETURNED,
				self::FOUND,
				self::PRODUCTION,
			]);
		}

		/**
		 * Get reasons that decrease inventory
		 */
		public static function decreaseReasons(): array {
			return array_filter(self::cases(), fn($reason) => $reason->isDecreaseReason());
		}

		/**
		 * Check if this reason typically decreases inventory
		 */
		public function isDecreaseReason(): bool {
			return in_array($this, [
				self::SALE,
				self::TRANSFER_OUT,
				self::DAMAGED,
				self::EXPIRED,
				self::THEFT,
				self::LOST,
				self::QC_REJECT,
				self::WRITE_OFF,
				self::SPILLAGE,
				self::DONATION,
				self::PROMO,
				self::DEMO,
				self::SAMPLE,
				self::PRODUCTION,
			]);
		}

		/**
		 * Check if a raw string value is a valid adjustment reason.
		 */
		public static function isValid(string $value): bool {
			dd($value);
			return self::tryFrom($value)->isDecreaseReason() || self::tryFrom($value)->isIncreaseReason();
		}

		public function requiresNotes(): bool {
			return in_array($this, [
				self::DAMAGED,
				self::THEFT,
				self::COUNTING_ERROR,
				self::QUALITY_CONTROL,
				self::OTHER,
				self::WRITE_OFF,
				self::QC_REJECT,
				self::DONATION,
				self::DEMO,
				self::PROMO,
			]);
		}

		/**
		 * Get color for UI display
		 *
		 * @throws Exception
		 */
		public function color(): string {
			return match ($this) {
				// Loss/negative reasons - red/orange
				self::DAMAGED, self::EXPIRED, self::THEFT, self::LOST, self::WRITE_OFF, self::QC_REJECT => 'danger',

				// Quality/issue reasons - yellow
				self::SPILLAGE, self::QUALITY_CONTROL, self::QC_SAMPLE => 'warning',

				// Correction reasons - blue
				self::STOCKTAKE, self::COUNTING_ERROR, self::DATA_ENTRY, self::FOUND, self::ADJUSTMENT => 'info',

				// Business use - purple
				self::PRODUCTION, self::SAMPLE, self::DEMO, self::PROMO, self::DONATION => 'secondary',

				// Core operations - green
				self::PURCHASE, self::SALE, self::TRANSFER_IN, self::TRANSFER_OUT, self::RETURNED => 'success',

				self::OTHER => 'light',
			};
		}

		/**
		 * Check if this reason is a correction (not a real transaction)
		 */
		public function isCorrectionReason(): bool {
			return in_array($this, [
				self::STOCKTAKE,
				self::COUNTING_ERROR,
				self::DATA_ENTRY,
				self::FOUND,
				self::QUALITY_CONTROL,
			]);
		}

		/**
		 * Check if this reason requires batch tracking
		 */
		public function requiresBatch(): bool {
			return in_array($this, [
				self::DAMAGED,
				self::EXPIRED,
				self::QC_REJECT,
				self::QC_SAMPLE,
				self::QUALITY_CONTROL,
			]);
		}

		/**
		 * Get icon for UI display
		 */
		public function icon(): string {
			return match ($this) {
				self::DAMAGED => '⚠️',
				self::EXPIRED => '📅',
				self::THEFT => '🔒',
				self::LOST => '🔍',
				self::WRITE_OFF => '📝',
				self::SPILLAGE => '💧',
				self::PURCHASE => '📦',
				self::SALE => '💰',
				self::TRANSFER_IN, self::TRANSFER_OUT => '🔄',
				self::ADJUSTMENT => '🛠️',
				self::RETURNED => '↩️',
				self::FOUND => '🎯',
				self::STOCKTAKE => '📊',
				self::COUNTING_ERROR => '❌',
				self::DATA_ENTRY => '⌨️',
				self::QC_REJECT, self::QC_SAMPLE, self::QUALITY_CONTROL => '🔬',
				self::PRODUCTION => '🏭',
				self::SAMPLE, self::DEMO => '📱',
				self::PROMO => '🎁',
				self::DONATION => '❤️',
				self::OTHER => '❓',
			};
		}
	}