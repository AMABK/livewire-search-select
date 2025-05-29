<div x-data="searchSelectDropdown()" x-init="init()" x-on:click.outside="open = false" class="relative w-full">
    <div class="flex flex-wrap items-center border rounded px-2 py-1 {{ $inputClass }} bg-white focus-within:ring-1 cursor-text"
        @click="$refs.input.focus()">
        @if ($multiple)
            @foreach ($selectedLabel as $id => $label)
                <span class="flex items-center bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1 mb-1">
                    {{ $label }}
                    <button type="button" wire:click="removeSelection('{{ $id }}')"
                        class="ml-1 text-red-600 hover:text-red-900 focus:outline-none" tabindex="-1" title="Remove"
                        @mousedown.prevent>&times;</button>
                </span>
            @endforeach
        @endif

        <input x-ref="input" type="text" wire:model.live="search" @focus="open = true"
            :placeholder="(Array.isArray(@js($selectedLabel)) && Object.keys(@js($selectedLabel)).length) ? '' :
            '{{ $placeholder }}'"
            class="flex-1 min-w-[100px] border-0 focus:ring-0 outline-none px-1 py-1 bg-transparent"
            @keydown.enter.prevent @keydown.backspace="
                @this.call('handleBackspace')
            " />
    </div>

    <ul x-show="open" x-transition x-cloak
        class="absolute bottom-full z-[999] bg-white border w-full mb-1 rounded shadow overflow-y-auto {{ $optionClass }}">
        @forelse($options as $option)
            <li wire:key="option-{{ $option->id }}" wire:click="selectOption('{{ $option->id }}')"
                @click="open = {{ $multiple ? 'true' : 'false' }}" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                {{ $this->buildLabel($option) }}
            </li>
        @empty
            <li class="px-4 py-2 text-gray-500">No results found.</li>
        @endforelse
    </ul>

</div>

<script>
    function searchSelectDropdown() {
        return {
            open: false,
            init() {
                Livewire.hook('message.processed', (message, component) => {
                    if (window.Alpine) {
                        Alpine.initTree(document.body);
                    }
                });
            }
        }
    }
</script>
