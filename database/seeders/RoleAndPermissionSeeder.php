<?php

	namespace Database\Seeders;

	use Illuminate\Database\Seeder;
	use Spatie\Permission\Models\Permission;
	use Spatie\Permission\Models\Role;
	use Spatie\Permission\PermissionRegistrar;

	class RoleAndPermissionSeeder extends Seeder {
		/**
		 * Run the database seeds.
		 */
		public function run(): void {
			$application = app(PermissionRegistrar::class);
			// Reset cached roles and permissions
//			app()[PermissionRegistrar::class]->forgetCachedPermissions();
			$application->forgetCachedPermissions();

			// Create permissions
			$permissions = [
				// Product permissions
				'product.view',
				'product.create',
				'product.update',
				'product.delete',

				// Category permissions
				'category.view',
				'category.create',
				'category.update',
				'category.delete',

				// Brand permissions
				'brand.view',
				'brand.create',
				'brand.update',
				'brand.delete',

				// Inventory permissions
				'inventory.view',
				'inventory.create',
				'inventory.update',
				'inventory.delete',
				'inventory.adjust',

				// Warehouse permissions
				'warehouse.view',
				'warehouse.create',
				'warehouse.update',
				'warehouse.delete',
				'warehouse.manage',

				// Purchase Order permissions
				'purchase_order.view',
				'purchase_order.create',
				'purchase_order.update',
				'purchase_order.delete',
				'purchase_order.approve',
				'purchase_order.receive',

				// Sales Order permissions
				'sales_order.view',
				'sales_order.create',
				'sales_order.update',
				'sales_order.delete',
				'sales_order.approve',
				'sales_order.ship',

				// Supplier permissions
				'supplier.view',
				'supplier.create',
				'supplier.update',
				'supplier.delete',

				// Customer permissions
				'customer.view',
				'customer.create',
				'customer.update',
				'customer.delete',

				// Stock Transfer permissions
				'stock_transfer.view',
				'stock_transfer.create',
				'stock_transfer.update',
				'stock_transfer.delete',
				'stock_transfer.approve',
				'stock_transfer.receive',

				// Stock Adjustment permissions
				'stock_adjustment.view',
				'stock_adjustment.create',
				'stock_adjustment.update',
				'stock_adjustment.delete',
				'stock_adjustment.approve',

				// Stock Count permissions
				'stock_count.view',
				'stock_count.create',
				'stock_count.update',
				'stock_count.delete',
				'stock_count.complete',

				// Return permissions
				'return.view',
				'return.create',
				'return.update',
				'return.delete',
				'return.approve',

				// Payment permissions
				'payment.view',
				'payment.create',
				'payment.update',
				'payment.delete',

				// Report permissions
				'report.view',
				'report.financial',
				'report.inventory',
				'report.sales',
				'report.purchase',

				// User management permissions
				'user.view',
				'user.create',
				'user.update',
				'user.delete',

				// Role management permissions
				'role.view',
				'role.create',
				'role.update',
				'role.delete',

				// Settings permissions
				'settings.view',
				'settings.update',

				// Activity Log permissions
				'activity_log.view',
				'activity_log.delete',
			];

			foreach ($permissions as $permission) {
				Permission::create(['name' => $permission]);
			}

			// Create roles and assign permissions

			// 1. Admin Role - Full access
			$adminRole = Role::create(['name' => 'admin']);
			$adminRole->givePermissionTo(Permission::all());

			// 2. Warehouse Manager Role
			$warehouseManager = Role::create(['name' => 'warehouse_manager']);
			$warehouseManager->givePermissionTo([
				'product.view',
				'inventory.view',
				'inventory.create',
				'inventory.update',
				'inventory.adjust',
				'warehouse.view',
				'warehouse.update',
				'warehouse.manage',
				'stock_transfer.view',
				'stock_transfer.create',
				'stock_transfer.approve',
				'stock_transfer.receive',
				'stock_adjustment.view',
				'stock_adjustment.create',
				'stock_adjustment.approve',
				'stock_count.view',
				'stock_count.create',
				'stock_count.complete',
				'report.view',
				'report.inventory',
				'activity_log.view',
			]);

			// 3. Sales Manager Role
			$salesManager = Role::create(['name' => 'sales_manager']);
			$salesManager->givePermissionTo([
				'product.view',
				'inventory.view',
				'customer.view',
				'customer.create',
				'customer.update',
				'sales_order.view',
				'sales_order.create',
				'sales_order.update',
				'sales_order.approve',
				'sales_order.ship',
				'return.view',
				'return.create',
				'return.approve',
				'payment.view',
				'payment.create',
				'report.view',
				'report.sales',
				'activity_log.view',
			]);

			// 4. Purchase Manager Role
			$purchaseManager = Role::create(['name' => 'purchase_manager']);
			$purchaseManager->givePermissionTo([
				'product.view',
				'product.create',
				'product.update',
				'supplier.view',
				'supplier.create',
				'supplier.update',
				'inventory.view',
				'purchase_order.view',
				'purchase_order.create',
				'purchase_order.update',
				'purchase_order.approve',
				'purchase_order.receive',
				'return.view',
				'return.create',
				'payment.view',
				'payment.create',
				'report.view',
				'report.purchase',
				'activity_log.view',
			]);

			// 5. Inventory Clerk Role
			$inventoryClerk = Role::create(['name' => 'inventory_clerk']);
			$inventoryClerk->givePermissionTo([
				'product.view',
				'inventory.view',
				'warehouse.view',
				'stock_count.view',
				'stock_count.create',
				'stock_adjustment.view',
				'stock_transfer.view',
				'activity_log.view',
			]);

			// 6. Accountant Role
			$accountant = Role::create(['name' => 'accountant']);
			$accountant->givePermissionTo([
				'purchase_order.view',
				'sales_order.view',
				'payment.view',
				'payment.create',
				'payment.update',
				'customer.view',
				'supplier.view',
				'report.view',
				'report.financial',
				'report.sales',
				'report.purchase',
				'activity_log.view',
			]);

			// 7. Sales Representative Role
			$salesRep = Role::create(['name' => 'sales_representative']);
			$salesRep->givePermissionTo([
				'product.view',
				'inventory.view',
				'customer.view',
				'customer.create',
				'sales_order.view',
				'sales_order.create',
				'report.view',
				'report.sales',
			]);
		}
	}
