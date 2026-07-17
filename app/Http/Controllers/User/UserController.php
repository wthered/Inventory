<?php

	namespace App\Http\Controllers\User;

	use App\DataTransferObjects\UserDTO;
	use App\Enums\Purchases\PurchaseOrderStatus;
	use App\Http\Controllers\Controller;
	use App\Models\Customer;
	use App\Models\Product;
	use App\Models\Purchases\PurchaseOrder;
	use App\Models\User;
	use App\Models\Warehouse;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Support\Facades\Auth;

	class UserController extends Controller {
		public function index(): Factory|View {

			// 1. Φέρνουμε τον χρήστη μαζί με το account του (1 query αντί για 2+)
			$userModel = User::query()->with('account')->findOrFail(Auth::id());

			return view('user.dashboard', [
				// 2. Μετατρέπουμε το Model σε DTO χρησιμοποιώντας τη static μέθοδο
				'user'          => UserDTO::fromModel($userModel),
				'pending'       => PurchaseOrder::query()->where('status_id', PurchaseOrderStatus::AWAITING_APPROVAL->value)->count(),
				'warehouses'    => Warehouse::query()->count(),
				'customers'     => Customer::query()->where('is_active', true)->count(),
				'products'      => Product::query()->count(),
				'newItemsCount' => Product::query()->where('created_at', '>=', now()->startOfMonth())->count(),
			]);
		}
	}
