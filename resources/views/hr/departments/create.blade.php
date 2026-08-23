@extends('templates.general')

@section('title', 'Create Department')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/departments/create.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-plus-circle icon-accent"></i> Create Department
				</h1>
				<p class="subtitle">Add a new organizational unit to your company structure</p>
			</div>

			<a href="{{ route('hr.departments.index') }}" class="btn-secondary">
				<i class="fas fa-arrow-left"></i> Back to Departments
			</a>
		</div>

		<!-- Form Card Container -->
		<div class="form-card">
			<form action="{{ route('hr.departments.store') }}" method="POST">
				@csrf

				<div class="form-grid">
					<!-- Department Name -->
					<div class="form-group">
						<label for="name" class="form-label">
							Department Name <span class="required">*</span>
						</label>
						<input
							type="text"
							name="name"
							id="name"
							class="form-control @error('name') is-invalid @enderror"
							value="{{ old('name') }}"
							placeholder="e.g., Human Resources"
							required
						>
						@error('name')
						<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>

					<!-- Department Code -->
					<div class="form-group">
						<label for="code" class="form-label">
							Department Code <span class="required">*</span>
						</label>
						<input
							type="text"
							name="code"
							id="code"
							class="form-control @error('code') is-invalid @enderror"
							value="{{ old('code') }}"
							placeholder="e.g., HR"
							required
						>
						@error('code')
						<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>

					<!-- Department Manager -->
					<div class="form-group col-span-2">
						<label for="manager_id" class="form-label">Assign Manager</label>
						<select name="manager_id" id="manager_id"
						        class="form-select @error('manager_id') is-invalid @enderror">
							<option value="">-- Select Manager (Optional) --</option>
							@foreach($employees as $employee)
								<option
									value="{{ $employee->id }}" {{ old('manager_id') == $employee->id ? 'selected' : '' }}>
									{{ $employee->surname }} {{ $employee->name }}
								</option>
							@endforeach
						</select>
						@error('manager_id')
						<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>

					<!-- Description -->
					<div class="form-group col-span-2">
						<label for="description" class="form-label">Description</label>
						<textarea
							name="description"
							id="description"
							rows="4"
							class="form-control @error('description') is-invalid @enderror"
							placeholder="Brief description of the department's responsibilities..."
						>{{ old('description') }}</textarea>
						@error('description')
						<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
				</div>

				<!-- Form Actions -->
				<div class="form-actions">
					<a href="{{ route('hr.departments.index') }}" class="btn-secondary">Cancel</a>
					<button type="submit" class="btn-primary">
						<i class="fas fa-save"></i> Save Department
					</button>
				</div>
			</form>
		</div>

	</div>
@endsection
