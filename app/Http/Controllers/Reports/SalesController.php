<?php

	namespace App\Http\Controllers\Reports;

	use App\Http\Controllers\Controller;
	use App\Models\Product;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;

	class SalesController extends Controller {
		public function index(): Factory|View {
			// Example: placeholder sales data
			$topProducts = Product::query()->inRandomOrder()->take(10)->get();
			$totalValue = $topProducts->sum('price'); // assuming `price` column exists

			return view('reports.sales', compact('topProducts', 'totalValue'));
		}
	}
