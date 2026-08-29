<?php

	namespace App\Http\Controllers\HumanResources;

	use App\DataTransferObjects\UserDTO;
	use App\Enums\HumanResources\EmployeeAttendanceStatus;
	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\HumanResources\Employees\StoreEmployeeRequest;
	use App\Http\Requests\HumanResources\Employees\UpdateEmployeeRequest;
	use App\Models\Department;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\Position;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Http\Request;

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
		public function show(Request $request, Employee $employee) {
			$employee->load(['department', 'position', 'warehouse', 'user', 'detail']);

			// 1. Filtered Attendances


			// 2. Filtered Leave Requests
			$leaveRequests = $employee->leaveRequests()
			                          ->paginate($request->session()->get('per_page'), ['*'], 'leave_page')
			                          ->withQueryString();

			$attendances = $employee->attendances()->paginate($request->session()->get('per_page'), ['*'], 'att_page');

			return view('hr.employees.show', [
				'employee'      => $employee,
				'account'       => $employee->user?->account ? UserDTO::fromModel($employee->user) : null,
				'attendances'   => $attendances,
				'leaveRequests' => $leaveRequests,
				'types'         => LeaveType::class,
				'leaves'        => LeaveStatus::class,
				'statuses'      => EmployeeAttendanceStatus::class,
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
			$input = $request->validated();

			$employee->update([
				'employee_code' => $input['employee_code'],
				'hire_date'     => $input['hire_date'],
				'department_id' => $input['department_id'] ?? null,
				'position_id'   => $input['position_id'] ?? null,
				'warehouse_id'  => $input['warehouse_id'] ?? null,
				'user_id'       => $input['user_id'] ?? null,
			]);

			$employee->detail()->update([
					'afm'                     => $input['afm'] ?? null,
					'social_security'         => $input['social_security'] ?? null,
					'id_card_number'          => $input['id_card_number'] ?? null,
					'birth_date'              => $input['birth_date'] ?? null,
					'address'                 => $input['address'] ?? null,
					'city'                    => $input['city'] ?? null,
					'postal_code'             => $input['postal_code'] ?? null,
					'iban'                    => $input['iban'] ?? null,
					'emergency_contact_name'  => $input['emergency_contact_name'] ?? null,
					'emergency_contact_phone' => $input['emergency_contact_phone'] ?? null,
				]
			);
			dd($request->input());
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(Employee $employee) {
			//
		}
	}
