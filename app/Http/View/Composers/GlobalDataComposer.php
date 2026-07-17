<?php

	namespace App\Http\View\Composers;

	use App\DataTransferObjects\UserDTO;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\View\View;

	class GlobalDataComposer {
		/**
		 * Bind data to the view.
		 */
		public function compose(View $view): void {
			$view->with('user', Auth::check() ? UserDTO::fromModel(Auth::user()) : null)
			     ->with('theme', 'dark'); // Example of another global variable
		}
	}