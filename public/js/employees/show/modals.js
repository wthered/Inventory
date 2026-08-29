document.addEventListener('DOMContentLoaded', () => {
	// Utility functions to toggle modal visibility
	const openModal = (modalId) => {
		const modal = document.getElementById(modalId);
		if (modal) modal.style.display = 'flex';
	};

	const closeModal = (modalId) => {
		const modal = document.getElementById(modalId);
		if (modal) {
			modal.style.display = 'none';
			const form = modal.querySelector('form');
			if (form) form.reset();
		}
	};

	// Leave Modal Triggers
	const openLeaveBtn = document.getElementById('btn-open-leave-modal');
	const closeLeaveBtn = document.getElementById('btn-close-leave-modal');
	const cancelLeaveBtn = document.getElementById('btn-cancel-leave-modal');
	const leaveBackdrop = document.getElementById('leave-modal-backdrop');

	if (openLeaveBtn) openLeaveBtn.addEventListener('click', () => openModal('leave-modal-backdrop'));
	if (closeLeaveBtn) closeLeaveBtn.addEventListener('click', () => closeModal('leave-modal-backdrop'));
	if (cancelLeaveBtn) cancelLeaveBtn.addEventListener('click', () => closeModal('leave-modal-backdrop'));

	// Attendance Modal Triggers
	const openAttendanceBtn = document.getElementById('btn-open-attendance-modal');
	const closeAttendanceBtn = document.getElementById('btn-close-attendance-modal');
	const cancelAttendanceBtn = document.getElementById('btn-cancel-attendance-modal');
	const attendanceBackdrop = document.getElementById('attendance-modal-backdrop');

	if (openAttendanceBtn) openAttendanceBtn.addEventListener('click', () => openModal('attendance-modal-backdrop'));
	if (closeAttendanceBtn) closeAttendanceBtn.addEventListener('click', () => closeModal('attendance-modal-backdrop'));
	if (cancelAttendanceBtn) cancelAttendanceBtn.addEventListener('click', () => closeModal('attendance-modal-backdrop'));

	// Close modals when clicking outside the dialog content
	window.addEventListener('click', (e) => {
		if (e.target === leaveBackdrop) closeModal('leave-modal-backdrop');
		if (e.target === attendanceBackdrop) closeModal('attendance-modal-backdrop');
	});

	// Close on Escape key press
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			closeModal('leave-modal-backdrop');
			closeModal('attendance-modal-backdrop');
		}
	});
});
