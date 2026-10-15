<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreLeaveRequestRequest;
	use App\Http\Requests\HumanResources\UpdateLeaveRequestRequest;
	use App\Models\HumanResources\LeaveRequest;

	class LeaveRequestController extends Controller {
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
		public function store(StoreLeaveRequestRequest $request) {
			//
		}

		/**
		 * Display the specified resource.
		 */
		public function show(LeaveRequest $leaveRequest) {
			//
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(LeaveRequest $leaveRequest) {
			//
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(LeaveRequest $leaveRequest) {
			//
		}
	}
