@extends('templates.general')

@section('title', 'Create Customer')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/customers/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customers/create.css') }}">
@endsection

@section('content')
    <div class="page-container">

        {{-- Validation Errors Feedback --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="ri-error-warning-line"></i>
                <div>
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-title-group">
                <h1>
                    <i class="ri-user-add-line icon-accent"></i>
                    <span>Create New Customer</span>
                </h1>
                <p class="subtitle">Add a new customer profile, setup billing attributes, and assign initial credit
                    terms</p>
            </div>

            <div class="action-buttons-group">
                <a href="{{ route('inventory.customers.index') }}" class="btn-action btn-show"
                   title="Back to Customer List">
                    <i class="ri-arrow-left-line"></i>
                </a>
            </div>
        </div>

        {{-- Form Container --}}
        <form action="{{ route('inventory.customers.store') }}" method="POST" class="form-card">
            @include('customers.partials.form')
        </form>

    </div>
@endsection

@section('scripts')
    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('country_id');
            const citySelect = document.getElementById('city_id');
            const stateInput = document.getElementById('state');
            const postalCodeInput = document.getElementById('postal_code');
            const countrySelectError = document.getElementById('country-select-error');

            const initialCityId = @json(old('city_id'));
            let citiesData = [];

            function fetchCities(countryId, selectedCityId = null) {
                if (!countryId) {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    citySelect.disabled = true;
                    citiesData = [];
                    return;
                }

                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Loading cities...</option>';

                fetch(`/countries/${countryId}/cities`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    }
                }).then(response => {
                    if (!response.ok) throw new Error('Failed to fetch cities');
                    return response.json();
                }).then(data => {
                    citiesData = data;
                    citySelect.innerHTML = data.options;
                    citySelect.disabled = false;
                    if (selectedCityId) {
                        citySelect.value = selectedCityId;
                    }
                }).catch(error => {
                    console.error('Error fetching cities:', error);
                    citySelect.innerHTML = '<option value="">Failed to load cities</option>';
                });
            }

            countrySelect.addEventListener('change', function () {
                if (this.value) {
                    countrySelectError?.classList.add('d-none');
                }
                fetchCities(this.value);
            });

            citySelect.addEventListener('change', function () {
                const selectedCity = citiesData.find(c => c.id === this.value);
                if (selectedCity) {
                    if (selectedCity.state && !stateInput.value) {
                        stateInput.value = selectedCity.state;
                    }
                    if (selectedCity.postal_code && !postalCodeInput.value) {
                        postalCodeInput.value = selectedCity.postal_code;
                    }
                }
            });

            if (countrySelect.value) {
                fetchCities(countrySelect.value, initialCityId);
            }
        });
    </script>
    <script src="{{ asset('js/customers/states.js') }}"></script>
@endsection