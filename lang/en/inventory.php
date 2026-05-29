<?php

	return [
		'invalid_combination' => "The reason ':reason' is not allowed for ':type' transactions.",

		'types' => [
			'in'         => 'Stock Increase',
			'out'        => 'Stock Decrease',
			'transfer'   => 'Internal Transfer',
			'adjustment' => 'Adjustment',
			'return'     => 'Return',
			'pending'    => 'Pending',
			'other'      => 'Other',
		],

		'reasons' => [
			// Core Operations
			'purchase'        => 'Purchase',
			'sale'            => 'Sale',
			'transfer_in'     => 'Transfer In',
			'transfer_out'    => 'Transfer Out',
			'returned'        => 'Customer Return',

			// Stock Corrections
			'stocktake'       => 'Stocktake Variance',
			'counting_error'  => 'Counting Error',
			'data_entry'      => 'Data Entry Error',
			'found'           => 'Found Stock',

			// Quality & Product Issues
			'damaged'         => 'Damaged Goods',
			'expired'         => 'Expired Stock',
			'qc_reject'       => 'Quality Control Reject',
			'qc_sample'       => 'Quality Control Sample',
			'quality_control' => 'Quality Control',
			'spillage'        => 'Spillage/Leakage',

			// Loss & Theft
			'theft'           => 'Theft',
			'lost'            => 'Lost Stock',
			'write_off'       => 'Write Off',

			// Business Use
			'production'      => 'Production',
			'sample'          => 'Sample/Display',
			'demo'            => 'Demo/Testing',
			'promo'           => 'Promotional Giveaway',
			'donation'        => 'Donation',

			// Other
			'other'           => 'Other / Miscellaneous',
		],

		'categories' => [
			'core_operations'   => 'Core Operations',
			'stock_corrections' => 'Stock Corrections',
			'quality_issues'    => 'Quality & Product Issues',
			'loss_theft'        => 'Loss & Theft',
			'business_use'      => 'Business Use',
			'other'             => 'Other',
		],
	];