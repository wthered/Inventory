<?php

	namespace App\Enums\HumanResources;

	enum EmployeeAttendanceStatus: string {
		case PRESENT  = 'present';
		case ABSENT   = 'absent';
		case LATE     = 'late';
		case ON_LEAVE = 'on_leave';
		case HALF_DAY = 'half_day';

		/**
		 * Get a user-friendly label for each status.
		 */
		public function label(): string {
			return __('human_resources.attendance_statuses.'.$this->value);
		}

		/**
		 * Get associated Hex Color code.
		 */
		public function hexColor(): string {
			return match ($this) {
				self::PRESENT  => '#10B981', // Green
				self::ABSENT   => '#EF4444', // Red
				self::LATE     => '#F59E0B', // Orange/Yellow
				self::ON_LEAVE => '#3B82F6', // Blue
				self::HALF_DAY => '#6B7280', // Gray
			};
		}

		/**
		 * Get CSS badge class name based on status value.
		 */
		public function badgeClass(): string {
			return 'badge-' . $this->value;
		}

		/**
		 * Get FontAwesome icon class.
		 */
		public function icon(): string {
			return match ($this) {
				self::PRESENT  => 'fas fa-check',
				self::LATE     => 'fas fa-exclamation-triangle',
				self::ABSENT   => 'fas fa-times',
				self::ON_LEAVE => 'fas fa-plane-departure',
				self::HALF_DAY => 'fas fa-adjust',
			};
		}
	}
