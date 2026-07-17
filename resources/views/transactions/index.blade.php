@extends('templates.general')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/movement.css') }}">
	<link rel="stylesheet" href="{{ asset('css/stocks/transactions/index.css') }}">
@endsection

@section('content')
	<div class="main-container">

		<div class="page-header">
			<div class="header-titles">
				<h1 class="page-title">Διαχείριση Συναλλαγών</h1>
				<p class="page-subtitle">Δείτε και φιλτράρετε το ιστορικό όλων των κινήσεων της αποθήκης.</p>
			</div>
			<a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Νέα Συναλλαγή</a>
		</div>

		@if(session('success'))
			<div class="alert alert-success" id="success-alert">
				<span>{{ session('success') }}</span>
				<button type="button" class="alert-close-btn" onclick="document.getElementById('success-alert').remove()">&times;</button>
			</div>
		@endif

		<div class="filter-card">
			<form method="GET" action="{{ route('transactions.index') }}" class="filter-form">
				<div class="form-group">
					<label for="search" class="form-label">Αναζήτηση (Κωδικός, Προϊόν)</label>
					<input type="text" name="search" id="search" class="form-input" value="{{ request('search') }}" placeholder="π.χ. TRN-1024...">
				</div>

				<div class="form-group">
					<label for="type" class="form-label">Τύπος Κίνησης</label>
					<select name="type" id="type" class="form-select">
						<option value="">Όλοι οι τύποι</option>
						<option value="inflow" {{ request('type') == 'inflow' ? 'selected' : '' }}>Εισαγωγή</option>
						<option value="outflow" {{ request('type') == 'outflow' ? 'selected' : '' }}>Εξαγωγή</option>
					</select>
				</div>

				<div class="form-group">
					<label for="date" class="form-label">Ημερομηνία</label>
					<input type="date" name="date" id="date" class="form-input" value="{{ request('date') }}">
				</div>

				<div class="filter-actions">
					<button type="submit" class="btn btn-secondary">Φιλτράρισμα</button>
					<a href="{{ route('transactions.index') }}" class="btn btn-link">Καθαρισμός</a>
				</div>
			</form>
		</div>

		<div class="table-container">
			<table class="data-table">
				<thead>
				<tr>
					<th>ID Συναλλαγής</th>
					<th>Ημερομηνία & Ώρα</th>
					<th>Τύπος</th>
					<th>Προϊόν</th>
					<th>Ποσότητα</th>
					<th>Υπάλληλος</th>
					<th class="text-right">Ενέργειες</th>
				</tr>
				</thead>
				<tbody>
				@forelse($transactions as $transaction)
					<tr>
						<td class="fw-bold">#{{ $transaction->id }}</td>
						<td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
						<td>
							@if($transaction->type === 'inflow')
								<span class="badge badge-inflow">Εισαγωγή</span>
							@else
								<span class="badge badge-outflow">Εξαγωγή</span>
							@endif
						</td>
						<td>
							<div class="product-name">{{ $transaction->product->name }}</div>
							<div class="product-sku">SKU: {{ $transaction->product->sku }}</div>
						</td>
						<td class="fw-bold">{{ $transaction->quantity }} τεμ.</td>
						<td>{{ $transaction->employee->surname }} {{ $transaction->employee->name }}</td>
						<td class="text-right">
							<div class="action-buttons">
								<a href="{{ route('transactions.show', $transaction->id) }}" class="action-link view-link">Προβολή</a>
								<a href="{{ route('transactions.edit', $transaction->id) }}" class="action-link edit-link">Επεξεργασία</a>
							</div>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="7" class="empty-table-state">Δεν βρέθηκαν συναλλαγές.</td>
					</tr>
				@endforelse
				</tbody>
			</table>

			@if($transactions->hasPages())
				<div class="pagination-footer">
					<div class="pagination-info">
						Εμφάνιση {{ $transactions->firstItem() }} έως {{ $transactions->lastItem() }} από {{ $transactions->total() }} συναλλαγές
					</div>
					<div class="pagination-links">
						{{ $transactions->withQueryString()->links('pagination::simple-default') }}
					</div>
				</div>
			@endif
		</div>
	</div>
@endsection