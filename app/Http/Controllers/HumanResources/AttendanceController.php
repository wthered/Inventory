<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\StoreAttendanceRequest;
	use App\Http\Requests\HumanResources\UpdateAttendanceRequest;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Employee;
	use Illuminate\Http\Request;

	class AttendanceController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request) {
			return view('hr.attendances.index', [
				'employees'   => Employee::query()->get(),
				'attendances' => Attendance::query()->paginate($request->session()->get('per_page', config('preferences.pagination.per_page', 25))),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			return view('hr.attendances.create', [
				'employees' => Employee::query()->get(),
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(StoreAttendanceRequest $request) {
			//
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Attendance $attendance) {
			return view('hr.attendances.show', [
				'attendance' => $attendance,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Attendance $attendance) {
			return view('hr.attendances.edit', [
				'attendance' => $attendance,
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateAttendanceRequest $request, Attendance $attendance) {
			//
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Attendance $attendance) {
			//
		}
	}
