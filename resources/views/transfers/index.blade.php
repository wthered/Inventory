@extends('templates.general')

@section('content')
    <div class="transfer-container">
        <div class="page-header">
            <h1 class="page-title">Stock Transfers</h1>
            <div class="header-actions">
                <a href="{{ route('transfers.create') }}" class="btn btn-primary">
                    + New Transfer
                </a>
            </div>
        </div>

        <div class="card">
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
                            <div class="route-display">
                                <span class="location">{{ $transfer->fromWarehouse->name }}</span>
                                <span class="arrow">&rarr;</span>
                                <span class="location">{{ $transfer->toWarehouse->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="item-count">{{ $transfer->items_count ?? $transfer->items->count() }}</span>
                        </td>
                        <td>
                        <span class="badge badge-{{ $transfer->status }}">
                            {{ ucfirst($transfer->status) }}
                        </span>
                        </td>
                        <td>
                            {{ $transfer->created_at->format('d M Y') }}
                        </td>
                        <td class="text-right">
                            <div class="action-links">
                                <a href="{{ route('transfers.show', $transfer) }}" class="link-view">View</a>
                                @if($transfer->status === 'draft')
                                    <a href="{{ route('transfers.edit', $transfer) }}" class="link-edit">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No stock transfers found in the ledger.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $transfers->links() }}
        </div>
    </div>

    <style>
        .transfer-container { padding: 20px; font-family: sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-title { font-size: 24px; color: #333; margin: 0; }

        .card { background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th { background: #f8f9fa; padding: 12px 15px; border-bottom: 2px solid #eee; color: #666; font-size: 13px; text-transform: uppercase; }
        .data-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }

        .trx-number { font-weight: bold; color: #2563eb; }
        .text-muted { color: #888; font-size: 12px; }

        .route-display { display: flex; align-items: center; font-weight: 500; }
        .arrow { margin: 0 10px; color: #999; font-size: 18px; }

        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-draft { background: #e5e7eb; color: #374151; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }

        .text-right { text-align: right; }
        .action-links a { text-decoration: none; font-size: 14px; margin-left: 10px; }
        .link-view { color: #2563eb; }
        .link-edit { color: #059669; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
    </style>
@endsection