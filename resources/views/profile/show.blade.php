<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>User Profile</title>
	<link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/navigation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/footer.css') }}">
	<link rel="stylesheet" href="{{ asset('css/user/profile.css') }}">
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Inventory</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="{{ route('dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('inventory.products.index') }}">📦 Products</a></li>
        <li><a href="{{ route('inventory.warehouses.index') }}">🏭 Warehouses</a></li>
        <li><a href="{{ route('inventory.suppliers.index') }}">🚚 Suppliers</a></li>
        <li><a href="{{ route('inventory.customers.index') }}">👥 Customers</a></li>
        <li><a href="{{ route('inventory.invoices.index') }}">🧾 Invoices</a></li>
        <li><a href="{{ route('inventory.purchases.index') }}">💰 Purchases</a></li>
        <li><a href="{{ route('inventory.reports.index') }}">📊 Reports</a></li>
        <li><a href="{{ route('profile.settings.index') }}">⚙️ Settings</a></li>
    </ul>
</aside>

@include('common.navigation', ['user' => $user])

<div class="profile-container">
	<div class="profile-header">
		<div class="profile-avatar">
			<img src="{{ $user->avatar ?? 'https://image.tmdb.org/t/p/original/tc1ezEfIY8BhCy85svOUDtpBFPt.jpg' }}" alt="{{ $user->name ?? 'User Avatar' }}">
		</div>
		<h1>{{ $user->name ?? 'John Doe' }}</h1>
		<p>{{ $user->email ?? 'john@example.com' }}</p>
	</div>

	<form class="profile-form" method="POST" action="{{ route('profile.update') }}">
		@csrf

		<div class="input-group">
			<label for="name">Full Name</label>
			<input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" autocomplete="off" required>
		</div>

		<div class="input-group">
			<label for="email">Email Address</label>
			<input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" autocomplete="off" required>
		</div>

		<div class="input-group">
			<label for="password">Update Password</label>
			<input type="password" id="password" name="password_one" placeholder="Leave blank to keep current password">
		</div>

		<div class="input-group">
			<label for="password">Confirm Password</label>
			<input type="password" id="password" name="password_two" placeholder="Leave blank to keep current password">
		</div>

		<div class="profile-actions">
			<button type="submit" class="btn save">💾 Save Changes</button>
			<a href="{{ route('dashboard') }}" class="btn cancel">← Back</a>
		</div>
	</form>

	@if (session('status'))
		<p class="profile-status">{{ session('status') }}</p>
	@endif
</div>
@include('common.footer')

<script>
    // Sidebar toggle button
    const toggleButton = document.getElementById('sidebar-toggle');
    toggleButton.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-open');
    });
</script>
</body>
</html>
