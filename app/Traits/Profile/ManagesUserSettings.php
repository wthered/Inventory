<?php

	namespace App\Traits\Profile;

	use Illuminate\Contracts\View\Factory;
	use Illuminate\Contracts\View\View;
	use Illuminate\Http\RedirectResponse;
	use Illuminate\Http\Request;

	trait ManagesUserSettings {

		/**
		 * Display the user preferences settings page.
		 */
		public function settings(Request $request): Factory|View {
			$preferences = session('preferences', [
				'per_page'    => $request->user()->per_page ?? 15,
				'timezone'    => config('app.timezone', 'Europe/Athens'),
				'locale'      => 'el',
				'date_format' => 'd/m/Y',
				'theme'       => 'light',
			]);

			return view('user.profile.settings', compact('preferences'));
		}

		/**
		 * Update user settings/preferences.
		 */
		public function setSettings(Request $request): RedirectResponse {
			$validated = $request->validate([
				'per_page' => 'required|integer|min:5|max:100',
				'timezone' => 'required|string',
			]);

			$user = $request->user();

			// Update database user record
			$user->account()->update([
				'per_page' => $validated['per_page'],
				// Add 'timezone' or JSON column 'preferences' here as needed
			]);

			// Update session state for instant access
			session(['preferences' => $validated]);

			return back()->with('success', 'Preferences updated successfully.');
		}
	}