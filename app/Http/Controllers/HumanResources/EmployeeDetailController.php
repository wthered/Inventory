<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreEmployeeDetailRequest;
	use App\Http\Requests\HumanResources\UpdateEmployeeDetailRequest;
	use App\Models\HumanResources\EmployeeDetail;

	class EmployeeDetailController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index() {
			//
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			//
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StoreEmployeeDetailRequest $request) {
			//
		}

		/**
		 * Display the specified resource.
		 */
		public function show(EmployeeDetail $employeeDetail) {
			//
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(EmployeeDetail $employeeDetail) {
			//
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateEmployeeDetailRequest $request, EmployeeDetail $employeeDetail) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(EmployeeDetail $employeeDetail) {
			//
		}
	}
