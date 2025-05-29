<?php

namespace AMABK\LivewireSearchSelect;

use Livewire\Component;

class SearchSelect extends Component
{
    public $modelClass;
    public $inputClass = '';
    public $optionClass = '';
    public $labelFields = ['name'];   // Accepts array or string, will always be cast to array
    public $labelSeparator = ' - ';
    public $labelSuffix = '';         // Appended after all fields (e.g., closing bracket)
    public $search = '';
    public $options = [];
    public $selectedId = null;        // int|null (single) OR array (multiple)
    public $emitEvent = null;
    public $placeholder = 'Search...';
    public $selectedLabel = '';       // string (single) OR array (multiple)
    public $multiple = false;

    /**
     * Mount the component with initial state.
     */
    public function mount(
        $modelClass,
        $labelFields = ['name'],
        $emitEvent = null,
        $placeholder = 'Search...',
        $selectedId = null,
        $inputClass = '',
        $optionClass = '',
        $multiple = false,
        $labelSeparator = ' - ',
        $labelSuffix = ''
    ) {
        // Accept comma-separated string or array for labelFields
        if (is_string($labelFields)) {
            $this->labelFields = array_map('trim', explode(',', $labelFields));
        } else {
            $this->labelFields = $labelFields;
        }
        $this->labelSeparator = $labelSeparator;
        $this->labelSuffix = $labelSuffix;

        // Validate emitEvent (must be a non-empty string)
        if (empty($emitEvent) || !is_string($emitEvent)) {
            $this->emitEvent = null;
            return;
        }

        $this->modelClass = $modelClass;
        $this->emitEvent = $emitEvent;
        $this->placeholder = $placeholder;
        $this->inputClass = $inputClass;
        $this->optionClass = $optionClass;
        $this->multiple = $multiple;

        if ($this->multiple) {
            $this->selectedId = is_array($selectedId) ? $selectedId : (empty($selectedId) ? [] : [$selectedId]);
            $this->selectedLabel = [];
            if (!empty($this->selectedId)) {
                foreach ($this->selectedId as $id) {
                    $model = $modelClass::find($id);
                    if ($model) {
                        $this->selectedLabel[$id] = $this->buildLabel($model);
                    }
                }
            }
        } else {
            $this->selectedId = $selectedId;
            if ($selectedId) {
                $model = $modelClass::find($selectedId);
                if ($model) {
                    $label = $this->buildLabel($model);
                    $this->selectedLabel = $label;
                    $this->search = $label;
                }
            }
        }
    }

    /**
     * Helper: Builds the label for an option, from the requested fields.
     */
    public function buildLabel($model)
    {
        // Ensure labelFields is always an array
        if (is_string($this->labelFields)) {
            $this->labelFields = array_map('trim', explode(',', $this->labelFields));
        }
        $parts = [];
        foreach ($this->labelFields as $field) {
            if (isset($model->{$field})) {
                $parts[] = $model->{$field};
            }
        }
        // Add suffix (like closing bracket) if set
        return implode($this->labelSeparator, $parts) . $this->labelSuffix;
    }

    /**
     * When the search input updates, fetch matching options.
     */
    public function updatedSearch()
    {
        if ($this->search == '') {
            if ($this->multiple) {
                // For multi-select, do not clear selections
            } else {
                $this->selectedId = null;
                $this->dispatch($this->emitEvent, '');
            }
            return;
        }
        $class = $this->modelClass;

        // Fetch matching records from the model (limit to 10) 
        $this->options = $class::where(function ($q) {
            foreach ($this->labelFields as $field) {
                $q->orWhere($field, 'ilike', '%' . $this->search . '%');
            }
        })
            ->orderBy($this->labelFields[0])
            ->limit(10)
            ->get();
    }

    /**
     * When an option is selected from the dropdown or a tag is removed.
     *
     * @param mixed $id
     * @return void
     */
    public function selectOption($id)
    {
        $class = $this->modelClass;
        $model = $class::find($id);

        if (!$model) return;

        if ($this->multiple) {
            // Add or remove from selected
            if (in_array($id, $this->selectedId)) {
                // Remove
                $index = array_search($id, $this->selectedId);
                if ($index !== false) {
                    unset($this->selectedId[$index]);
                    unset($this->selectedLabel[$id]);
                    // Reset array keys to avoid index gaps
                    $this->selectedId = array_values($this->selectedId);
                }
            } else {
                // Add
                $this->selectedId[] = $id;
                $this->selectedLabel[$id] = $this->buildLabel($model);
            }
            // Emit the array of IDs to the parent
            $this->dispatch($this->emitEvent, $this->selectedId);
            // Optionally clear input and close options
            $this->options = [];
            $this->search = '';
        } else {
            // Single select
            $this->selectedId = $model->id;
            $this->selectedLabel = $this->buildLabel($model);
            $this->search = $this->selectedLabel;
            $this->options = [];
            $this->dispatch($this->emitEvent, $model->id);
        }
    }

    /**
     * Remove a selected item by ID (for tag removal).
     */
    public function removeSelection($id)
    {
        if ($this->multiple && in_array($id, $this->selectedId)) {
            $index = array_search($id, $this->selectedId);
            if ($index !== false) {
                unset($this->selectedId[$index]);
                unset($this->selectedLabel[$id]);
                $this->selectedId = array_values($this->selectedId);
                $this->dispatch($this->emitEvent, $this->selectedId);
            }
        }
    }

    /**
     * Called by Alpine on backspace, to remove the last selected tag if search is empty.
     */
    public function handleBackspace()
    {
        if ($this->multiple && $this->search === '' && !empty($this->selectedId)) {
            $lastId = array_pop($this->selectedId);
            unset($this->selectedLabel[$lastId]);
            $this->selectedId = array_values($this->selectedId);
            $this->dispatch($this->emitEvent, $this->selectedId);
        }
    }

    public function render()
    {
        return view('livewire-search-select::search-select');
    }
}
