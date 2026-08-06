<?php

	use App\Http\Controllers\HumanResources\AttendanceController;
	use App\Http\Controllers\HumanResources\DepartmentController;
	use App\Http\Controllers\HumanResources\EmployeeController;
	use App\Http\Controllers\HumanResources\LeaveRequestController;
	use App\Http\Controllers\HumanResources\PositionController;
	use Illuminate\Support\Facades\Route;

	/*
	|--------------------------------------------------------------------------
	| Human Resources Routes
	|--------------------------------------------------------------------------
	|
	| All routes in this file inherit the 'hr.' route name prefix and
	| 'auth' / 'verified' middlewares defined in web.php.
	|
	*/

	// Employees Management
	Route::resource('employees', EmployeeController::class);

	// Departments & Positions Management
	Route::resource('departments', DepartmentController::class);
	Route::resource('positions', PositionController::class)->except(['show']);

	// Attendance & Leave Tracking
	Route::resource('attendances', AttendanceController::class);
	Route::resource('leave-requests', LeaveRequestController::class);