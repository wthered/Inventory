<?php

	namespace App\Http\Controllers\Auth;

	use App\Http\Controllers\Controller;
	use Illuminate\Http\Request;

	class PasswordController extends Controller {
		public function requestPasswordReset(Request $request) {
			dd($request->all());
		}

		public function emailPasswordReset(Request $request) {
			dd($request->all());
		}

		public function resetPassword(Request $request) {
			dd($request->all());
		}

		public function updatePassword(Request $request) {
			dd($request->all());
		}
	}
