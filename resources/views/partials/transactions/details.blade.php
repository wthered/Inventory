<div class="transaction-details">
	<div class="detail-section">
		<h3>Basic Information</h3>
		<div class="detail-grid">
			<div class="detail-item">
				<label>Reference Number</label>
				<span>${transaction.reference_number}</span>
			</div>
			<div class="detail-item">
				<label>Type</label>
				<span class="transaction-type type-${transaction.type}">
                                ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                            </span>
			</div>
			<div class="detail-item">
				<label>Date & Time</label>
				<span>${new Date(transaction.created_at).toLocaleString()}</span>
			</div>
			<div class="detail-item">
				<label>Quantity</label>
				<span class="quantity ${transaction.type === 'in' ? 'text-success' : 'text-danger'}">
                                ${transaction.type === 'in' ? '+' : '-'}${transaction.quantity}
                            </span>
			</div>
		</div>
	</div>

	<div class="detail-section">
		<h3>Location Information</h3>
		<div class="detail-grid">
			<div class="detail-item">
				<label>Warehouse</label>
				<span>${transaction.warehouse?.name || 'N/A'}</span>
			</div>
			<div class="detail-item">
				<label>Location</label>
				<span>${transaction.location?.name || 'N/A'}</span>
			</div>
		</div>
	</div>

	<div class="detail-section">
		<h3>User Information</h3>
		<div class="detail-grid">
			<div class="detail-item">
				<label>Created By</label>
				<span>${transaction.user?.name || 'System'}</span>
			</div>
			<div class="detail-item">
				<label>User Role</label>
				<span>${transaction.user?.role || 'N/A'}</span>
			</div>
		</div>
	</div>

	${transaction.notes ? `
	<div class="detail-section">
		<h3>Notes</h3>
		<div class="notes-content">
			${transaction.notes}
		</div>
	</div>
	` : ''}

	${transaction.metadata ? `
	<div class="detail-section">
		<h3>Additional Information</h3>
		<div class="metadata">
			<pre>${JSON.stringify(transaction.metadata, null, 2)}</pre>
		</div>
	</div>
	` : ''}
</div>
