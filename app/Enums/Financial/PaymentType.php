<?php

	namespace App\Enums\Financial;

	use Illuminate\Support\Str;

	enum PaymentType: string {
		case RECEIPT = 'receipt'; // Είστραξη (Incoming from Customer)
		case PAYMENT = 'payment'; // Πληρωμή (Outgoing to Supplier)
		case REFUND  = 'refund';  // Επιστροφή (Money returned to customer or received back from supplier)

		public function label(): string {
			return __("enums.payment_type." . Str::lower($this->value));
		}

		/**e
		 * Determines if this transaction adds to or subtracts from cash flow.
		 */
		public function isIncoming(): bool {
			return $this === self::RECEIPT;
		}

		public function isOutgoing(): bool {
			return $this === self::PAYMENT;
		}

		public static function options(): array {
			return collect(self::cases())->mapWithKeys(fn ($type) => [
				$type->value => $type->label()
			])->toArray();
		}
	}