@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/stocks/returns/create.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}"/>
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Νέα Επιστροφή Αποθέματος</h2>
            </div>

            <div class="card-body">
                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger"
                         style="margin-bottom: 1.5rem; padding: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">
                        <ul style="margin: 0; padding-left: 1.2rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inventory.returns.store') }}" method="POST">
                    @csrf

                    <!-- RMA Number -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="rma_number" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Κωδικός
                            RMA:</label>
                        <input type="text"
                               name="rma_number"
                               id="rma_number"
                               class="form-control"
                               value="{{ old('rma_number', $suggestedRma) }}"
                               required
                               placeholder="π.χ. RMA-S-20260716-VTSGRQ"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        <small style="color: #666;">Προτεινόμενος μοναδικός κωδικός RMA.</small>
                    </div>

                    <!-- Return Date -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="return_date" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Ημερομηνία
                            Επιστροφής:</label>
                        <input type="date"
                               name="return_date"
                               id="return_date"
                               class="form-control"
                               value="{{ old('return_date', date('Y-m-d')) }}"
                               required
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <!-- Status Select (Includes status-themed badge classes in preview) -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="status"
                               style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Κατάσταση:</label>
                        <select name="status" id="status" class="form-control"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Carrier -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="carrier" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Μεταφορική
                            (Courier):</label>
                        <input type="text"
                               name="carrier"
                               id="carrier"
                               class="form-control"
                               value="{{ old('carrier') }}"
                               placeholder="π.χ. UPS, DHL, ACS"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <!-- Tracking Number -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label for="tracking_number" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Αριθμός
                            Αποστολής (Tracking Number):</label>
                        <input type="text"
                               name="tracking_number"
                               id="tracking_number"
                               class="form-control"
                               value="{{ old('tracking_number') }}"
                               placeholder="π.χ. TRK-FY792545921GR"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <!-- Form Buttons -->
                    <div class="form-actions" style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary"
                                style="padding: 0.6rem 1.2rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                            Αποθήκευση Επιστροφής
                        </button>
                        <a href="{{ route('inventory.returns.index') }}" class="btn btn-secondary"
                           style="padding: 0.6rem 1.2rem; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-flex; align-items: center;">
                            Ακύρωση
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection