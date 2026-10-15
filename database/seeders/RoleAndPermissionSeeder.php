<?php

	namespace Database\Seeders;

	use App\Enums\RoleRank;
	use Illuminate\Database\Seeder;
	use Spatie\Permission\Models\Permission;
	use Spatie\Permission\Models\Role;
	use Spatie\Permission\PermissionRegistrar;

	class RoleAndPermissionSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			// Reset cached roles and permissions
			app()[PermissionRegistrar::class]->forgetCachedPermissions();

			// Create permissions
			$permissions = [
				// User & Profile permissions
				'profile.view',
				'user.view', 'user.create', 'user.update', 'user.delete',
				'role.view', 'role.create', 'role.update', 'role.delete',

				// Product & Master Data permissions
				'product.view', 'product.create', 'product.update', 'product.delete',
				'category.view', 'category.create', 'category.update', 'category.delete',
				'brand.view', 'brand.create', 'brand.update', 'brand.delete',
				'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
				'customer.view', 'customer.create', 'customer.update', 'customer.delete',

				// Inventory & Warehouse permissions
				'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.adjust',
				'warehouse.view', 'warehouse.create', 'warehouse.update', 'warehouse.delete', 'warehouse.manage',

				// Orders & Transactions
				'purchase_order.view', 'purchase_order.create', 'purchase_order.update', 'purchase_order.delete',
				'purchase_order.approve', 'purchase_order.receive',

				'sales_order.view', 'sales_order.create', 'sales_order.update', 'sales_order.delete',
				'sales_order.approve', 'sales_order.ship',

				'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete',
				'stock_transfer.approve', 'stock_transfer.receive',

				'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.update',
				'stock_adjustment.delete',
				'stock_adjustment.approve',

				'stock_count.view', 'stock_count.create', 'stock_count.update', 'stock_count.delete',
				'stock_count.complete',

				'return.view', 'return.create', 'return.update', 'return.delete', 'return.approve',
				'payment.view', 'payment.create', 'payment.update', 'payment.delete',

				// Reports & Logs
				'report.view', 'report.financial', 'report.inventory', 'report.sales', 'report.purchase',
				'settings.view', 'settings.update',
				'activity_log.view', 'activity_log.delete',
			];

			// Δημιουργία Permissions
			foreach ($permissions as $permission) {
				Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
			}

			// ==========================================
			// 1. ADMIN (Rank: 100)
			// ==========================================
			$adminRole = Role::query()->firstOrCreate([
				'name' => 'admin', 'guard_name' => 'web', 'rank' => RoleRank::ADMIN->value
			]);
			$adminRole->syncPermissions(Permission::all());

			// ==========================================
			// 2. WAREHOUSE MANAGER (Rank: 90)
			// ==========================================
			$warehouseManager = Role::query()->firstOrCreate([
				'name' => 'warehouse_manager', 'guard_name' => 'web', 'rank' => RoleRank::WAREHOUSE_MANAGER->value
			]);
			$warehouseManager->syncPermissions([
				'profile.view', 'product.view', 'category.view', 'brand.view', 'supplier.view',
				'inventory.view', 'inventory.create', 'inventory.update', 'inventory.adjust',
				'warehouse.view', 'warehouse.update', 'warehouse.manage',
				'purchase_order.view', 'purchase_order.receive',
				'sales_order.view', 'sales_order.ship',
				'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.approve', 'stock_transfer.receive',
				'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.approve',
				'stock_count.view', 'stock_count.create', 'stock_count.complete',
				'return.view', 'return.approve',
				'report.view', 'report.inventory', 'activity_log.view',
			]);

			// ==========================================
			// 3. SALES MANAGER (Rank: 85)
			// ==========================================
			$salesManager = Role::query()->firstOrCreate([
				'name' => 'sales_manager', 'guard_name' => 'web', 'rank' => RoleRank::SALES_MANAGER->value
			]);
			$salesManager->syncPermissions([
				'profile.view', 'product.view', 'category.view', 'brand.view', 'inventory.view',
				'customer.view', 'customer.create', 'customer.update', 'customer.delete',
				'sales_order.view', 'sales_order.create', 'sales_order.update', 'sales_order.approve',
				'return.view', 'return.create', 'return.approve',
				'payment.view', 'payment.create',
				'report.view', 'report.sales', 'activity_log.view',
			]);

			// ==========================================
			// 4. PURCHASE MANAGER (Rank: 80)
			// ==========================================
			$purchaseManager = Role::query()->firstOrCreate([
				'name' => 'purchase_manager', 'guard_name' => 'web', 'rank' => RoleRank::PURCHASE_MANAGER->value
			]);
			$purchaseManager->syncPermissions([
				'profile.view', 'product.view', 'product.create', 'product.update',
				'category.view', 'brand.view', 'inventory.view',
				'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
				'purchase_order.view', 'purchase_order.create', 'purchase_order.update', 'purchase_order.approve',
				'return.view', 'return.create',
				'payment.view', 'payment.create',
				'report.view', 'report.purchase', 'activity_log.view',
			]);

			// ==========================================
			// 5. FINANCIAL CONTROLLER (Rank: 75)
			// ==========================================
			$financialController = Role::query()->firstOrCreate([
				'name' => 'financial_controller', 'guard_name' => 'web', 'rank' => RoleRank::FINANCIAL_CONTROLLER->value
			]);
			$financialController->syncPermissions([
				'profile.view', 'product.view', 'inventory.view', 'customer.view', 'supplier.view',
				'purchase_order.view', 'sales_order.view',
				'payment.view', 'payment.create', 'payment.update', 'payment.delete',
				'return.view', 'return.approve',
				'report.view', 'report.financial', 'report.sales', 'report.purchase', 'report.inventory',
				'activity_log.view',
			]);

			// ==========================================
			// 6. WAREHOUSE SUPERVISOR (Rank: 60)
			// ==========================================
			$warehouseSupervisor = Role::query()->firstOrCreate([
				'name' => 'warehouse_supervisor', 'guard_name' => 'web', 'rank' => RoleRank::WAREHOUSE_SUPERVISOR->value
			]);
			$warehouseSupervisor->syncPermissions([
				'profile.view', 'product.view', 'inventory.view', 'inventory.adjust',
				'warehouse.view', 'purchase_order.receive', 'sales_order.ship',
				'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.receive',
				'stock_adjustment.view', 'stock_adjustment.create',
				'stock_count.view', 'stock_count.create',
				'activity_log.view',
			]);

			// ==========================================
			// 7. SALES SUPERVISOR (Rank: 55)
			// ==========================================
			$salesSupervisor = Role::query()->firstOrCreate([
				'name' => 'sales_supervisor', 'guard_name' => 'web', 'rank' => RoleRank::SALES_SUPERVISOR->value
			]);
			$salesSupervisor->syncPermissions([
				'profile.view', 'product.view', 'inventory.view',
				'customer.view', 'customer.create', 'customer.update',
				'sales_order.view', 'sales_order.create', 'sales_order.update', 'sales_order.approve',
				'return.view', 'return.create',
				'report.view', 'report.sales',
			]);

			// ==========================================
			// 8. ACCOUNTANT (Rank: 30)
			// ==========================================
			$accountant = Role::query()->firstOrCreate([
				'name' => 'accountant', 'guard_name' => 'web', 'rank' => RoleRank::ACCOUNTANT->value
			]);
			$accountant->syncPermissions([
				'profile.view', 'purchase_order.view', 'sales_order.view',
				'payment.view', 'payment.create', 'payment.update',
				'customer.view', 'supplier.view',
				'report.view', 'report.financial', 'report.sales', 'report.purchase',
				'activity_log.view',
			]);

			// ==========================================
			// 9. INVENTORY CLERK (Rank: 25)
			// ==========================================
			$inventoryClerk = Role::query()->firstOrCreate([
				'name' => 'inventory_clerk', 'guard_name' => 'web', 'rank' => RoleRank::INVENTORY_CLERK->value
			]);
			$inventoryClerk->syncPermissions([
				'profile.view', 'product.view', 'inventory.view', 'warehouse.view',
				'purchase_order.receive', 'sales_order.ship',
				'stock_count.view', 'stock_count.create',
				'stock_transfer.view', 'stock_transfer.receive',
				'activity_log.view',
			]);

			// ==========================================
			// 10. SALES REPRESENTATIVE (Rank: 20)
			// ==========================================
			$salesRep = Role::query()->firstOrCreate([
				'name' => 'sales_representative', 'guard_name' => 'web', 'rank' => RoleRank::SALES_REPRESENTATIVE->value
			]);
			$salesRep->syncPermissions([
				'profile.view', 'product.view', 'inventory.view',
				'customer.view', 'customer.create',
				'sales_order.view', 'sales_order.create',
				'report.view', 'report.sales',
			]);

			// ==========================================
			// 11. STORE CASHIER (Rank: 10)
			// ==========================================
			$cashier = Role::query()->firstOrCreate([
				'name' => 'store_cashier', 'guard_name' => 'web', 'rank' => RoleRank::STORE_CASHIER->value
			]);
			$cashier->syncPermissions([
				'profile.view', 'product.view', 'inventory.view',
				'sales_order.view', 'sales_order.create',
				'payment.view', 'payment.create',
			]);
		}
	}
