<?php

	namespace App\Enums\Customers;

	use Illuminate\Support\Str;

	enum CustomerType: string {
		// Ιδιώτες / Λιανική
		case INDIVIDUAL = 'individual'; // Απλός ιδιώτης
		case RETAIL     = 'retail';     // Πελάτης λιανικής (συχνός)

		// Επιχειρήσεις / B2B
		case WHOLESALE  = 'wholesale';  // Χονδρική
		case CORPORATE  = 'corporate';  // Μεγάλες εταιρείες
		case BUSINESS   = 'business';   // Μικρομεσαίες επιχειρήσεις (SME)
		case FREELANCER = 'freelancer'; // Ελεύθεροι επαγγελματίες / Ατομικές επιχειρήσεις

		// Δημόσιος Τομέας & Οργανισμοί
		case GOVERNMENT = 'government'; // Δημόσιοι φορείς / Οργανισμοί
		case NON_PROFIT = 'non_profit'; // ΜΚΟ / Σύλλογοι

		// Ειδικές Κατηγορίες
		case DISTRIBUTOR = 'distributor'; // Διανομέας / Αντιπρόσωπος
		case PROSPECT    = 'prospect';    // Υποψήφιος πελάτης (Lead)

		public function label(): string {
			return __("enums.customer_type." . $this->value);
		}

		public function color(): string {
			return match($this) {
				self::INDIVIDUAL, self::RETAIL => '#64748b', // Slate
				self::WHOLESALE  => '#f59e0b', // Amber
				self::CORPORATE  => '#6366f1', // Indigo
				self::BUSINESS   => '#3b82f6', // Blue
				self::FREELANCER => '#0ea5e9', // Sky
				self::GOVERNMENT => '#ef4444', // Red (Συνήθως απαιτεί προσοχή/γραφειοκρατία)
				self::NON_PROFIT => '#10b981', // Emerald
				self::DISTRIBUTOR => '#8b5cf6', // Violet
				self::PROSPECT    => '#94a3b8', // Light Gray
			};
		}

		public static function options(): array {
			return collect(self::cases())->mapWithKeys(fn ($type) => [
				$type->value => $type->label()
			])->toArray();
		}
	}