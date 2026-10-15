<?php

	namespace App\Enums;

	enum RoleRank: int {
		case ADMIN = 100;

		// Managers / Controllers (80-99)
		case WAREHOUSE_MANAGER    = 90;
		case SALES_MANAGER        = 85;
		case PURCHASE_MANAGER     = 80;
		case FINANCIAL_CONTROLLER = 75;

		// Supervisors / Team Leads (50-79)
		case WAREHOUSE_SUPERVISOR = 60;
		case SALES_SUPERVISOR     = 55;

		// Operational Staff (10-49)
		case ACCOUNTANT           = 30;
		case INVENTORY_CLERK      = 25;
		case SALES_REPRESENTATIVE = 20;
		case STORE_CASHIER        = 10;

		public function label(): string {
			return match ($this) {
				self::ADMIN                => 'System Administrator',
				self::WAREHOUSE_MANAGER    => 'Warehouse Manager',
				self::SALES_MANAGER        => 'Sales Manager',
				self::PURCHASE_MANAGER     => 'Purchase Manager',
				self::FINANCIAL_CONTROLLER => 'Financial Controller',
				self::WAREHOUSE_SUPERVISOR => 'Warehouse Supervisor',
				self::SALES_SUPERVISOR     => 'Sales Supervisor',
				self::ACCOUNTANT           => 'Accountant',
				self::INVENTORY_CLERK      => 'Inventory Clerk',
				self::SALES_REPRESENTATIVE => 'Sales Representative',
				self::STORE_CASHIER        => 'Store Cashier',
			};
		}
	}