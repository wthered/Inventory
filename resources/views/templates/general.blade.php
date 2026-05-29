<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title')</title>
	<!-- We'll use a single <style> block containing all necessary styles -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&display=swap" />
	<!-- Font Awesome CDN for general icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
	<link rel="stylesheet" href = "{{ asset('css/style.css') }}">
	<link rel="stylesheet" href = "{{ asset('css/templates/navigation.css') }}"/>
	@yield('styles')
	<link rel = "stylesheet" href = "{{ asset('css/templates/footer.css') }}"/>
</head>
<body>
@include('common.navigation')

<!-- Προσθήκη του container για να δουλεύουν τα CSS styles σου -->
<main id="box">
	@yield('content')
</main>

@include('common.footer')
<script type="application/javascript" src="{{ asset('js/navigation.js') }}"></script>
@yield('scripts')
</body>
</html>