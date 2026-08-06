<tr>
    <td class="fw-bold">
        <a href="{{ route('inventory.adjustments.show', $adjustment->id) }}"
           style="color: #4e73df; text-decoration: none;">
            {{ $adjustment->adjustment_number ?? '#'.$adjustment->id }}
        </a>
    </td>
    <td>{{ $adjustment->adjustment_date->format('Y-m-d') }}</td>
    <td>
        @if($adjustment->items->isNotEmpty())
            <div class="products-list-cell">
                {{-- Δείχνουμε αναλυτικά τα πρώτα 2 προϊόντα της προσαρμογής --}}
                @foreach($adjustment->items->take(2) as $item)
                    <div class="product-item-badge">
                        <span class="product-name">{{ $item->product->name ?? 'Άγνωστο Προϊόν' }}</span>
                        <span class="product-sku">(SKU: {{ $item->product->sku ?? '-' }})</span>

                        {{-- Μικρό σήμα αιτιολογίας ανά γραμμή --}}
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.25rem;">
												[@switch($item->reason)
                                @case('damage') Κατεστραμμένο @break
                                @case('lost') Κλοπή/Απώλεια @break
                                @case('recount') Λάθος Καταμ. @break
                                @case('found') Εύρεση @break
                                @default {{ $item->reason }}
                            @endswitch]
											</span>
                    </div>
                @endforeach

                {{-- Αν έχει παραπάνω από 2 γραμμές, βάζουμε ένα έξυπνο badge --}}
                @if($adjustment->items->count() > 2)
                    <div style="padding-left: 0.5rem;">
                        <span class="badge-count">+{{ $adjustment->items->count() - 2 }} ακόμη γραμμές</span>
                    </div>
                @endif
            </div>
        @else
            <div class="text-muted" style="font-style: italic;">Χωρίς γραμμές προϊόντων</div>
        @endif
    </td>
    <td>
        {{-- Υπολογισμός και σωστή απεικόνιση των συνολικών τεμαχίων --}}
        @php
            $totalQty = $adjustment->items->sum('quantity');
            // Ελέγχουμε αν όλα τα items είναι εξερχόμενα ("out")
            $allOut = $adjustment->items->every(fn($i) => $i->type === 'out' || $i->type === 'decrease');
        @endphp

        @if($allOut)
            <span class="adjustment-qty qty-negative">-{{ abs($totalQty) }} τεμ.</span>
        @elseif($adjustment->items->every(fn($i) => $i->type === 'in' || $i->type === 'increase'))
            <span class="adjustment-qty qty-positive">+{{ abs($totalQty) }} τεμ.</span>
        @else
            {{-- Μικτή κίνηση (κάποια + και κάποια -) --}}
            <span class="adjustment-qty"
                  style="background: #e2e8f0; color: #334155;">Σύνθετη ({{ $totalQty }} τεμ.)</span>
        @endif
    </td>
    <td>{{ $adjustment->creator->first_name." ".$adjustment->creator->last_name }}</td>
    <td class="text-right">
        <div class="action-buttons"
             style="display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center;">
            <a href="{{ route('inventory.adjustments.show', $adjustment->id) }}"
               class="action-link view-link" title="Προβολή"
               style="color: #64748b; text-decoration: none;">
                👁️ <span class="action-text">Προβολή</span>
            </a>

            <a href="{{ route('inventory.adjustments.edit', $adjustment->id) }}"
               class="action-link edit-link" title="Επεξεργασία"
               style="color: #4e73df; text-decoration: none;">
                ✏️ <span class="action-text">Επεξεργασία</span>
            </a>

            <form action="{{ route('inventory.adjustments.destroy', $adjustment->id) }}"
                  method="POST" class="delete-form" style="margin: 0;"
                  onsubmit="return confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτή την προσαρμογή;');">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-link delete-btn" title="Διαγραφή"
                        style="background: none; border: none; color: #dc3545; cursor: pointer; padding: 0; font-size: inherit; font-family: inherit;">
                    🗑️ <span class="action-text">Διαγραφή</span>
                </button>
            </form>
        </div>
    </td>
</tr>