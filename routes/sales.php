<?php

	use App\Http\Controllers\Commercial\SalesOrderController;
	use App\Http\Controllers\FilterController;
	use Illuminate\Support\Facades\Route;

	// POST route for dynamic sales orders filtering
	Route::post('sales/index/filter', [FilterController::class, 'sales'])->name('sales.filter');

	// Resource routes for Sales Orders
	Route::resource('sales', SalesOrderController::class);