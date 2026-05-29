<?php

	namespace App\Http\Controllers\Auth;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\User\Auth\LoginRequest;
	use App\Http\Requests\User\Auth\RegisterRequest;
	use App\Models\User;
	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;
	use Illuminate\Routing\Redirector;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Hash;

	class AuthController extends Controller {
		// Show login form
		public function showLoginForm(): Factory|View {
			return view('auth.login');
		}

		// Handle login request
		public function login(LoginRequest $request): RedirectResponse {
			$credentials = $request->validated();
			$user = User::query()->where('email', $credentials->get('email'));
			if ($user->exists()) {
				$user_input = $user->first();
				if(Hash::check($credentials->get('password'), $user_input['password'])) {
					Auth::login($user_input);
				}
				$request->session()->regenerate();
				return redirect()->intended(route('dashboard'));
			}
			return back()->withInput();
		}

		// Logout method (optional)
		public function logout(Request $request): Redirector|RedirectResponse {
			Auth::logout();

			$request->session()->invalidate();
			$request->session()->regenerateToken();

			return redirect('/login');
		}

		public function showRegistrationForm(): Factory|View {
			return view('auth.register');
		}

		public function register(RegisterRequest $request) {
			dd($request->validated());
		}
	}
