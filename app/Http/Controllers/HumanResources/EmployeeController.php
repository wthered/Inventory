<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreEmployeeRequest;
	use App\Http\Requests\HumanResources\UpdateEmployeeRequest;
	use App\Models\Department;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\Position;
	use App\Models\User;
	use App\Models\Warehouse;

	class EmployeeController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			return view('hr.employees.index', [
				'employees'   => Employee::query()->paginate(request()->session()->get('per_page', config('preferences.pagination.per_page'))),
				'departments' => Department::query()->get(),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			return view('hr.employees.create', [
				'departments' => Department::query()->get(),
				'positions'   => Position::query()->get(),
				'warehouses'  => Warehouse::query()->get(),
				'users'       => User::query()->get(),
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StoreEmployeeRequest $request) {
			$input = $request->validated();
			dd($input);
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Employee $employee) {
			return view('hr.employees.show', [
				'employee' => $employee,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Employee $employee) {
			return view('hr.employees.edit', [
				'employee'    => $employee,
				'departments' => Department::query()->get(),
				'positions'   => Position::query()->get(),
				'warehouses'  => Warehouse::query()->get(),
				'users'       => User::query()->get(),
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateEmployeeRequest $request, Employee $employee) {
			dd($request->validated());
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Employee $employee) {
			//
		}
	}
