<?php

	return [
		/*
		|--------------------------------------------------------------------------
		| Custom Validation Attributes
		|--------------------------------------------------------------------------
		*/

		'attributes' => [
			// Στοιχεία Προϊόντος
			'product'          => 'προϊόν',
			'product_id'       => 'προϊόν',
			'sku'              => 'κωδικός SKU',
			'quantity'         => 'ποσότητα',
			'unit_cost'        => 'κόστος μονάδας',

			// Αποθήκη & Τοποθεσίες
			'warehouse_id'     => 'αποθήκη',
			'location'         => 'θέση αποθήκης',
			'location_id'      => 'θέση αποθήκης',

			// Επιστροφές (Stock Returns)
			'return_number'    => 'αριθμός επιστροφής',
			'rma_number'       => 'αριθμός RMA',
			'return_date'      => 'ημερομηνία επιστροφής',
			'quality_status'   => 'κατάσταση ποιότητας',
			'is_restockable'   => 'δυνατότητα επαναποθήκευσης',

			// Polymorphic Fields (Morphs)
			'returnable_type'  => 'τύπος συναλλασσόμενου',
			'returnable_id'    => 'συναλλασσόμενος',
			'customer_id'      => 'πελάτης',
			'supplier_id'      => 'προμηθευτής',

			// Προσαρμογές (Adjustments)
			'type'             => 'τύπος κίνησης',
			'reason'           => 'αιτιολογία',
			'notes'            => 'σημειώσεις',
			'inspection_notes' => 'σημειώσεις ελέγχου',

			// Metadata
			'created_by'       => 'δημιουργήθηκε από',
			'status'           => 'κατάσταση',
			'tracking_number'  => 'αριθμός αποστολής',
			'carrier'          => 'μεταφορική εταιρεία',
		],
	];