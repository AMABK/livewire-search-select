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
    label-field="name"
    emit-event="userSelected"
    placeholder="Search users..."
/>
```

**Props:**

* `model-class`: The Eloquent model you want to search/select from. **(Required)**
* `label-field`: The attribute to display in the dropdown. *(Defaults to `'name'`)*
* `emit-event`: The event name to emit when an item is selected. **(Required)**
* `placeholder`: Placeholder text for the search input. *(Optional)*
* Other optional props: `selected-id`, `input-class`, `option-class`.

---

### 3. Listening for Selection Events

When a user selects an option, the component emits a Livewire event (named by your `emit-event` prop) **to the parent component**.
You can listen for it and update your parent Livewire component’s data accordingly.

**Example Parent Component:**

```php
class UserForm extends Component
{
    public $selectedUserId;

    protected $listeners = ['userSelected'];

    public function userSelected($userId)
    {
        $this->selectedUserId = $userId;
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
    emit-event="userSelected"
/>
Selected User ID: {{ $selectedUserId }}
```

---

### 4. Customizing Appearance

You can pass custom CSS classes to the input and the options list:

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\Product::class"
    label-field="title"
    emit-event="productChosen"
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
    selected-id="5"
    emit-event="categoryChosen"
/>
```

This will preselect the item with `id = 5` and show its label in the input.

---

### 6. Supported Props

| Prop         | Type   | Required | Default     | Description                    |
| ------------ | ------ | -------- | ----------- | ------------------------------ |
| model-class  | string | yes      | —           | Fully qualified Eloquent class |
| label-field  | string | no       | 'name'      | Attribute used for labels      |
| emit-event   | string | yes      | —           | Livewire event name to emit    |
| placeholder  | string | no       | 'Search...' | Input placeholder              |
| selected-id  | mixed  | no       | null        | Pre-selected model id          |
| input-class  | string | no       | ''          | Custom input CSS classes       |
| option-class | string | no       | ''          | Custom dropdown CSS classes    |

---

### 7. Troubleshooting & Tips

* **No results found:** Ensure your model and label field are correct and records exist.
* **Alpine/Livewire conflicts:** Alpine.js is auto-reinitialized after Livewire updates, but if you experience issues, check your Alpine.js version.
* **Emitted event not caught?**
  Ensure your parent Livewire component is listening for the same event name as the `emit-event` you specify.

---

### 8. Example: User Dropdown in a Form

```blade
<form wire:submit.prevent="save">
    <livewire:livewire-search-select::search-select
        :model-class="\App\Models\User::class"
        label-field="email"
        emit-event="userSelected"
        placeholder="Search for a user by email..."
    />

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

```php
// Parent Component (UserForm.php)
public $userId;
protected $listeners = ['userSelected'];

public function userSelected($userId)
{
    $this->userId = $userId;
}
```

---

### 9. Advanced: Use With Other Models

You can use the component for **any Eloquent model**:

```blade
<livewire:livewire-search-select::search-select
    :model-class="\App\Models\Country::class"
    label-field="country_name"
    emit-event="countrySelected"
    placeholder="Search country..."
/>
```

---

**For advanced use, extend the component in your own package.
For issues, open a GitHub issue or PR!**
