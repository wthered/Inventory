@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/adjustments/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/adjustments/show.css') }}">
@endsection

@section('content')
    <div class="main-container">

        {{-- Διακριτική Πλοήγηση (Breadcrumb Style) --}}
        <div class="back-action" style="margin-bottom: 1rem;">
            <a href="{{ route('inventory.adjustments.index') }}"
               style="text-decoration: none; color: #64748b; font-weight: 500; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                <span>←</span> Επιστροφή στις Προσαρμογές
            </a>
        </div>

        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Προσαρμογή Αποθέματος #{{ $adjustment->id }}</h1>
                <p class="page-subtitle">Αριθμός Εγγράφου:
                    <strong>{{ $adjustment->adjustment_number ?? 'Χωρίς Αριθμό' }}</strong></p>
            </div>

            <div class="header-actions" style="display: flex; gap: 0.75rem; align-items: center;">

                {{-- 🧩 ACTION SPLIT BUTTON GROUP --}}
                <div class="split-btn-group" style="position: relative; display: inline-flex; vertical-align: middle;">

                    {{-- Κύριο Κουμπί: Εκτελεί την υποβολή της φόρμας με την επιλεγμένη κατάσταση --}}
                    <form id="status-split-form" action="{{ route('inventory.adjustments.approve', $adjustment->id) }}"
                          method="POST" style="margin: 0; display: inline-flex;">
                        @csrf
                        @method('PATCH')

                        {{-- Διασφάλιση ότι παίρνουμε το value του Enum --}}
                        <input type="hidden" name="status" id="selected-status-value"
                               value="{{ $adjustment->status->value }}">

                        <button type="submit" id="main-action-btn" class="btn"
                                style="background-color: #0f172a; color: white; border: none; padding: 0.6rem 1.2rem; border-top-right-radius: 0; border-bottom-right-radius: 0; font-weight: 600; cursor: pointer; border-right: 1px solid #334155;"
                                onclick="return confirm('Επιβεβαίωση αλλαγής κατάστασης;');">
                            💾 Αποθήκευση Κατάστασης
                        </button>
                    </form>

                    {{-- Το Βέλος (Trigger για το Dropdown) --}}
                    <button type="button" id="dropdown-toggle-btn" class="btn"
                            style="background-color: #0f172a; color: white; border: none; padding: 0.6rem 0.8rem; border-top-left-radius: 0; border-bottom-left-radius: 0; cursor: pointer;"
                            onclick="toggleSplitDropdown()">
                        ▼
                    </button>

                    {{-- Το Μενού με τις Επιλογές των Status --}}
                    <ul id="split-dropdown-menu"
                        style="position: absolute; top: 100%; right: 0; z-index: 1000; display: none; min-width: 220px; padding: 0.5rem 0; margin: 0.25rem 0 0; font-size: 0.9rem; color: #1e293b; text-align: left; list-style: none; background-color: #ffffff; background-clip: padding-box; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">

                        @foreach($adjustmentStatuses as $status)
                            {{-- Προσθήκη διαχωριστικής γραμμής πριν την Ακύρωση (Canceled) --}}
                            @if($status->name === 'CANCELED')
                                <li>
                                    <hr style="height: 0; margin: 0.5rem 0; overflow: hidden; border-top: 1px solid #e2e8f0;">
                                </li>
                            @endif

                            <li>
                                <a class="dropdown-item" href="#" data-value="{{ $status->value }}"
                                   onclick="selectStatus(event, {{ $status->value }}, '{{ $status->icon() }} {{ $status->label() }}')"
                                   style="display: block; width: 100%; padding: 0.5rem 1.25rem; clear: both; font-weight: 500; color: {{ $status->name === 'CANCELED' ? '#ef4444' : '#334155' }}; text-decoration: none; white-space: nowrap; background: none; border: 0; cursor: pointer;">{{ $status->icon() }} {{ $status->label() }}
                                </a>
                            </li>
                        @endforeach

                    </ul>
                </div>

                {{-- Κουμπί Επεξεργασίας (Έλεγχος με βάση το Enum instance) --}}
                @if($canBeEdited)
                    <a href="{{ route('inventory.adjustments.edit', $adjustment->id) }}" class="btn btn-primary"
                       style="padding: 0.6rem 1.2rem; border-radius: 4px;">
                        ✏️ Επεξεργασία
                    </a>
                @endif
            </div>
        </div>

        {{-- Header Information Grid --}}
        <div class="details-grid">
            <div class="info-box">
                <div class="info-label">Αποθήκη</div>
                <div class="info-value">{{ $adjustment->warehouse->name ?? 'Default Warehouse' }}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Ημερομηνία Παραστατικού</div>
                <div class="info-value">{{ $adjustment->adjustment_date->format('Y-m-d') }}</div>
            </div>

            {{-- INFO: Δημιουργία --}}
            <div class="info-box">
                <div class="info-label">Δημιουργήθηκε Από</div>
                <div class="info-value">
                    {{ $adjustment->creator->account->full_name ?? 'Άγνωστος' }}
                    <span style="display: block; font-size: 0.8rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">
				        📅 {{ $adjustment->created_at ?? '-' }}
			        </span>
                </div>
            </div>

            {{-- INFO: Δυναμική Κατάσταση με χρήση του MovementStatus Enum --}}
            <div class="info-box"
                 style="border-left: 4px solid {{ $statusBorderColor }}; background-color: {{ $statusColor }};">
                <div class="info-label" style="color: #64748b;">Κατάσταση</div>
                <div class="info-value" style="color: #1e293b;">
                    {{ $adjustment->status->label() }}
                    <span style="display: block; font-size: 0.8rem; color: #64748b; font-weight: normal; margin-top: 0.25rem;">
						@if($adjustment->approved_at)
                            Εγκρίθηκε από: {{ $adjustment->approver->account->full_name ?? 'Admin' }}
                            <br>
                            📅 {{ $adjustment->approved_at }}
                        @else
                            Το παραστατικό δεν έχει οριστικοποιηθεί.
                        @endif
					</span>
                </div>
            </div>
        </div>

        {{-- Global Notes Box (Εμφανίζεται μόνο αν υπάρχουν σημειώσεις) --}}
        @if(!empty($adjustment->notes))
            <div class="notes-box">
                <div class="info-label" style="color: #856404; margin-bottom: 0.5rem;">Σημειώσεις / Παρατηρήσεις
                    Εγγράφου
                </div>
                <div class="notes-text">{{ $adjustment->notes }}</div>
            </div>
        @endif

        {{-- Items Table Section --}}
        <div class="table-container" style="margin-top: 2rem;">
            <div style="background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #e3e6f0; font-weight: bold; color: #4e73df;">
                📦 Αναλυτικές Γραμμές Μεταβολών
            </div>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Προϊόν</th>
                    <th>Θέση (Location)</th>
                    <th>Αιτιολογία</th>
                    <th class="text-right">Απόθεμα Πριν</th>
                    <th class="text-right">Ποσότητα Μεταβολής</th>
                    <th class="text-right">Απόθεμα Μετά</th>
                </tr>
                </thead>
                <tbody>
                @forelse($adjustment->items as $item)
                    <tr>
                        <td>
                            <span class="product-name">{{ $item->product->name ?? 'Άγνωστο Προϊόν' }}</span>
                            <br>
                            <span class="product-sku">SKU: {{ $item->product->sku ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $item->location->name ?? 'Default Location' }}</span>
                        </td>
                        <td>
                            {{ $item->reason->label() }}
                        </td>
                        <td class="text-right fw-bold" style="color: #64748b;">
                            {{ $item->quantity_before }} τεμ.
                        </td>
                        <td class="text-right">
                            @if(!$item->isNegative && $item->quantity > 0)
                                <span class="adjustment-qty qty-positive">+{{ abs($item->quantity) }} τεμ.</span>
                            @else
                                <span class="adjustment-qty qty-negative">-{{ abs($item->quantity) }} τεμ.</span>
                            @endif
                        </td>
                        <td class="text-right fw-bold" style="color: #1e293b;">
                            {{ $item->quantity_after }} τεμ.
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-table-state">Δεν βρέθηκαν γραμμές προϊόντων για αυτή την
                            προσαρμογή.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/stocks/adjustments/show.js') }}"></script>
@endsection