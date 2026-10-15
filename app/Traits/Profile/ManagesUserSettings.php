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
			$user = $request->user();

			$preferences = session('preferences', [
				'per_page'    => $user->account?->per_page ?? 15, // <--- Access via account relation
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

			// Update database account record
			if ($user->account) {
				$user->account->update([
					'per_page' => $validated['per_page'],
				]);
			}

			// Update session state for instant access
			session(['preferences' => $validated]);

			return back()->with('success', 'Preferences updated successfully.');
		}
	}