<?php

namespace AMABK\LivewireSearchSelect;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Livewire SearchSelect reusable dropdown component.
 *
 * @property string $modelClass      The Eloquent model class to search.
 * @property string $inputClass      CSS classes for input element.
 * @property string $optionClass     CSS classes for option list.
 * @property string $labelField      The field to display as label.
 * @property string $search          The current search value.
 * @property array  $options         List of search results.
 * @property int    $selectedId      The currently selected model ID.
 * @property string $emitEvent       Event name to emit when an option is selected.
 * @property string $placeholder     Placeholder text for input.
 * @property string $selectedLabel   Label of the currently selected item.
 */
class SearchSelect extends Component
{
    public $modelClass;
    public $inputClass = '';
    public $optionClass = '';
    public $labelField = 'name';
    public $search = '';
    public $options = [];
    public $selectedId = null;
    public $emitEvent = null;
    public $placeholder = 'Search...';
    public $selectedLabel = '';

    /**
     * Mount the component with initial state.
     */
    public function mount(
        $modelClass,
        $labelField = 'name',
        $emitEvent = null,
        $placeholder = 'Search...',
        $selectedId = null,
        $inputClass = '',
        $optionClass = ''
    ) {
        // Validate emitEvent (must be a non-empty string)
        if (empty($emitEvent) || !is_string($emitEvent)) {
            Log::error('Emit event must be a string and not empty');
            $this->emitEvent = null;
            return ;
        }

        $this->modelClass = $modelClass;
        $this->labelField = $labelField;
        $this->emitEvent = $emitEvent;
        $this->placeholder = $placeholder;
        $this->selectedId = $selectedId;
        $this->inputClass = $inputClass;
        $this->optionClass = $optionClass;

        // If selectedId is provided, fetch and show its label
        if ($selectedId) {
            $model = $modelClass::find($selectedId);
            if ($model) {
                $this->selectedLabel = $model->{$labelField};
                $this->search = $model->{$labelField};
            }
        }
    }

    /**
     * When the search input updates, fetch matching options.
     */
    public function updatedSearch()
    {
        Log::info('Search updated: ' . $this->search);
        if ($this->search == '') {
            $this->selectedId = null;
            $this->dispatch($this->emitEvent, '');

            return;
        }
        $class = $this->modelClass;
        $field = $this->labelField;

        // Fetch matching records from the model (limit to 10)
        $this->options = $class::where($field, 'like', '%' . $this->search . '%')
            ->orderBy($field)
            ->limit(10)
            ->get();
    }

    /**
     * When an option is selected from the dropdown.
     *
     * @param mixed $id
     * @return void
     */
    public function selectOption($id)
    {
        $class = $this->modelClass;
        $field = $this->labelField;

        $model = $class::find($id);
        if ($model) {
            $this->selectedId = $model->id;
            $this->selectedLabel = $model->{$field};
            $this->search = $model->{$field};
            $this->options = [];

            // Emit event to parent with selected ID
            $this->dispatch($this->emitEvent, $model->id);
        }
    }

    /**
     * Render the Livewire component's Blade view.
     */
    public function render()
    {
        // View path uses package namespace
        return view('livewire-search-select::search-select');
    }
}
