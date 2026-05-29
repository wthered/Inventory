document.addEventListener('DOMContentLoaded', () => {
	const sidebarToggle = document.getElementById('sidebar-toggle');
	const userInfo = document.getElementById('user-profile-toggle');
	const themeToggle = document.getElementById('theme-toggle'); // Το νέο button
	const htmlElement = document.documentElement;

	/* --- 1. Theme Logic (Light/Dark) --- */

	// Έλεγχος για αποθηκευμένη προτίμηση ή default του συστήματος
	const savedTheme = localStorage.getItem('theme') || 'dark'; // Force dark as default

	// Εφαρμογή του theme αμέσως
	htmlElement.setAttribute('data-theme', savedTheme);
	updateThemeIcon(savedTheme);

	themeToggle.addEventListener('click', () => {
		const currentTheme = htmlElement.getAttribute('data-theme');
		const newTheme = currentTheme === 'light' ? 'dark' : 'light';

		htmlElement.setAttribute('data-theme', newTheme);
		localStorage.setItem('theme', newTheme);
		updateThemeIcon(newTheme);
		console.log(`Theme switched to: ${newTheme}`);
	});

	function updateThemeIcon(theme) {
		const icon = themeToggle.querySelector('i');
		if (theme === 'dark') {
			icon.className = 'fas fa-sun'; // Ήλιος για να γυρίσει σε light
		} else {
			icon.className = 'fas fa-moon'; // Φεγγάρι για να γυρίσει σε dark
		}
	}

	/* --- 2. Sidebar Toggle Logic --- */

	sidebarToggle.addEventListener('click', () => {
		document.body.classList.toggle('sidebar-open');
		console.log('Sidebar toggle: class sidebar-open Toggled.');
	});

	/* --- 3. User Dropdown Toggle Logic --- */
	// Using a simple toggle for mobile/click and letting CSS handle hover for desktop is often cleaner.
	userInfo.addEventListener('click', (event) => {
		event.stopPropagation();
		userInfo.classList.toggle('active');
	});

	// Close when clicking anywhere else
	document.addEventListener('click', () => {
		userInfo.classList.remove('active');
	});

	// Hover behavior για desktop
	userInfo.addEventListener('mouseenter', () => {
		userInfo.classList.add('active');
	});

	userInfo.addEventListener('mouseleave', () => {
		setTimeout(() => {
			if (!userInfo.matches(':hover')) {
				userInfo.classList.remove('active');
			}
		}, 150);
	});

	/* --- 4. Layout Initial Check --- */

	if (window.innerWidth >= 768) {
		document.body.classList.add('sidebar-open');
	}
});