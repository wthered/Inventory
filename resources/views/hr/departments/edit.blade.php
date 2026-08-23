@extends('templates.general')

@section('title', 'Edit Department - ' . $department->name)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/departments/edit.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-building icon-accent"></i> Edit Department
				</h1>
				<p class="subtitle">Modify department details, manager assignments, and operational status</p>
			</div>

			<div class="action-buttons-group">
				<a href="{{ route('hr.departments.index') }}" class="btn-secondary">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
			</div>
		</div>

		<!-- Error / Alert Messages -->
		@if($errors->any())
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-triangle"></i>
				<div>
					<strong>Please review the errors below:</strong>
					<ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		@endif

		<!-- Form Card -->
		<form action="{{ route('hr.departments.update', $department->id) }}" method="POST" class="form-card">
			@csrf
			@method('PUT')

			<!-- Section: Department Information -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-sitemap"></i> Department Information</h2>

				<div class="form-grid">

					<!-- Department Code -->
					<div class="form-group">
						<label for="code" class="form-label required">Department Code</label>
						<input type="text"
						       id="code"
						       name="code"
						       value="{{ old('code', $department->code) }}"
						       class="form-control @error('code') is-invalid @enderror"
						       placeholder="e.g. HR, IT, SALES"
						       required>
						@error('code') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Department Name -->
					<div class="form-group">
						<label for="name" class="form-label required">Department Name</label>
						<input type="text"
						       id="name"
						       name="name"
						       value="{{ old('name', $department->name) }}"
						       class="form-control @error('name') is-invalid @enderror"
						       placeholder="e.g. Human Resources"
						       required>
						@error('name') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Department Manager -->
					<div class="form-group full-width">
						<label for="manager_id" class="form-label">Department Manager</label>
						<select name="manager_id" id="manager_id" class="form-control @error('manager_id') is-invalid @enderror">
							<option value="">-- No Manager Assigned --</option>
							@foreach($employees as $employee)
								<option value="{{ $employee->id }}" {{ old('manager_id', $department->manager_id) == $employee->id ? 'selected' : '' }}>
									{{ $employee->last_name }} {{ $employee->first_name }} ({{ $employee->employee_code }})
								</option>
							@endforeach
						</select>
						@error('manager_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Status Toggle -->
					<div class="form-group full-width">
						<label class="form-label">Status</label>
						<label class="checkbox-container">
							<input type="checkbox"
							       name="is_active"
							       value="1"
								{{ old('is_active', $department->is_active) ? 'checked' : '' }}>
							<span class="checkbox-label">Department is active</span>
						</label>
						@error('is_active') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<!-- Description -->
					<div class="form-group full-width">
						<label for="description" class="form-label">Description</label>
						<textarea id="description"
						          name="description"
						          rows="3"
						          class="form-control @error('description') is-invalid @enderror"
						          placeholder="Brief description of the department's responsibilities">{{ old('description', $department->description) }}</textarea>
						@error('description') <span class="form-error">{{ $message }}</span> @enderror
					</div>

				</div>
			</div>

			<!-- Form Actions Bar -->
			<div class="form-actions">
				<a href="{{ route('hr.departments.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-save"></i> Update Department
				</button>
			</div>
		</form>

	</div>
@endsection
