<?php

	namespace App\Enums\HumanResources;

	enum CompanyDepartments: string {
		case HR        = 'Human Resources';
		case LOGISTICS = 'Logistics & Warehouse';
		case SALES     = 'Sales & Marketing';
		case IT        = 'Information Technology';
		case FINANCE   = 'Accounting & Finance';
		case SUPPORT   = 'Customer Support';

		public function code(): string {
			return match ($this) {
				self::HR        => 'DEP-HR',
				self::LOGISTICS => 'DEP-LOG',
				self::SALES     => 'DEP-SLS',
				self::IT        => 'DEP-IT',
				self::FINANCE   => 'DEP-FIN',
				self::SUPPORT   => 'DEP-SUP',
			};
		}
	}
