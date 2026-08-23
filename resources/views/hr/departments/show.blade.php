@extends('templates.general')

@section('title', $department->name . ' - Details')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/departments/show.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-building icon-accent"></i> {{ $department->name }}
				</h1>
				<p class="subtitle">Code: <strong>{{ $department->code }}</strong> &bull; Created {{ $department->created_at->format('M d, Y') }}</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.departments.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
				@can('department.update')
					<a href="{{ route('hr.departments.edit', $department->id) }}" class="btn-primary">
						<i class="fas fa-edit"></i> Edit Department
					</a>
				@endcan
			</div>
		</div>

		<!-- Details Overview Card -->
		<div class="details-card">

			<div class="details-header-bar">
				<div class="department-meta">
					<div class="dept-icon-circle">
						<i class="fas fa-sitemap"></i>
					</div>
					<div class="dept-info">
						<h3>{{ $department->name }}</h3>
						<span class="dept-code">{{ $department->code }}</span>
					</div>
				</div>

				<div class="status-indicator">
					@if($department->is_active)
						<span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
					@else
						<span class="badge badge-danger"><i class="fas fa-minus-circle"></i> Inactive</span>
					@endif
				</div>
			</div>

			<!-- Department Meta Grid -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-info-circle"></i> General Information</h2>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label">Department Code</span>
						<span class="info-value text-highlight">{{ $department->code }}</span>
					</div>
					<div class="info-item">
						<span class="info-label">Department Manager</span>
						<span class="info-value">
							@if($department->manager)
								{{ $department->manager->last_name }} {{ $department->manager->first_name }}
								<small class="text-muted">({{ $department->manager->employee_code }})</small>
							@else
								<span class="text-muted">Unassigned</span>
							@endif
						</span>
					</div>
					<div class="info-item">
						<span class="info-label">Total Assigned Employees</span>
						<span class="info-value font-mono">{{ $department->employees->count() }}</span>
					</div>
					<div class="info-item full-width">
						<span class="info-label">Description</span>
						<span class="info-value">{{ $department->description ?? 'No description provided.' }}</span>
					</div>
				</div>
			</div>

			<hr class="info-divider">

			<!-- Assigned Employees Table -->
			<div class="info-section">
				<h2 class="info-section-title"><i class="fas fa-users"></i> Department Members</h2>

				<div class="table-wrapper">
					<table class="data-table">
						<thead>
						<tr>
							<th>ID</th>
							<th>Code</th>
							<th>Employee Name</th>
							<th>Position</th>
							<th>Status</th>
							<th class="text-right">Actions</th>
						</tr>
						</thead>
						<tbody>
						@forelse($department->employees->sortBy('id') as $employee)
							<tr>
								<td class="td-id font-mono">{{ $employee->id }}</td>
								<td class="font-mono text-highlight">{{ $employee->employee_code }}</td>
								<td class="td-name">
									{{ $employee->last_name }} {{ $employee->first_name }}
								</td>
								<td>{{ $employee->position->title ?? '-' }}</td>
								<td>
									@if($employee->is_active)
										<span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
									@else
										<span class="badge badge-danger"><i class="fas fa-times"></i> Inactive</span>
									@endif
								</td>
								<td class="actions-cell text-right">
									<a href="{{ route('hr.employees.show', $employee->id) }}" class="btn-action btn-view" title="View Profile">
										<i class="fas fa-eye"></i>
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="table-empty-state">
									<i class="fas fa-user-slash empty-icon"></i>
									<p>No employees are currently assigned to this department.</p>
								</td>
							</tr>
						@endforelse
						</tbody>
					</table>
				</div>
			</div>

		</div>

	</div>
@endsection
