<?php

	use App\Http\Middleware\SetLocale;
	use Illuminate\Foundation\Application;
	use Illuminate\Foundation\Configuration\Exceptions;
	use Illuminate\Foundation\Configuration\Middleware;

	return Application::configure(basePath: dirname(__DIR__))->withRouting(
		web: __DIR__.'/../routes/web.php',
		commands: __DIR__.'/../routes/console.php',
		health: '/up',
	)->withMiddleware(function (Middleware $middleware): void {
		$middleware->web(append: [
			SetLocale::class,
		]);

		// Καταχώρηση του Spatie Middleware Alias για το Laravel 11
		$middleware->alias([
			'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
			'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
		]);

	})->withExceptions(function (Exceptions $exceptions): void {
		//
	})->create();
