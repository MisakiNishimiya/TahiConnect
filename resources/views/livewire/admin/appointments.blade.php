<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('components.layouts.app')] class extends Component {
    public function mount(): mixed { return redirect()->route('shopowner.appointments'); }
}; ?>
<div></div>
