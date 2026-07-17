<?php

	use App\Http\Controllers\Commercial\SalesOrderController;
	use Illuminate\Support\Facades\Route;

// Resource routes for Sales Orders
	Route::resource('sales', SalesOrderController::class);