<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreDepartmentRequest;
	use App\Http\Requests\HumanResources\UpdateDepartmentRequest;
	use App\Models\HumanResources\Department;
	use App\Models\HumanResources\Employee;

	class DepartmentController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			return view('hr.departments.index', [
				'departments' => Department::all(),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			return view('hr.departments.create', [
				'employees' => Employee::query()->get(),
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StoreDepartmentRequest $request) {
			//
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Department $department) {
			return view('hr.departments.show', [
				'department' => $department,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Department $department) {
			return view('hr.departments.edit', [
				'department' => $department,
				'employees'  => Employee::query()->get(),
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateDepartmentRequest $request, Department $department) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Department $department) {
			//
		}
	}
