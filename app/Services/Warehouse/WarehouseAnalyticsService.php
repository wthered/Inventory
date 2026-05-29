<?php

	namespace App\Services\Warehouse;

	use App\Models\Warehouse;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\DB;

	class WarehouseAnalyticsService {

		/**
		 * Get the 10 most recently updated inventory records
		 */
		public function getLatestInventories(Warehouse $warehouse): Collection {
			return $warehouse
				->inventories()
				->with(['product'])
				->latest()
				->take(10)
				->get();
		}

		/**
		 * Calculate total monetary value of all products in this warehouse
		 */
		public function calculateWarehouseValue(int $warehouseId): float {
			$total = DB::table('inventories')
				->join('products', 'inventories.product_id', '=', 'products.id')
				->where('inventories.warehouse_id', $warehouseId)
				->whereNull('products.deleted_at')
				->selectRaw('SUM(inventories.quantity * products.cost_price) as total_value')
				->value('total_value');

			return (float) ($total ?? 0);
		}

		/**
		 * Get recent transactions with formatted descriptions
		 */
		public function getRecentActivities(int $warehouseId): Collection {
			return DB::table('inventory_transactions')
				->join('products', 'inventory_transactions.product_id', '=', 'products.id')
				->leftJoin('users', 'inventory_transactions.created_by', '=', 'users.id')
				->where('inventory_transactions.warehouse_id', $warehouseId)
				->select([
					'inventory_transactions.*',
					'products.name as product_name',
					'products.sku as product_sku',
					'users.name as user_name'
				])
				->latest('inventory_transactions.created_at')
				->limit(10)
				->get()
				->map(fn($transaction) => $this->formatActivity($transaction));
		}

		private function formatActivity($transaction): object {
			$sourceType = class_basename($transaction->reference_type);
			$icons      = [
				'in'         => '📥',
				'out'        => '📤',
				'transfer'   => '🔄',
				'adjustment' => '🛠️'
			];

			return (object) [
				'id'          => $transaction->id,
				'icon'        => $icons[$transaction->type] ?? '📋',
				'description' => "{$transaction->product_name} ({$transaction->product_sku}): " . ucfirst($transaction->reason) . " via {$sourceType} #{$transaction->reference_id}",
				'user'        => $transaction->user_name ?? 'System',
				'quantity'    => $transaction->quantity,
				'time_ago'    => $transaction->updated_at,
				'type'        => $transaction->type,
			];
		}

		/**
		 * Get the count of incoming transfers for the current month
		 */
		public function getMonthlyTransferCount(Warehouse $warehouse): int {
			return $warehouse->incomingTransfers()->whereMonth('created_at', now()->month)->count();
		}
	}