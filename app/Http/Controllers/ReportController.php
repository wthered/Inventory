<?php

	namespace App\Http\Controllers;

	use App\Models\Warehouse;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;

	class ReportController extends Controller {
		public function index(): Factory|View {
			return view('reports.index'); // a page linking to /reports/sales, /reports/inventory, etc.
		}

		public function generate() {}

		public function warehouse(Warehouse $warehouse) {}
	}
