<?php

	namespace App\Http\Middleware;

	use Closure;
	use Illuminate\Http\Request;
	use Psr\Container\ContainerExceptionInterface;
	use Psr\Container\NotFoundExceptionInterface;
	use Symfony\Component\HttpFoundation\Response;

	class SetLocale {
		/**
		 * Handle an incoming request.
		 *
		 * @param  Closure(Request): (Response)  $next
		 */
		public function handle(Request $request, Closure $next): Response {
			if (session()->has('locale')) {
				try {
					app()->setLocale(session()->get('locale'));
				} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
					// Αν αποτύχει, το Laravel θα χρησιμοποιήσει το config('app.locale')
				}
			}

			return $next($request);
		}
	}
