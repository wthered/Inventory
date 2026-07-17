<?php

	namespace App\Providers;

	use Illuminate\Support\ServiceProvider;
	use Illuminate\Support\Facades\View;
	use App\Http\View\Composers\GlobalDataComposer;

	class ViewServiceProvider extends ServiceProvider {
		/**
		 * Register services.
		 */
		public function register(): void {
			//
		}

		/**
		 * Bootstrap services.
		 */
		public function boot(): void {
			// Option A: Apply to ALL views using wildcard '*'
//			View::composer('*', GlobalDataComposer::class);

			// Option B: Apply only to specific views (e.g., layouts or admin panel)
			View::composer(['templates.general'], GlobalDataComposer::class);
		}
	}
