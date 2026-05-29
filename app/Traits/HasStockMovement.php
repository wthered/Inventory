<?php

	namespace App\Traits;

	trait HasStockMovement {
		/**
		 * Επιστρέφει τα items της κίνησης (π.χ. stock_return_items, sales_order_items)
		 */
		public function getMovementItems() {
			// Αν το relation στο μοντέλο λέγεται 'items', το επιστρέφει αυτόματα
			return $this->items;
		}

		/**
		 * Επιστρέφει την αποθήκη που επηρεάζεται.
		 * Για τα transfers, θα χρειαστεί override στο μοντέλο.
		 */
		public function getAffectedWarehouseId(): int {
			return $this->warehouse_id;
		}

		/**
		 * Καθορίζει αν η κίνηση είναι είσοδος ή έξοδος.
		 * Επιστρέφει 'in' για Returns, 'out' για Sales κτλ.
		 */
		public function getMovementType(): string {
			// Default logic βασισμένο στο class name αν δεν οριστεί διαφορετικά
			$className = class_basename($this);

			return match ($className) {
				'StockReturn' => 'in',
				'StockAdjustment' => 'adjustment',
				default => 'out',
			};
		}

		/**
		 * Ελέγχει αν το status του μοντέλου επιτρέπει την ενημέρωση του αποθέματος.
		 */
		public function isReadyForStockUpdate(): bool {
			return match ($this->status) {
				'completed', 'approved', 'shipped', 'received' => true,
				default => false,
			};
		}
	}