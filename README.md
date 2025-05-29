## Usage

### 1. Installation

Install via Composer:

```bash
composer require amabk/livewire-search-select
```

---

### 2. Basic Usage

Add the component to any Blade view:

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\User::class"
    label-fields="name,email"
    label-separator=" ["
    label-suffix="]"
    :multiple="true"
    emit-event="usersSelected"
    placeholder="Search users..."
/>
```

**Props:**

* `model-class`: The Eloquent model you want to search/select from. **(Required)**
* `label-fields`: One or more model attributes to display as the label. Accepts comma-separated string or array. **(Required)**
* `label-separator`: The separator used **between** label fields. *(Default: **`' - '`**)*
* `label-suffix`: Appended **after** the final label field (e.g., closing bracket). *(Default: **`''`**)*
* `emit-event`: The event name to emit when an item is selected. **(Required)**
* `placeholder`: Placeholder text for the search input. *(Optional)*
* `multiple`: Enable multi-select. Pass `true` for multiple selection. *(Optional)*
* Other optional props: `selected-id`, `input-class`, `option-class`.

---

### 3. Listening for Selection Events

When a user selects an option, the component emits a Livewire event (named by your `emit-event` prop) **to the parent component**.
You can listen for it and update your parent Livewire component’s data accordingly.

**Example Parent Component:**

```php
class UserForm extends Component
{
    public $selectedUserIds = [];

    protected $listeners = ['usersSelected'];

    public function usersSelected($userIds)
    {
        $this->selectedUserIds = $userIds;
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
```

**In your Blade view:**

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\User::class"
    label-fields="name,email"
    label-separator=" ["
    label-suffix="]"
    :multiple="true"
    emit-event="usersSelected"
/>
Selected User IDs: {{ implode(', ', $selectedUserIds) }}
```

---

### 4. Customizing Appearance

You can pass custom CSS classes to the input and the options list:

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\Product::class"
    label-fields="title,sku"
    label-separator=" | SKU: "
    :multiple="true"
    emit-event="productsChosen"
    input-class="border-blue-500"
    option-class="max-h-48"
    placeholder="Find a product..."
/>
```

---

### 5. Setting a Default Selected Option

To set a default selection:

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\Category::class"
    label-fields="name"
    :selected-id="[5, 9]"
    :multiple="true"
    emit-event="categoriesChosen"
/>
```

This will preselect the items with `id = 5` and `id = 9` and show their labels in the input.

---

### 6. Supported Props

| Prop            | Type         | Required | Default     | Description                         |
| --------------- | ------------ | -------- | ----------- | ----------------------------------- |
| model-class     | string       | yes      | —           | Fully qualified Eloquent class      |
| label-fields    | string/array | yes      | 'name'      | Attribute(s) used for labels        |
| label-separator | string       | no       | ' - '       | Used **between** label fields       |
| label-suffix    | string       | no       | ''          | Appended **after** all label fields |
| emit-event      | string       | yes      | —           | Livewire event name to emit         |
| placeholder     | string       | no       | 'Search...' | Input placeholder                   |
| selected-id     | mixed        | no       | null        | Pre-selected model id(s)            |
| multiple        | boolean      | no       | false       | Enable multi-select                 |
| input-class     | string       | no       | ''          | Custom input CSS classes            |
| option-class    | string       | no       | ''          | Custom dropdown CSS classes         |

---

### 7. Troubleshooting & Tips

* **No results found:** Ensure your model and label fields are correct and records exist.
* **Alpine/Livewire conflicts:** Alpine.js is auto-reinitialized after Livewire updates, but if you experience issues, check your Alpine.js version.
* **Emitted event not caught?**
  Ensure your parent Livewire component is listening for the same event name as the `emit-event` you specify.

---

### 8. Example: User Dropdown in a Form

```blade
<form wire:submit.prevent="save">
    <livewire:livewire-search-select::search-select
        :model-class="\App\Models\User::class"
        label-fields="name,email"
        label-separator=", "
        label-suffix=" (active)"
        :multiple="true"
        emit-event="userSelected"
        placeholder="Search for a user by name or email..."
    />

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

```php
// Parent Component (UserForm.php)
public $userIds = [];
protected $listeners = ['userSelected'];

public function userSelected($userIds)
{
    $this->userIds = $userIds;
}
```

---

### 9. Advanced: Use With Any Model & Any Label Format

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\Country::class"
    label-fields="country_name,country_code"
    label-separator=" - "
    label-suffix=""
    :multiple="true"
    emit-event="countriesSelected"
    placeholder="Search country..."
/>
```

---

**For advanced use, extend the component in your own package.**
**For issues, open a GitHub issue or PR!**
