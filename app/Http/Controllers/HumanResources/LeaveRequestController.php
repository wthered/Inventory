<?php

	namespace App\Http\Controllers\HumanResources;

	use App\Enums\HumanResources\LeaveStatus;
	use App\Enums\HumanResources\LeaveType;
	use App\Http\Controllers\Controller;
	use App\Http\Requests\Filters\Leaves\FilterLeavesRequest;
	use App\Http\Requests\HumanResources\Employees\Leaves\LeaveStoreRequest;
	use App\Http\Requests\HumanResources\UpdateLeaveRequest;
	use App\Models\HumanResources\Employee;
	use App\Models\HumanResources\LeaveRequest;
	use Carbon\Carbon;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Throwable;

	class LeaveRequestController extends Controller {
		/**
		 * Display a listing of the resource.
		 */
		public function index(Request $request) {
			return view('hr.leave-requests.index', [
				'employeeGroups' => Employee::with(['warehouse', 'user.account'])->get()
				                            ->groupBy(function ($employee) {
					                            return $employee->warehouse ? $employee->warehouse->name : 'Unassigned Warehouse';
				                            }),
				'statuses'       => LeaveStatus::class,
				'types'          => LeaveType::class,
				'leaveRequests'  => LeaveRequest::query()->paginate($request->session()->get('per_page', config('preferences.pagination.per_page', 25))),
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
		public function store(LeaveStoreRequest $request) {
			$input = $request->validated();
			$employee = Employee::query()->find($input['employee_id']);
			$employee->leaveRequests()->create([
				'leave_type' => $input['type'],
				'start_date' => Carbon::parse($input['start_date'])->format('Y-m-d'),
				'end_date'   => Carbon::parse($input['end_date'])->format('Y-m-d'),
				'total_days' => Carbon::parse($input['start_date'])->diffInDays(Carbon::parse($input['end_date'])) + 1,
				'reason'     => $input['reason'],
			]);
			return redirect()->route('hr.leaves.index');
		}

		/**
		 * Display the specified resource.
		 */
		public function show(Request $request, LeaveRequest $leaf) {
			$leaf->load(['employee']);
			return view('hr.leave-requests.show', [
				'leaveRequest' => $leaf,
			]);
		}

		/**
		 * Show the form for editing the specified resource.
		 */
		public function edit(LeaveRequest $leaf) {
			return view('hr.leave-requests.edit', [
				'leaf'        => $leaf,
				'employees'   => Employee::with(['warehouse', 'user.account'])->get()
				                         ->groupBy(function ($employee) {
					                         return $employee->warehouse ? $employee->warehouse->name : 'Unassigned Warehouse';
				                         }),
				'leaveTypes'  => LeaveType::class,
				'leaveStatus' => LeaveStatus::class,
			]);
		}

		/**
		 * Update the specified resource in storage.
		 */
		/**
		 * Update the specified resource in storage.
		 */
		public function update(UpdateLeaveRequest $request, LeaveRequest $leaf) {
			$input = $request->validated();

			// Ensure $isApproved is evaluated before dumping
			$isApproved = $input['status'] === LeaveStatus::APPROVED->value;

			$leaf->update([
				'employee_id'      => $input['employee_id'],
				'leave_type'       => $input['leave_type_id'], // Validated as 'leave_type_id'
				'start_date'       => $input['start_date'],
				'end_date'         => $input['end_date'],
				'total_days'       => Carbon::parse($input['start_date'])->diffInDays(Carbon::parse($input['end_date'])) + 1,
				'reason'           => $input['reason'] ?? null,
				'rejection_reason' => $input['rejection_reason'] ?? null,
				'status'           => $input['status'],
				'approved_by'      => $isApproved ? $request->user()->id : null,
				'approved_at'      => $isApproved ? Carbon::now() : null,
				'action_at'        => Carbon::now(),
			]);

			return redirect()->route('hr.leaves.show', $leaf)
			                 ->with('success', 'Leave request updated successfully.');
		}

		/**
		 * Remove the specified resource from storage.
		 */
		public function destroy(LeaveRequest $leaf) {
			$leaf->delete();
			return redirect()->route('hr.leaves.index');
		}

		/*************
		 * Used by Index for filtering by Employee, Type, Status
		 *
		 * @return JsonResponse
		 * @throws Throwable
		 */
		public function filter(FilterLeavesRequest $request) {
			$input = $request->validated();
			$leaves = LeaveRequest::query()->when(!empty($input['employee_id']), function ($query) use ($input) {
				$query->where('employee_id', $input['employee_id']);
			})->when(!empty($input['leave_type']), function ($query) use ($input) {
				$query->where('leave_type', $input['leave_type']);
			})->when(!empty($input['leave_status']), function ($query) use ($input) {
				$query->where('status', $input['leave_status']);
			})->paginate($request->session()->get('per_page'));
			return response()->json([
				'html'       => view('partials.leaves.index', ['leaveRequests' => $leaves])->render(),
				'pagination' => $leaves->hasPages() ? $leaves->withQueryString()->links('pagination::simple') : '',
			]);
		}
	}
