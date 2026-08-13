<tr>
    <td class="td-id">#{{ $brand->id }}</td>
    <td class="td-name">{{ $brand->name }}</td>
    <td>
        @forelse($brand->categories as $category)
            <a href="{{ route('inventory.categories.show', ['category' => $category->id]) }}"
               class="badge-count"
               style="margin-right: var(--space-xs); margin-bottom: var(--space-xs);">
                {{ $category->name }}
            </a>
        @empty
            <span class="td-description">—</span>
        @endforelse
    </td>

    <td class="text-right">
        <div class="action-buttons-group">
            {{-- Κουμπί Προβολής (Show) --}}
            <a href="{{ route('inventory.brands.show', $brand->id) }}"
               class="btn-action btn-show" title="Προβολή">
                <i class="fas fa-eye"></i>
            </a>

            {{-- Κουμπί Επεξεργασίας (Edit) --}}
            <a href="{{ route('inventory.brands.edit', $brand->id) }}"
               class="btn-action btn-edit" title="Επεξεργασία">
                <i class="fas fa-pencil-alt"></i>
            </a>

            {{-- Κουμπί Διαγραφής (Delete) --}}
            <form action="{{ route('inventory.brands.destroy', $brand->id) }}" method="POST"
                  class="inline-form"
                  onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το brand;');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete" title="Διαγραφή">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
        </div>
    </td>
</tr>