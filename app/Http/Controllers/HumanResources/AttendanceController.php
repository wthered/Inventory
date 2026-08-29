<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\Employees\Attendances\AttendanceStoreRequest;
	use App\Http\Requests\HumanResources\Employees\Attendances\AttendanceUpdateRequest;
	use App\Models\HumanResources\Attendance;
	use App\Models\HumanResources\Employee;
	use Carbon\Carbon;
	use Illuminate\Http\Request;

	class AttendanceController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request) {
			return view('hr.attendances.index', [
				'employeeGroups' => Employee::query()->with(['user.account', 'warehouse'])->get()->groupBy(function (
					$employee
				) {
					return $employee->warehouse ? $employee->warehouse->name : 'Unassigned Warehouse';
				}),
				'statuses'       => EmployeeAttendanceStatus::class,
				'attendances'    => Attendance::query()->where([
					'work_date' => Carbon::today()->format('Y-m-d'),
				])->paginate($request->session()->get('per_page', config('preferences.pagination.per_page', 25))),
			]);
		}

		/**
		 * Show the form for creating a new resource.
		 */
		public function create() {
			return view('hr.attendances.create', [
				'employeeGroups' => Employee::query()->with(['user.account', 'warehouse'])
				                            ->get()->groupBy(function ($employee) {
						return $employee->warehouse ? $employee->warehouse->name : 'Unassigned Warehouse';
					}),
				'statuses'       => EmployeeAttendanceStatus::class,
			]);
		}

		/**
		 * Store a newly created resource in storage.
		 */
		public function store(AttendanceStoreRequest $request) {
			// Έφυγε χωρίς προειδοποίηση και όταν την πήρα τηλέφωνο μου είπε για οικογενειακούς λόγους
			$input = $request->all();
//			dd($input);
			$employee = Employee::query()->find($input['employee_id']);
			$employee->attendances()->create([
				'warehouse_id'   => $employee->warehouse->id,
				'work_date'      => $input['work_date'],
				'check_in'       => $input['check_in']->format('Y-m-d H:i:s'),
				'check_out'      => $input['check_out']->format('Y-m-d H:i:s'),
				'overtime_hours' => $input['overtime_hours'],
				'notes'          => $input['notes'],
			]);
			return redirect()->route('hr.attendances.show', [
				'employee' => $employee,
				'date'     => $input['work_date'],
			]);
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Request $request, Employee $employee, string $date) {
			$attendance = $employee->attendances()
			                       ->whereDate('work_date', Carbon::parse($date)->format('Y-m-d'))
			                       ->firstOrFail();
			$work_date = $attendance->work_date;
			return view('hr.attendances.show', [
				'employee'       => $employee,
				'date'           => Carbon::parse($date),
				'prevWorkDate'   => $work_date->copy()->subWeekday(),
				'isPrevDisabled' => $work_date->copy()->subWeekday()->lt($employee->hire_date->startOfDay()),
				'nextWorkDate'   => $work_date->copy()->addWeekday(),
				'isNextDisabled' => $work_date->copy()->addWeekday()->gt(now()->startOfDay()),
				'attendance'     => $attendance,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(Employee $employee, string $date) {
			$workDate = Carbon::parse($date)->format('Y-m-d');
			return view('hr.attendances.edit', [
				'employee'       => $employee,
				'employeeGroups' => Employee::query()->with(['user.account', 'warehouse'])->get()->groupBy(function (
					$employee
				) {
					return $employee->warehouse ? $employee->warehouse->name : 'Unassigned Warehouse';
				}),
				'statuses'       => EmployeeAttendanceStatus::class,
				'date'           => $workDate,
				'attendance'     => Attendance::query()->where('employee_id', $employee->id)
				                              ->whereDate('work_date', $workDate)
				                              ->firstOrFail(),
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		public function update(AttendanceUpdateRequest $request, Employee $employee) {
			$input = $request->input();

			$attendance = Employee::query()->findOrFail($input['employee_id'])->attendances()->whereDate('work_date', $input['work_date']);
			if ($attendance->exists()) {
				$attendance->update([
					'work_date'      => $input['work_date'],
					'check_in'       => $input['check_in'],
					'check_out'      => $input['check_out'],
					'overtime_hours' => $input['overtime_hours'],
					'status'         => $input['status'],
					'notes'          => $input['notes'],
				]);
			}
			return redirect(route('hr.attendances.show', [
				'employee' => $employee,
				'date'     => $input['work_date']
			]))->with('success', 'Attendance updated.');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Attendance $attendance) {
			//
		}
	}
