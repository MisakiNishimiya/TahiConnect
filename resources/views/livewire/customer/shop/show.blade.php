<?php
// This route is no longer active in the single-shop system.
// Redirect to the catalog page.
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function mount(): mixed
    {
        return redirect()->route('customer.catalog');
    }
}; ?>
<div></div>
