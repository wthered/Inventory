<?php

	return [
		'invalid_combination' => "Η αιτιολογία ':reason' δεν επιτρέπεται για κινήσεις τύπου ':type'",

		'types' => [
			'increase'   => 'Αύξηση Αποθέματος (+)',
			'decrease'   => 'Μείωση Αποθέματος (-)',
			'transfer'   => 'Ενδοδιακίνηση',
			'adjustment' => 'Προσαρμογή',
			'return'     => 'Επιστροφή',
			'pending'    => 'Εκκρεμής',
			'correction' => 'Διόρθωση',
		],

		'reasons' => [
			'purchase'        => 'Αγορά',
			'sale'            => 'Πώληση',
			'transfer_in'     => 'Είσοδος από Μεταφορά',
			'transfer_out'    => 'Έξοδος λόγω Μεταφοράς',
			'returned'        => 'Επιστροφή Πελάτη',
			'stocktake'       => 'Διαφορά Απογραφής',
			'counting_error'  => 'Λάθος Καταμέτρησης',
			'data_entry'      => 'Λάθος Καταχώρησης',
			'found'           => 'Πλεόνασμα / Εύρεση',
			'damaged'         => 'Κατεστραμμένα Εμπορεύματα',
			'expired'         => 'Ληγμένα Προϊόντα',
			'qc_reject'       => 'Απόρριψη Ποιοτικού Ελέγχου',
			'qc_sample'       => 'Δείγμα Ποιοτικού Ελέγχου',
			'quality_control' => 'Ποιοτικός Έλεγχος',
			'spillage'        => 'Διαρροή / Απώλεια υγρού',
			'theft'           => 'Κλοπή',
			'lost'            => 'Απώλεια',
			'write_off'       => 'Διαγραφή (Write Off)',
			'production'      => 'Παραγωγή',
			'sample'          => 'Δειγματισμός / Έκθεση',
			'demo'            => 'Δοκιμαστική Χρήση / Demo',
			'promo'           => 'Προωθητική Ενέργεια',
			'donation'        => 'Δωρεά',
			'other'           => 'Άλλη Αιτιολογία',
			'adjustment'      => 'Προσαρμογή άνευ λόγου'
		],

		'categories' => [
			'core_operations'   => 'Κύριες Λειτουργίες',
			'stock_corrections' => 'Διορθώσεις Αποθεμάτων',
			'quality_issues'    => 'Ποιοτικά Ζητήματα',
			'loss_theft'        => 'Απώλειες & Κλοπές',
			'business_use'      => 'Επιχειρησιακή Χρήση',
			'other'             => 'Άλλα',
		],
	];