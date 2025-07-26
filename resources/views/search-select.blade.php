<div x-data="searchSelectDropdown()" x-init="init()" x-on:click.outside="open = false" class="relative w-full">
    <!-- Search Input -->
    <input type="text" wire:model.live="search" @focus="open = true" :placeholder="`{{ $placeholder }}`"
        class="w-full {{ $inputClass }} px-3 py-2" />

    <!-- Dropdown Options -->
    <ul x-show="open" x-transition x-cloak
        class="absolute bottom-full z-[999] bg-white border w-full mb-1 rounded shadow overflow-y-auto {{ $optionClass }}">
        @forelse($options as $option)
            <li wire:key="option-{{ $option->id }}" wire:click="selectOption('{{ $option->id }}')"
                @click="open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                {{ is_array($this->labelFields)
                    ? implode(' ', array_map(fn($field) => $option->{$field} ?? '', $this->labelFields))
                    : $option->{$this->labelFields} ?? '' }}
            </li>
        @empty
            <li class="px-4 py-2 text-gray-500">No results found.</li>
        @endforelse
    </ul>
</div>

<!-- Alpine Dropdown Script -->
<script>
    function searchSelectDropdown() {
        return {
            open: false,
            init() {
                Livewire.hook('message.processed', () => {
                    if (window.Alpine) {
                        Alpine.initTree(document.body);
                    }
                });
            }
        };
    }
</script>
