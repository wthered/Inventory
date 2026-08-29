<?php

	use App\Http\Controllers\FilterController;
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
	Route::resource('attendances', AttendanceController::class)->except(['show', 'edit', 'update', 'destroy']);

	// Attendance Tracking Actions
	Route::get('attendances/employees/{employee}/date/{date}', [
		AttendanceController::class, 'show'
	])->name('attendances.show');

	Route::get('attendances/employees/{employee}/date/{date}/edit', [
		AttendanceController::class, 'edit'
	])->name('attendances.edit');

	Route::put('attendances/employees/{employee}/date/{date}', [
		AttendanceController::class, 'update'
	])->name('attendances.update');

	Route::delete('attendances/employees/{employee}/date/{date}', [
		AttendanceController::class, 'destroy'
	])->name('attendances.destroy');

	Route::post('leaves/filter', [LeaveRequestController::class, 'filter']);
	Route::resource('leaves', LeaveRequestController::class);

	// Filtering Requests
	Route::post('attendances/employees/{employee}/filter', [
		FilterController::class, 'attendances'
	])->name('employees.attendances.filter');
	Route::post('leaves/employees/{employee}/filter', [
		FilterController::class, 'leaves'
	])->name('employees.leaves.filter');
