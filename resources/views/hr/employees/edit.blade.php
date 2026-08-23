@extends('templates.general')

@section('title', 'Edit Employee: ' . $employee->first_name . ' ' . $employee->last_name)

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/hr/employees/edit.css') }}"/>
@endsection

@section('content')
	<div class="page-container">

		<!-- Header Section -->
		<div class="page-header">
			<div class="header-title-group">
				<h1>
					<i class="fas fa-user-edit icon-accent"></i> Edit
					Employee: {{ $employee->first_name }} {{ $employee->last_name }}
				</h1>
				<p class="subtitle">Update the employee's main information and personal details</p>
			</div>

			<a href="{{ route('hr.employees.index') }}" class="btn-secondary">
				<i class="fas fa-arrow-left"></i> Back to List
			</a>
		</div>

		<!-- Error / Alert Messages -->
		@if($errors->any())
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-triangle"></i>
				<div>
					<strong>Please check the form for errors:</strong>
					<ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			</div>
		@endif

		<!-- Form Container -->
		<form action="{{ route('hr.employees.update', $employee->id) }}" method="POST" class="form-card">
			@csrf
			@method('PUT')

			<!-- Section 1: Employment Details -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-briefcase"></i> Employment & Work Details</h2>

				<div class="form-grid">
					<div class="form-group">
						<label for="employee_code" class="form-label required">Employee Code</label>
						<input type="text"
						       id="employee_code"
						       name="employee_code"
						       value="{{ old('employee_code', $employee->employee_code) }}"
						       class="form-control @error('employee_code') is-invalid @enderror"
						       placeholder="e.g. EMP-2026-001"
						       required>
						@error('employee_code') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="hire_date" class="form-label required">Hire Date</label>
						<input type="date"
						       id="hire_date"
						       name="hire_date"
						       value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}"
						       class="form-control @error('hire_date') is-invalid @enderror"
						       required>
						@error('hire_date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="first_name" class="form-label required">First Name</label>
						<input type="text"
						       id="first_name"
						       name="first_name"
						       value="{{ old('first_name', $employee->first_name) }}"
						       class="form-control @error('first_name') is-invalid @enderror"
						       required>
						@error('first_name') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="last_name" class="form-label required">Last Name</label>
						<input type="text"
						       id="last_name"
						       name="last_name"
						       value="{{ old('last_name', $employee->last_name) }}"
						       class="form-control @error('last_name') is-invalid @enderror"
						       required>
						@error('last_name') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="department_id" class="form-label">Department</label>
						<select name="department_id" id="department_id" class="form-control">
							<option value="">-- Select Department --</option>
							@foreach($departments as $department)
								<option
									value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
									{{ $department->name }}
								</option>
							@endforeach
						</select>
						@error('department_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="position_id" class="form-label">Position / Job Title</label>
						<select name="position_id" id="position_id" class="form-control">
							<option value="">-- Select Position --</option>
							@foreach($positions as $position)
								<option
									value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
									{{ $position->title }}
								</option>
							@endforeach
						</select>
						@error('position_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="warehouse_id" class="form-label">Assigned Warehouse</label>
						<select name="warehouse_id" id="warehouse_id" class="form-control">
							<option value="">-- Select Warehouse --</option>
							@foreach($warehouses as $warehouse)
								<option
									value="{{ $warehouse->id }}" {{ old('warehouse_id', $employee->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
									{{ $warehouse->name }}
								</option>
							@endforeach
						</select>
						@error('warehouse_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="user_id" class="form-label">Linked System User</label>
						<select name="user_id" id="user_id" class="form-control">
							<option value="">-- No User Account --</option>
							@foreach($users as $user)
								<option
									value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
									{{ $user->name }} ({{ $user->email }})
								</option>
							@endforeach
						</select>
						@error('user_id') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="phone" class="form-label">Primary Phone</label>
						<input type="text"
						       id="phone"
						       name="phone"
						       value="{{ old('phone', $employee->phone) }}"
						       class="form-control @error('phone') is-invalid @enderror">
						@error('phone') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group form-group-checkbox">
						<label class="checkbox-label">
							<input type="checkbox" name="is_active"
							       value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
							<span>Active Employee</span>
						</label>
					</div>
				</div>
			</div>

			<hr class="form-divider">

			<!-- Section 2: Personal & Identification Details -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-id-card"></i> Personal & Tax Information</h2>

				<div class="form-grid">
					<div class="form-group">
						<label for="afm" class="form-label">AFM (Tax ID)</label>
						<input type="text" id="afm" name="afm" value="{{ old('afm', $employee->afm) }}" maxlength="15"
						       class="form-control">
						@error('afm') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="social_security" class="form-label">AMKA (Social Security)</label>
						<input type="text" id="social_security" name="social_security"
						       value="{{ old('social_security', $employee->social_security) }}" maxlength="16"
						       class="form-control">
						@error('social_security') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="id_card_number" class="form-label">ID Card Number</label>
						<input type="text" id="id_card_number" name="id_card_number"
						       value="{{ old('id_card_number', $employee->id_card_number) }}"
						       maxlength="20" class="form-control">
						@error('id_card_number') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="birth_date" class="form-label">Date of Birth</label>
						<input type="date" id="birth_date" name="birth_date"
						       value="{{ old('birth_date', optional($employee->birth_date)->format('Y-m-d')) }}"
						       class="form-control">
						@error('birth_date') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group full-width">
						<label for="address" class="form-label">Street Address</label>
						<input type="text" id="address" name="address" value="{{ old('address', $employee->address) }}"
						       class="form-control">
						@error('address') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="city" class="form-label">City</label>
						<input type="text" id="city" name="city" value="{{ old('city', $employee->city) }}"
						       class="form-control">
						@error('city') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group">
						<label for="postal_code" class="form-label">Postal Code</label>
						<input type="text" id="postal_code" name="postal_code"
						       value="{{ old('postal_code', $employee->postal_code) }}"
						       maxlength="10" class="form-control">
						@error('postal_code') <span class="form-error">{{ $message }}</span> @enderror
					</div>

					<div class="form-group full-width">
						<label for="iban" class="form-label">IBAN Account</label>
						<input type="text" id="iban" name="iban" value="{{ old('iban', $employee->iban) }}"
						       maxlength="34"
						       class="form-control" placeholder="GR...">
						@error('iban') <span class="form-error">{{ $message }}</span> @enderror
					</div>
				</div>
			</div>

			<hr class="form-divider">

			<!-- Section 3: Emergency Contact -->
			<div class="form-section">
				<h2 class="form-section-title"><i class="fas fa-phone-alt"></i> Emergency Contact</h2>

				<div class="form-grid">
					<div class="form-group">
						<label for="emergency_contact_name" class="form-label">Contact Person Name</label>
						<input type="text" id="emergency_contact_name" name="emergency_contact_name"
						       value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
						       class="form-control">
					</div>

					<div class="form-group">
						<label for="emergency_contact_phone" class="form-label">Contact Person Phone</label>
						<input type="text" id="emergency_contact_phone" name="emergency_contact_phone"
						       value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
						       class="form-control">
					</div>
				</div>
			</div>

			<!-- Actions Bar -->
			<div class="form-actions">
				<a href="{{ route('hr.employees.index') }}" class="btn-secondary">Cancel</a>
				<button type="submit" class="btn-primary">
					<i class="fas fa-sync-alt"></i> Update Employee
				</button>
			</div>
		</form>

	</div>
@endsection
