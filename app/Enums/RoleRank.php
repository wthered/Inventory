<?php

	namespace App\Enums;

	use Illuminate\Support\Str;

	enum RoleRank: int {
		case ADMIN = 100;

		// Managers / Controllers (80-99)
		case WAREHOUSE_MANAGER    = 90;
		case SALES_MANAGER        = 85;
		case PURCHASE_MANAGER     = 80;
		case FINANCIAL_CONTROLLER = 75;
		case HR_MANAGER           = 70;

		// Supervisors / Team Leads (50-79)
		case WAREHOUSE_SUPERVISOR = 60;
		case SALES_SUPERVISOR     = 55;

		// Operational Staff (10-49)
		case ACCOUNTANT           = 30;
		case INVENTORY_CLERK      = 25;
		case SALES_REPRESENTATIVE = 20;
		case STORE_CASHIER        = 10;

		public function label(): string {
			return __('human_resources.employee_roles.'.Str::lower($this->name));
		}
	}
