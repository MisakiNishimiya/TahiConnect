<?php
// This view is superseded by the Super Admin dashboard.
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('components.layouts.app')] class extends Component {
    public function mount(): mixed { return redirect()->route('superadmin.dashboard'); }
}; ?>
<div></div>
