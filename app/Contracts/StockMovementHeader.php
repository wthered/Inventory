<?php

	namespace App\Contracts;

	use Illuminate\Database\Eloquent\Model;

	interface StockMovementHeader {
		public function getSourceWarehouseId(): ?int;

		public function getTargetWarehouseId(): ?int;

		public function getMovementReason(): string;

		// Προσθέτουμε αυτό για να ξέρουμε αν είναι Πελάτης/Προμηθευτής στο Return
		public function getReferenceModel(): Model;
	}
