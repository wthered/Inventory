<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Enums\HumanResources\LeaveType;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreLeaveRequestRequest;
	use App\Http\Requests\HumanResources\UpdateLeaveRequestRequest;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\LeaveRequest;
	use Illuminate\Http\Request;

	class LeaveRequestController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request) {
			return view('hr.leave-requests.index', [
				'employees'     => Employee::query()->get(),
				'leaveTypes'    => LeaveType::class,
				'leaveRequests' => LeaveRequest::query()->paginate($request->session()->get('per_page', config('preferences.pagination.per_page', 25))),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			return view('hr.leave-requests.create', [
				'employees'  => Employee::query()->get(),
				'leaveTypes' => LeaveType::class,
			]);
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
			return view('hr.leave-requests.show', [
				'leaveRequest' => $leaveRequest,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(LeaveRequest $leaveRequest) {
			return view('hr.leave-requests.edit', [
				'leaveRequest' => $leaveRequest,
			]);
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
