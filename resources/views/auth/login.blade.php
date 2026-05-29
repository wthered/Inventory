<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login</title>
	<link rel="stylesheet" href="{{ asset('css/style.css') }}" />
	<link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
</head>
<body>
<div class="auth-container">
	<form class="auth-form" method="POST" action="{{ route('auth.sign.in') }}">

		<div class="input-group">
			<label for="email">Email</label>
			<input type="email" id="email" name="email" value="{{ old('email') }}"  required autofocus>
			@error('email')
				<p class="error-message">{{ $message }}</p>
			@enderror
		</div>

		<div class="input-group">
			<label for="password">Password</label>
			<input type="password" id="password" name="password" required>
			@error('password')
				<p class="error-message">{{ $message }}</p>
			@enderror
		</div>

		@csrf
		<button type="submit" class="auth-button">Login</button>
	</form>

	<div class="auth-links">
		<a href="{{ route('register') }}">Create an account</a>
		<span>•</span>
		<a href="{{ route('password.request') }}">Forgot password?</a>
	</div>
</div>

</body>
</html>