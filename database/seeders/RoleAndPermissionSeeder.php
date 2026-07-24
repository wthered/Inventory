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
			// Reset cached roles and permissions
			app()[PermissionRegistrar::class]->forgetCachedPermissions();

			// Create permissions
			$permissions = [
				// Product permissions
				'product.view', 'product.create', 'product.update', 'product.delete',
				// Category permissions
				'category.view', 'category.create', 'category.update', 'category.delete',
				// Brand permissions
				'brand.view', 'brand.create', 'brand.update', 'brand.delete',
				// Inventory permissions
				'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.adjust',
				// Warehouse permissions
				'warehouse.view', 'warehouse.create', 'warehouse.update', 'warehouse.delete', 'warehouse.manage',
				// Purchase Order permissions
				'purchase_order.view', 'purchase_order.create', 'purchase_order.update', 'purchase_order.delete',
				'purchase_order.approve', 'purchase_order.receive',
				// Sales Order permissions
				'sales_order.view', 'sales_order.create', 'sales_order.update', 'sales_order.delete',
				'sales_order.approve', 'sales_order.ship',
				// Supplier permissions
				'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
				// Customer permissions
				'customer.view', 'customer.create', 'customer.update', 'customer.delete',
				// Stock Transfer permissions
				'stock_transfer.view', 'stock_transfer.create', 'stock_transfer.update', 'stock_transfer.delete',
				'stock_transfer.approve', 'stock_transfer.receive',
				// Stock Adjustment permissions
				'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.update',
				'stock_adjustment.delete', 'stock_adjustment.approve',
				// Stock Count permissions
				'stock_count.view', 'stock_count.create', 'stock_count.update', 'stock_count.delete',
				'stock_count.complete',
				// Return permissions
				'return.view', 'return.create', 'return.update', 'return.delete', 'return.approve',
				// Payment permissions
				'payment.view', 'payment.create', 'payment.update', 'payment.delete',
				// Report permissions
				'report.view', 'report.financial', 'report.inventory', 'report.sales', 'report.purchase',
				// User management permissions
				'user.view', 'user.create', 'user.update', 'user.delete',
				// Role management permissions
				'role.view', 'role.create', 'role.update', 'role.delete',
				// Settings permissions
				'settings.view', 'settings.update',
				// Activity Log permissions
				'activity_log.view', 'activity_log.delete',
			];

			// Ασφαλής δημιουργία με firstOrCreate και ρητό guard
			foreach ($permissions as $permission) {
				Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
			}

			// 1. Admin Role - Full access
			$adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
			$adminRole->syncPermissions(Permission::all()); // Χρήση syncPermissions αντί για givePermissionTo

			// 2. Warehouse Manager Role
			$warehouseManager = Role::query()->firstOrCreate(['name' => 'warehouse_manager', 'guard_name' => 'web']);
			$warehouseManager->syncPermissions([
				'product.view',
				'inventory.view',
				'inventory.create',
				'inventory.update',
				'inventory.adjust',
				'warehouse.view',
				'warehouse.update',
				'warehouse.manage',
				'purchase_order.receive', // Προσθήκη: για να επιβεβαιώνει παραλαβές στην αποθήκη
				'sales_order.ship',       // Προσθήκη: για την τελική έξοδο από την αποθήκη
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
			$salesManager = Role::query()->firstOrCreate(['name' => 'sales_manager', 'guard_name' => 'web']);
			$salesManager->syncPermissions([
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
			$purchaseManager = Role::query()->firstOrCreate(['name' => 'purchase_manager', 'guard_name' => 'web']);
			$purchaseManager->syncPermissions([
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
			$inventoryClerk = Role::query()->firstOrCreate(['name' => 'inventory_clerk', 'guard_name' => 'web']);
			$inventoryClerk->syncPermissions([
				'product.view',
				'inventory.view',
				'warehouse.view',
				'purchase_order.receive', // Προσθήκη: Ο υπάλληλος παραλαμβάνει το inbound φορτίο
				'sales_order.ship',       // Προσθήκη: Ο υπάλληλος κάνει το pack & ship
				'stock_count.view',
				'stock_count.create',
				'stock_adjustment.view',
				'stock_transfer.view',
				'stock_transfer.receive',
				'activity_log.view',
			]);

			// 6. Accountant Role
			$accountant = Role::query()->firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
			$accountant->syncPermissions([
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
			$salesRep = Role::query()->firstOrCreate(['name' => 'sales_representative', 'guard_name' => 'web']);
			$salesRep->syncPermissions([
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
