<?php

namespace AMABK\LivewireSearchSelect;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SearchSelect extends Component
{
    /** Core config */
    public string $modelClass;
    public array $labelFields = ['email'];
    public array $searchFields = [];
    public bool $concatFields = false;
    public array $orderBy = ['field' => 'id', 'direction' => 'asc'];
    public int $limit = 10;

    /** UX config */
    public string $inputClass = '';
    public string $optionClass = '';
    public string $placeholder = 'Search...';
    public ?string $emitEvent = null;
    public int $initialLoad = 0; // <— NEW

    /** State */
    public string $search = '';
    /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array */
    public $options = [];
    // int or string
    public $selectedId = null;      // single-select (legacy/back-compat)
    public array $selectedIds = [];       // multi-select
    public string $selectedLabel = '';

    /** Multi-select toggle */
    public bool $multiple = false;

    public string $labelSeparator = ' ';   // used when rendering labels
    public string $searchSeparator = ' ';  // used when CONCAT searching

    public function mount(
        $modelClass,
        $labelFields = ['email'],
        $emitEvent = null,
        $placeholder = 'Search...',
        $selectedId = null,
        $inputClass = 'ring-1 ring-inset ring-gray-300 rounded-md',
        $optionClass = '',
        $searchFields = [],
        $concatFields = false,
        $multiple = false,
        $selectedIds = [],
        $initialLoad = 0,
        $limit = 10,
        $orderBy = ['field' => 'id', 'direction' => 'asc'],
        $labelSeparator = ' ',
        $searchSeparator = ' '
    ) {
        $this->modelClass   = $modelClass;
        $this->labelFields  = is_array($labelFields) ? $labelFields : [$labelFields];
        $this->emitEvent    = is_string($emitEvent) && $emitEvent !== '' ? $emitEvent : null;
        if ($emitEvent === null) {
            Log::debug('SearchSelect: no emitEvent provided; skipping emits.');
        }

        $this->placeholder  = $placeholder;
        $this->inputClass   = $inputClass;
        $this->optionClass  = $optionClass;
        $this->concatFields = (bool) $concatFields;
        $this->multiple     = (bool) $multiple;
        $this->initialLoad  = max(0, (int) $initialLoad);
        $this->limit        = max(1, (int) $limit);

        // orderBy sanity
        $this->orderBy = [
            'field'     => $orderBy['field'] ?? 'id',
            'direction' => strtolower($orderBy['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
        ];

        // default search fields to labels if not provided
        $this->searchFields = !empty($searchFields) ? array_values($searchFields) : $this->labelFields;

        // Preselect values
        if ($this->multiple) {
            $this->selectedIds = array_values(array_unique(array_map('intval', (array) $selectedIds)));
        } else {
            $this->selectedId = $selectedId ? (int) $selectedId : null;
            if ($this->selectedId) {
                $model = ($this->modelClass)::find($this->selectedId);
                if ($model) {
                    $this->selectedLabel = $this->getLabelFromModel($model);
                    $this->search = $this->selectedLabel;
                }
            }
        }

        $this->options = [];
    }

    /** Called by the input focus (see blade) */
    public function onFocus(): void
    {
        if (trim($this->search) === '' && $this->initialLoad > 0) {
            $this->loadOptions(initial: true);
        }
    }

    public function updatedSearch(): void
    {
        $this->loadOptions();
    }

    protected function loadOptions(bool $initial = false): void
    {
        $class = $this->modelClass;
        /** @var Builder $query */
        $query = $class::query();

        // If initial load, skip search filters; otherwise apply search logic
        if (!$initial) {
            $term = trim($this->search);
            if ($term === '') {
                // No term => no options for normal typing flow
                $this->options = [];
                // Only in single-select do we clear selection when empty search
                if (!$this->multiple) {
                    $this->selectedId = null;
                    $this->selectedLabel = '';
                    if ($this->emitEvent) {
                        $this->dispatch($this->emitEvent, '');
                    }
                }
                return;
            }

            // Search: concat or field-wise OR WHERE
            if ($this->concatFields && count($this->searchFields) > 1) {
                $safe = array_map(fn($f) => $this->quoteIdentifier($f), $this->searchFields);
                // CONCAT_WS works on MySQL & PostgreSQL
                $query->whereRaw(
                    "CONCAT_WS(?, " . implode(', ', $safe) . ") LIKE ?",
                    [$this->searchSeparator, "%{$term}%"]
                );
            } else {
                $query->where(function (Builder $q) use ($term) {
                    foreach ($this->searchFields as $i => $field) {
                        $method = $i === 0 ? 'where' : 'orWhere';
                        $q->{$method}($field, 'like', "%{$term}%");
                    }
                });
            }
        }

        // Exclude already selected in multi-select
        if ($this->multiple && !empty($this->selectedIds)) {
            $query->whereNotIn('id', $this->selectedIds);
        }

        $query->orderBy($this->orderBy['field'], $this->orderBy['direction'])
            ->limit($initial ? $this->initialLoad : $this->limit);

        $this->options = $query->get();
    }

    public function selectOption($id): void
    {
        $class = $this->modelClass;
        $model = $class::find($id);

        if (!$model) {
            return;
        }

        if ($this->multiple) {
            // Toggle in multi-select
            if (!in_array($model->id, $this->selectedIds, true)) {
                $this->selectedIds[] = $model->id;
            }
            // Refresh options and emit
            $this->loadOptions();
            if ($this->emitEvent) {
                $this->dispatch($this->emitEvent, $this->selectedIds);
            }
        } else {
            $this->selectedId = $model->id;
            $this->selectedLabel = $this->getLabelFromModel($model);
            $this->search = $this->selectedLabel;
            $this->options = [];
            if ($this->emitEvent) {
                $this->dispatch($this->emitEvent, $model->id);
            }
        }
    }

    public function removeSelected($id): void
    {
        if (!$this->multiple) {
            return;
        }
        $this->selectedIds = array_values(array_filter($this->selectedIds, fn($x) => (string)$x !== (string)$id));
        $this->loadOptions(); // repopulate dropdown list
        if ($this->emitEvent) {
            $this->dispatch($this->emitEvent, $this->selectedIds);
        }
    }

    protected function getLabelFromModel($model): string
    {
        $fields = $this->labelFields;
        if (!is_array($fields)) {
            return (string) ($model->{$fields} ?? '');
        }
        $parts = array_map(fn($f) => trim((string)($model->{$f} ?? '')), $fields);
        $parts = array_filter($parts, fn($v) => $v !== '');
        return implode($this->labelSeparator, $parts);
    }

    /** Very light identifier quoting (best-effort) */
    protected function quoteIdentifier(string $identifier): string
    {
        // Prevent injections via field names (allow a-z0-9_ and dot)
        if (!preg_match('/^[A-Za-z0-9_.]+$/', $identifier)) {
            abort(400, 'Invalid field name.');
        }
        return $identifier;
    }

    public function render()
    {
        return view('livewire-search-select::search-select');
    }
}
