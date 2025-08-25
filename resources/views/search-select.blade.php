<div x-data="searchSelectField(@js($multiple))" x-init="init()" x-on:click.outside="open = false" class="lw-ss relative w-full">
    {{-- Single bordered container (looks like an input) --}}
    <div class="lw-ss__box w-full min-h-9.5 rounded px-2 py-1 flex flex-wrap items-center bg-white focus-within:ring-1 {{ $inputClass }}"
        @click="$refs.input.focus(); open = true; $wire.onFocus()" role="combobox" aria-expanded="true"
        aria-haspopup="listbox">
        {{-- Chips (only for multi) --}}
        @if ($multiple && !empty($selectedIds))
            @foreach ($selectedIds as $sid)
                @php
                    $model = $modelClass::find($sid);
                    $label = $model
                        ? (is_array($labelFields)
                            ? implode(
                                $labelSeparator,
                                array_filter(array_map(fn($f) => $model->{$f} ?? '', $labelFields)),
                            )
                            : $model->{$labelFields} ?? '')
                        : '';
                @endphp

                <span class="lw-ss__chip inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-sm">
                    <span class="truncate max-w-[16ch]" title="{{ $label }}">{{ $label }}</span>
                    <button type="button" class="leading-none text-gray-500 hover:text-red-600"
                        wire:click="removeSelected(@js($sid))"
                        aria-label="Remove {{ $label }}">&times;</button>
                </span>
            @endforeach
        @endif

        {{-- The only actual input (made visually seamless) --}}
        <input x-ref="input" type="text"
            class="lw-ss__input flex-1 min-w-[8ch] appearance-none bg-transparent border-none outline-none focus:outline-none focus:ring-0 py-1"
            wire:model.live="search" :placeholder="@js(!$multiple || empty($selectedIds) ? $placeholder : '')" autocomplete="off"
            @focus="open = true; $wire.onFocus()" @keydown.backspace="maybeRemoveLastChip($event)"
            @keydown.escape.stop.prevent="open = false" />
    </div>

    {{-- Dropdown (scoped classes/z-index) --}}
    <ul x-show="open" x-transition x-cloak
        class="lw-ss__menu absolute z-[999] bg-white border w-full mt-1 rounded shadow max-h-64 overflow-y-auto {{ $optionClass }}"
        role="listbox">
        @forelse($options as $option)
            <li wire:key="option-{{ $option->id }}" wire:click="selectOption(@js($option->id))"
                @click="open = @js($multiple ? true : false); $nextTick(() => $refs.input.focus())"
                class="lw-ss__option px-4 py-2 hover:bg-gray-100 cursor-pointer" role="option" aria-selected="false">
                {{ is_array($labelFields)
                    ? implode($labelSeparator, array_filter(array_map(fn($field) => $option->{$field} ?? '', $labelFields)))
                    : $option->{$labelFields} ?? '' }}

            </li>
        @empty
            <li class="px-4 py-2 text-gray-500">No results found.</li>
        @endforelse
    </ul>
</div>

{{-- Alpine (scoped; no global side-effects) --}}
<script>
    function searchSelectField(isMultiple = false) {
        return {
            open: false,
            isMultiple,
            root: null,
            init() {
                this.root = this.$root;

                // Re-init Alpine only for THIS Livewire component
                Livewire.hook('message.processed', (message, component) => {
                    try {
                        if (component.id === this.$wire.__instance.id && window.Alpine) {
                            Alpine.initTree(this.root);
                        }
                    } catch (_) {}
                });
            },
            async maybeRemoveLastChip(e) {
                if (!this.isMultiple) return;

                // If there's text, allow normal backspace behavior
                const term = await this.$wire.get('search');
                if (term && term.length) return;

                // If empty, remove last chip
                const selectedIds = await this.$wire.get('selectedIds');
                if (Array.isArray(selectedIds) && selectedIds.length) {
                    e.preventDefault();
                    const lastId = selectedIds[selectedIds.length - 1];
                    this.$wire.removeSelected(lastId);
                    this.$nextTick(() => this.$refs.input.focus());
                }
            },
        };
    }
</script>
