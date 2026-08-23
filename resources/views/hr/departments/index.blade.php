@extends('templates.general')

@section('title', 'Departments')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/departments/index.css') }}" />
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-sitemap icon-accent"></i> Departments
				</h1>
				<p class="subtitle">Manage company departments, manager assignments, and teams</p>
			</div>

			<a href="{{ route('hr.departments.create') }}" class="btn-primary">
				<i class="fas fa-plus"></i> Add Department
			</a>
		</div>

		<!-- Alert Messages -->
		@if(session('success'))
			<div class="alert alert-success">
				<i class="fas fa-check-circle"></i>
				<span>{{ session('success') }}</span>
			</div>
		@endif

		<!-- Table Card Container -->
		<div class="table-card">
			@if($departments->isNotEmpty())
				<table class="data-table">
					<thead>
					<tr>
						<th>Code</th>
						<th>Department Name</th>
						<th>Manager</th>
						<th>Positions</th>
						<th>Active Employees</th>
						<th class="text-right">Actions</th>
					</tr>
					</thead>
					<tbody>
					@foreach($departments as $department)
						<tr>
							<td class="code-font">{{ $department->code }}</td>
							<td>
								<strong>{{ $department->name }}</strong>
								@if($department->description)
									<span class="sub-text">{{ Str::limit($department->description, 50) }}</span>
								@endif
							</td>
							<td>
								@if($department->manager)
									<div class="manager-info">
										<i class="fas fa-user-tie icon-muted"></i>
										<span>{{ $department->manager->name }}</span>
									</div>
								@else
									<span class="text-muted">Unassigned</span>
								@endif
							</td>
							<td>
								<span class="badge badge-neutral">
									{{ $department->positions_count ?? $department->positions->count() }} Positions
								</span>
							</td>
							<td>
								<span class="badge badge-info">
									{{ $department->employees_count ?? $department->employees->count() }} Members
								</span>
							</td>
							<td class="actions-cell text-right">
								<a href="{{ route('hr.departments.show', $department->id) }}" class="btn-icon" title="View Details">
									<i class="fas fa-eye"></i>
								</a>
								<a href="{{ route('hr.departments.edit', $department->id) }}" class="btn-icon" title="Edit Department">
									<i class="fas fa-edit"></i>
								</a>
								<form action="{{ route('hr.departments.destroy', $department->id) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this department?');">
									@csrf
									@method('DELETE')
									<button type="submit" class="btn-icon btn-icon-danger" title="Delete Department">
										<i class="fas fa-trash-alt"></i>
									</button>
								</form>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>

				@if(method_exists($departments, 'links'))
					<div class="pagination-wrapper">
						{{ $departments->links() }}
					</div>
				@endif
			@else
				<div class="empty-state">
					<i class="fas fa-sitemap empty-icon"></i>
					<h3>No Departments Found</h3>
					<p>Get started by creating your first company department.</p>
					<a href="{{ route('hr.departments.create') }}" class="btn-primary mt-3">
						<i class="fas fa-plus"></i> Add Department
					</a>
				</div>
			@endif
		</div>

	</div>
@endsection
