@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}"/>
    
@endsection

@section('content')
    <div class="main-container">

        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Επεξεργασία Επιστροφής</h1>
                <p class="page-subtitle">Τροποποιήστε τα στοιχεία της επιστροφής #{{ $return->return_number }}</p>
            </div>
            <a href="{{ route('inventory.returns.index') }}" class="btn btn-secondary">← Πίσω στη Λίστα</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" id="error-alert">
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <strong>Διορθώστε τα παρακάτω σφάλματα:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close-btn" onclick="document.getElementById('error-alert').remove()">
                    &times;
                </button>
            </div>
        @endif

        <div class="filter-card" style="max-width: 800px; margin: 0 auto;">
            <form action="{{ route('inventory.returns.update', ['return' => $return]) }}" method="POST"
                  style="display: flex; flex-direction: column; gap: 1.5rem;">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">

                    <div class="form-group">
                        <label class="form-label">Κωδικός Επιστροφής (Return Number)</label>
                        <input type="text" class="form-input" value="{{ $return->return_number }}" readonly
                               style="background-color: #f1f5f9; cursor: not-allowed; color: #64748b;">
                    </div>

                    <div class="form-group">
                        <label for="rma_number" class="form-label">Κωδικός RMA</label>
                        <input type="text" name="rma_number" id="rma_number" class="form-input"
                               value="{{ old('rma_number', $return->rma_number) }}" placeholder="π.χ. RMA-12345">
                    </div>

                    <div class="form-group">
                        <label for="return_date" class="form-label">Ημερομηνία Επιστροφής</label>
                        <input type="date" name="return_date" id="return_date" class="form-input"
                               value="{{ old('return_date', $return->return_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Κατάσταση</label>
                        <select name="status" id="status" class="form-select" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', $return->status->value) === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">

                    <div class="form-group">
                        <label for="carrier" class="form-label">Μεταφορική / Courier</label>
                        <input type="text" name="carrier" id="carrier" class="form-input"
                               value="{{ old('carrier', $return->carrier) }}"
                               placeholder="π.χ. ACS, Γενική Ταχυδρομική">
                    </div>

                    <div class="form-group">
                        <label for="tracking_number" class="form-label">Αριθμός Αποστολής (Tracking Number)</label>
                        <input type="text" name="tracking_number" id="tracking_number" class="form-input"
                               value="{{ old('tracking_number', $return->tracking_number) }}"
                               placeholder="π.χ. 1234567890">
                    </div>

                </div>

                <div class="filter-actions"
                     style="justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 1.5rem; margin-top: 0.5rem;">
                    <a href="{{ route('inventory.returns.index') }}" class="btn btn-link">Ακύρωση</a>
                    <button type="submit" class="btn btn-primary">Ενημέρωση Επιστροφής</button>
                </div>

            </form>
        </div>

    </div>
@endsection