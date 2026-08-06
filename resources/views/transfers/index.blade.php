@extends('templates.general')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/movement.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stocks/transfers/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@endsection

@section('content')
    <div class="main-container">
        <!-- Ενιαίο Page Header σύμφωνα με το movement.css -->
        <div class="page-header">
            <div class="header-titles">
                <h1 class="page-title">Stock Transfers</h1>
                <p class="page-subtitle">Διαχείριση και παρακολούθηση μεταφορών μεταξύ αποθηκών</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('inventory.transfers.create') }}" class="btn btn-primary">
                    + New Transfer
                </a>
            </div>
        </div>

        <!-- Πίνακας Δεδομένων -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Transfer ID</th>
                    <th>Source & Destination</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td>
                            <div class="trx-number">{{ $transfer->transfer_number }}</div>
                            <small class="text-muted">By: {{ $transfer->creator->name ?? 'System' }}</small>
                        </td>
                        <td>
                            <!-- Custom διάταξη για τη διαδρομή της μεταφοράς -->
                            <div class="route-display">
                                <span class="location-badge">{{ $transfer->sourceWarehouse->name }}</span>
                                <span class="route-arrow">&rarr;</span>
                                <span class="location-badge">{{ $transfer->targetWarehouse->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="item-count">{{ $transfer->items_count ?? $transfer->items->count() }}</span>
                        </td>
                        <td>
                            <!-- Δυναμικό Badge βασισμένο στο ID ή Value του Enum -->
                            <span class="badge" style="--badge-color: {{ $transfer->status_id->color() }};">
                                {{ $transfer->status_id->label() }}
                            </span>
                        </td>
                        <td>
                            {{ $transfer->created_at->format('d M Y') }}
                        </td>
                        <td class="text-right">
                            <div class="action-links">
                                <a href="{{ route('inventory.transfers.show', $transfer) }}" class="link-view"
                                   title="Προβολή">
                                    View
                                </a>

                                @if($transfer->status_id === $status::PENDING)
                                    <a href="{{ route('inventory.transfers.edit', $transfer) }}" class="link-edit"
                                       title="Επεξεργασία">
                                        Edit
                                    </a>
                                @endif

                                @if(!$transfer->status_id->isFinalized())
                                    <form action="{{ route('inventory.transfers.destroy', $transfer) }}"
                                          method="POST"
                                          class="delete-form"
                                          onsubmit="return confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε/διαγράψετε αυτή τη μεταφορά;');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-link-delete" title="Διαγραφή">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-table-state">
                            No stock transfers found in the ledger.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $transfers->links('vendor.pagination.default_custom') }}
        </div>
    </div>
@endsection