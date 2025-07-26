<?php

namespace AMABK\LivewireSearchSelect;

use Livewire\Component;

class SearchSelect extends Component
{
    public $modelClass;
    public $inputClass = '';
    public $optionClass = '';
    public $labelFields = ['email'];
    public $searchFields = [];
    public $concatFields = false;
    public $search = '';
    public $options = [];
    public $selectedId = null;
    public $emitEvent = null;
    public $placeholder = 'Search...';
    public $selectedLabel = '';
    public $orderBy = [
        'field' => 'id',
        'direction' => 'asc',
    ];

    public function mount(
        $modelClass,
        $labelFields = ['email'],
        $emitEvent = null,
        $placeholder = 'Search...',
        $selectedId = null,
        $inputClass = '',
        $optionClass = '',
        $searchFields = [],
        $concatFields = false
    ) {
        if (empty($emitEvent) || !is_string($emitEvent)) {
            Log::error('Emit event must be a string and not empty');
            $this->emitEvent = null;
            return;
        }

        $this->modelClass = $modelClass;
        $this->labelFields = is_array($labelFields) ? $labelFields : [$labelFields];
        $this->emitEvent = $emitEvent;
        $this->placeholder = $placeholder;
        $this->selectedId = $selectedId;
        $this->inputClass = $inputClass;
        $this->optionClass = $optionClass;
        $this->searchFields = !empty($searchFields) ? $searchFields : $this->labelFields;
        $this->concatFields = $concatFields;

        if ($selectedId) {
            $model = $modelClass::find($selectedId);
            if ($model) {
                $this->selectedLabel = $this->getLabelFromModel($model);
                $this->search = $this->selectedLabel;
            }
        }

        $this->loadOptions();
    }

    public function updatedSearch()
    {
        $this->loadOptions();
    }

    protected function loadOptions()
    {
        if (trim($this->search) === '') {
            $this->options = [];
            $this->selectedId = null;
            $this->dispatch($this->emitEvent, '');
            return;
        }

        $class = $this->modelClass;
        $query = $class::query();

        if ($this->concatFields && count($this->searchFields) > 1) {
            $concatExpr = "CONCAT(" . implode(", ' ', ", $this->searchFields) . ")";
            $query->whereRaw("$concatExpr LIKE ?", ["%" . $this->search . "%"]);
        } else {
            foreach ($this->searchFields as $i => $field) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $query->{$method}($field, 'like', '%' . $this->search . '%');
            }
        }

        $query->orderBy($this->orderBy['field'], $this->orderBy['direction'])
              ->limit(10);

        $this->options = $query->get();
    }

    public function selectOption($id)
    {
        $class = $this->modelClass;
        $model = $class::find($id);

        if ($model) {
            $this->selectedId = $model->id;
            $this->selectedLabel = $this->getLabelFromModel($model);
            $this->search = $this->selectedLabel;
            $this->options = [];

            $this->dispatch($this->emitEvent, $model->id);
        }
    }

    protected function getLabelFromModel($model)
    {
        $fields = $this->labelFields;

        // Ensure $fields is an array
        if (!is_array($fields)) {
            return $model->{$fields};
        }

        return implode(' ', array_map(fn($field) => $model->{$field} ?? '', $fields));
    }

    public function render()
    {
        return view('livewire-search-select::search-select');

    }
}

