<?php
/**
 * Pre-Made Garment Order Flow
 *
 * Steps:
 *   1  — Product confirmation (selected garment summary)
 *   2  — Enter body measurements for this order
 *   3  — Validation result (compatible / incompatible)
 *   4a — (valid)   AI visualization placeholder → order confirmation → payment placeholder
 *   4b — (invalid) Actions: select another product / request custom / edit measurements
 *
 * Measurements entered here are order-specific and are NOT saved as a
 * permanent reusable customer profile.
 *
 * Tolerance-Based Measurement Validation Algorithm:
 *   MV = |CM - GM|
 *   Valid   when MV <= T
 *   Invalid when MV >  T
 * Overall: Valid only when ALL required measurements pass.
 */

use App\Models\PreMadeProduct;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Measurement;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

    // ── Flow state ────────────────────────────────────────────────────────────
    public int    $step          = 1;   // 1=confirm, 2=measure, 3=result, 4=post-valid
    public string $flowResult    = '';  // '' | 'valid' | 'invalid'
    public bool   $showTryOn     = false;
    public bool   $orderPlaced   = false;
    public string $orderTracking = '';

    // ── Product ───────────────────────────────────────────────────────────────
    public ?int $productId   = null;
    public string $selectedSize = '';

    // ── Measurement inputs (order-specific, not profile) ──────────────────────
    public string $chest         = '';
    public string $waist         = '';
    public string $hip           = '';
    public string $shoulder      = '';
    public string $sleeve_length = '';
    public string $inseam        = '';
    public string $neck          = '';
    public string $height        = '';

    // ── Validation results (computed, not persisted) ──────────────────────────
    public array $validationBreakdown = [];   // per-field results
    public bool  $overallValid        = false;

    // ── Quantity ──────────────────────────────────────────────────────────────
    public int $quantity = 1;

    // ── Double-submit guard ───────────────────────────────────────────────────
    public bool $orderSubmitting = false;

    // ── Boot ──────────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->productId = (int) request()->query('product', 0);
        if (!$this->productId || !PreMadeProduct::where('id', $this->productId)->where('is_active', true)->exists()) {
            $this->redirect(route('customer.catalog'), navigate: true);
            return;
        }
        // Pre-select first available size
        $product = PreMadeProduct::find($this->productId);
        if ($product && count($product->available_sizes)) {
            $this->selectedSize = $product->available_sizes[0];
        }
    }

    // ── Step navigation ───────────────────────────────────────────────────────
    public function goToStep(int $n): void
    {
        // Reset try-on state when navigating back so step 4 doesn't auto-show it
        if ($n < 4) {
            $this->showTryOn = false;
        }
        $this->step = $n;
    }

    public function confirmProduct(): void
    {
        $this->validate(['selectedSize' => 'required|string']);
        $this->step = 2;
    }

    // ── Run the Tolerance-Based Measurement Validation Algorithm ──────────────
    public function validateMeasurements(): void
    {
        $rules = [
            'chest'         => 'nullable|numeric|min:1|max:200',
            'waist'         => 'nullable|numeric|min:1|max:200',
            'hip'           => 'nullable|numeric|min:1|max:200',
            'shoulder'      => 'nullable|numeric|min:1|max:100',
            'sleeve_length' => 'nullable|numeric|min:1|max:100',
            'inseam'        => 'nullable|numeric|min:1|max:150',
            'neck'          => 'nullable|numeric|min:1|max:60',
            'height'        => 'nullable|numeric|min:1|max:250',
        ];

        $product = PreMadeProduct::find($this->productId);

        // If the product has standards, required fields must have real values
        if ($product && $product->hasMeasurementStandards()) {
            foreach (array_keys($product->measurement_standards) as $field) {
                $rules[$field] = 'required|numeric|min:1|max:300';
            }
        }

        $this->validate($rules);

        // If no standards defined, skip validation and treat as valid
        if (!$product || !$product->hasMeasurementStandards()) {
            $this->overallValid        = true;
            $this->validationBreakdown = [];
            $this->flowResult          = 'valid';
            $this->step                = 3;
            return;
        }

        $standards   = $product->measurement_standards;   // [field => GM]
        $tolerances  = $product->measurement_tolerances;  // [field => T]
        $breakdown   = [];
        $allValid    = true;

        $fieldLabels = [
            'chest'         => 'Chest',
            'waist'         => 'Waist',
            'hip'           => 'Hip',
            'shoulder'      => 'Shoulder',
            'sleeve_length' => 'Sleeve Length',
            'inseam'        => 'Inseam',
            'neck'          => 'Neck',
        ];

        foreach ($standards as $field => $gm) {
            $cm        = (float) ($this->{$field} ?? 0);
            $t         = (float) ($tolerances[$field] ?? 0);
            $mv        = abs($cm - $gm);
            $isValid   = ($mv <= $t);

            if (!$isValid) $allValid = false;

            $breakdown[] = [
                'field'       => $field,
                'label'       => $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field)),
                'cm'          => $cm,   // Customer Measurement
                'gm'          => $gm,   // Garment Measurement standard
                'tolerance'   => $t,    // Tolerance value
                'variance'    => round($mv, 2),
                'is_valid'    => $isValid,
            ];
        }

        $this->validationBreakdown = $breakdown;
        $this->overallValid        = $allValid;
        $this->flowResult          = $allValid ? 'valid' : 'invalid';
        $this->step                = 3;
    }

    // ── Post-valid actions ────────────────────────────────────────────────────

    public function proceedToVisualization(): void
    {
        $this->showTryOn = true;
        $this->step = 4;
    }

    public function skipVisualization(): void
    {
        $this->step = 4;
    }

    public function placeOrder(): void
    {
        if ($this->orderSubmitting || $this->orderPlaced) {
            return;
        }
        $this->orderSubmitting = true;
        $shop    = Shop::instance();
        $product = PreMadeProduct::findOrFail($this->productId);

        $order = Order::create([
            'user_id'              => auth()->id(),
            'shop_id'              => $shop->id,
            'tracking_number'      => 'TC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'order_type'           => 'pre_made',
            'pre_made_product_id'  => $this->productId,
            'product_size'         => $this->selectedSize,
            'quantity'             => $this->quantity,
            'total_amount'         => $product->price * $this->quantity,
            'status'               => 'pending',
            'estimated_completion' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // Save measurements as order-specific (not profile)
        Measurement::create([
            'user_id'           => auth()->id(),
            'order_id'          => $order->id,
            'chest'             => $this->chest        ?: null,
            'waist'             => $this->waist        ?: null,
            'hip'               => $this->hip          ?: null,
            'shoulder'          => $this->shoulder     ?: null,
            'sleeve_length'     => $this->sleeve_length ?: null,
            'inseam'            => $this->inseam       ?: null,
            'neck'              => $this->neck         ?: null,
            'height'            => $this->height       ?: null,
            'validation_status' => 'validated',
            'notes'             => 'Order-specific measurement from pre-made flow.',
        ]);

        $this->orderTracking = $order->tracking_number;
        $this->orderPlaced   = true;
        $this->orderSubmitting = false;
    }

    // ── Invalid result actions ────────────────────────────────────────────────

    /** Return to catalog to pick a different product */
    public function selectAnotherProduct(): void
    {
        $this->redirect(route('customer.catalog'), navigate: true);
    }

    /** Convert to custom-made order — keep entered measurements, go to orders page */
    public function convertToCustomOrder(): void
    {
        // Store measurements in session so customer.orders can pre-populate notes
        session([
            'premade_to_custom_measurements' => [
                'chest'         => $this->chest,
                'waist'         => $this->waist,
                'hip'           => $this->hip,
                'shoulder'      => $this->shoulder,
                'sleeve_length' => $this->sleeve_length,
                'inseam'        => $this->inseam,
                'neck'          => $this->neck,
                'height'        => $this->height,
            ],
        ]);
        $this->redirect(route('customer.orders', ['from' => 'premade_convert']), navigate: true);
    }

    /** Allow editing measurements (for entry mistakes only) */
    public function editMeasurements(): void
    {
        $this->flowResult = '';
        $this->validationBreakdown = [];
        $this->overallValid = false;
        $this->step = 2;
    }

    public function incrementQuantity(): void { $this->quantity++; }
    public function decrementQuantity(): void  { if ($this->quantity > 1) $this->quantity--; }

    // ── Data for the view ─────────────────────────────────────────────────────
    public function with(): array
    {
        return [
            'product' => PreMadeProduct::find($this->productId),
        ];
    }
}; ?>

<div class="space-y-6 max-w-3xl mx-auto">

    {{-- ── BREADCRUMB ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('customer.catalog') }}" wire:navigate class="hover:text-primary-600 transition-colors">Our Catalog</a>
        <svg class="w-4 h-4 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-zinc-700 font-medium">Order Pre-Made Garment</span>
    </div>

    {{-- ── STEP INDICATOR ──────────────────────────────────────────────────── --}}
    @php
        $steps = [
            1 => 'Confirm Product',
            2 => 'Enter Measurements',
            3 => 'Compatibility Check',
            4 => 'Confirm & Pay',
        ];
    @endphp
    <div class="tc-card !p-4" style="box-shadow: none;">
        <div class="flex items-center">
            @foreach($steps as $n => $label)
                {{-- Step circle + label --}}
                <div class="flex flex-col items-center shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $step > $n ? 'bg-emerald-500 text-white' : ($step === $n ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-zinc-100 text-zinc-400') }}">
                        @if($step > $n)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <span class="hidden sm:block mt-1.5 text-[11px] font-medium text-center leading-tight max-w-[72px]
                        {{ $step === $n ? 'text-primary-600' : ($step > $n ? 'text-emerald-600' : 'text-zinc-400') }}">
                        {{ $label }}
                    </span>
                </div>
                {{-- Connector line between steps --}}
                @if(!$loop->last)
                    <div class="flex-1 h-0.5 mx-2 self-start mt-4
                        {{ $step > $n ? 'bg-emerald-300' : 'bg-zinc-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    @if($product)

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- STEP 1 — Confirm Product                                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($step === 1)
    <div class="tc-card space-y-5">
        <div>
            <h1 class="text-2xl font-bold text-primary-600" style="font-family:'Poppins'">Confirm Your Selection</h1>
            <p class="text-zinc-500 mt-1 text-sm">Review the garment details and choose your size before entering measurements.</p>
        </div>

        <div class="flex gap-5 flex-col sm:flex-row">
            {{-- Product image --}}
            <div class="w-full sm:w-40 h-44 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 border border-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                @if($product->image_url)
                    <img src="{{ Storage::url($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-2xl">
                @else
                    <svg class="w-12 h-12 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 space-y-3">
                <div>
                    <h2 class="text-xl font-bold text-zinc-900">{{ $product->name }}</h2>
                    <p class="text-2xl font-bold text-primary-600 mt-1">₱{{ number_format($product->price, 2) }}</p>
                    @if($product->description)
                        <p class="text-sm text-zinc-500 mt-2">{{ $product->description }}</p>
                    @endif
                </div>

                {{-- Size selector --}}
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">Select Size <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->available_sizes as $size)
                            <button type="button" wire:click="$set('selectedSize', '{{ $size }}')"
                                class="px-4 py-2 text-sm font-semibold rounded-xl border-2 transition-all
                                {{ $selectedSize === $size ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-zinc-200 text-zinc-600 hover:border-primary-300 hover:text-primary-600' }}">
                                {{ $size }}
                            </button>
                        @endforeach
                    </div>
                    @error('selectedSize') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Quantity --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-zinc-700">Quantity:</span>
                    <button type="button" wire:click="decrementQuantity" class="w-8 h-8 flex items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-100 transition-colors font-bold">-</button>
                    <span class="w-8 text-center font-bold text-zinc-900">{{ $quantity }}</span>
                    <button type="button" wire:click="incrementQuantity" class="w-8 h-8 flex items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-100 transition-colors font-bold">+</button>
                </div>
            </div>
        </div>

        @if($product->hasMeasurementStandards())
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-blue-700">This garment requires a <strong>compatibility check</strong>. You will enter your body measurements on the next step so we can verify whether this garment fits your body.</p>
        </div>
        @endif

        <div class="flex gap-3 pt-2">
            <a href="{{ route('customer.catalog') }}" wire:navigate class="flex-1 text-center px-5 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                ← Back to Catalog
            </a>
            <button wire:click="confirmProduct" class="flex-1 px-5 py-3 text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm">
                Continue → Enter Measurements
            </button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- STEP 2 — Enter Measurements                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($step === 2)
    <div class="tc-card space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-primary-600" style="font-family:'Poppins'">Enter Your Measurements</h1>
            <p class="text-zinc-500 mt-1 text-sm">These measurements are for this order only and will not be saved as a profile. Use a flexible tape measure in centimetres.</p>
        </div>

        {{-- Which fields are required --}}
        @if($product->hasMeasurementStandards())
        @php $requiredFields = array_keys($product->measurement_standards ?? []); @endphp
        <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-sm text-amber-700">Required for compatibility check: <strong>{{ collect($requiredFields)->map(fn($f) => ucwords(str_replace('_',' ',$f)))->implode(', ') }}</strong>. Other fields are optional.</p>
        </div>
        @endif

        <form wire:submit="validateMeasurements" class="space-y-6">
            {{-- Upper Body --}}
            <div>
                <h3 class="text-base font-semibold text-zinc-800 border-b border-zinc-100 pb-2 mb-4">Upper Body (cm)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach([
                        ['chest',         'Chest',        '96.5'],
                        ['neck',          'Neck',         '38.1'],
                        ['shoulder',      'Shoulder',     '44.5'],
                        ['sleeve_length', 'Sleeve',       '61.0'],
                    ] as [$field, $label, $ph])
                    @php $isRequired = $product->hasMeasurementStandards() && in_array($field, array_keys($product->measurement_standards ?? [])); @endphp
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-zinc-700">{{ $label }} @if($isRequired)<span class="text-red-500">*</span>@endif</label>
                        <input type="number" step="0.1" wire:model="{{ $field }}" placeholder="{{ $ph }}"
                            class="w-full px-3 py-2.5 border {{ $isRequired ? 'border-primary-300' : 'border-zinc-200' }} rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all bg-white" />
                        @error($field) <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Lower Body --}}
            <div>
                <h3 class="text-base font-semibold text-zinc-800 border-b border-zinc-100 pb-2 mb-4">Lower Body (cm)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach([
                        ['waist',  'Waist',  '81.3'],
                        ['hip',    'Hip',    '96.5'],
                        ['inseam', 'Inseam', '78.7'],
                        ['height', 'Height', '170.2'],
                    ] as [$field, $label, $ph])
                    @php $isRequired = $product->hasMeasurementStandards() && in_array($field, array_keys($product->measurement_standards ?? [])); @endphp
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-zinc-700">{{ $label }} @if($isRequired)<span class="text-red-500">*</span>@endif</label>
                        <input type="number" step="0.1" wire:model="{{ $field }}" placeholder="{{ $ph }}"
                            class="w-full px-3 py-2.5 border {{ $isRequired ? 'border-primary-300' : 'border-zinc-200' }} rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all bg-white" />
                        @error($field) <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Measuring tips --}}
            <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-100 text-sm text-zinc-600 space-y-1">
                <p class="font-semibold text-zinc-700 mb-2">📏 Measuring Tips</p>
                <ul class="space-y-1 text-xs list-disc list-inside">
                    <li>Use a flexible cloth tape measure</li>
                    <li>Wear fitted clothing for accurate results</li>
                    <li>Measure while standing straight</li>
                    <li>Have someone help you for the best accuracy</li>
                </ul>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="goToStep(1)" class="flex-1 px-5 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                    ← Back
                </button>
                <button type="submit" class="flex-1 px-5 py-3 text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm">
                    <span wire:loading.remove wire:target="validateMeasurements">Check Compatibility →</span>
                    <span wire:loading wire:target="validateMeasurements" class="flex items-center justify-center gap-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Checking...
                    </span>
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- STEP 3 — Validation Result                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($step === 3)
    <div class="space-y-5">

        {{-- Overall result banner --}}
        @if($overallValid)
        <div class="tc-card border-2 border-emerald-300 bg-emerald-50" style="box-shadow:none;">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-emerald-800" style="font-family:'Poppins'">Compatible</h2>
                    <p class="text-sm text-emerald-700 mt-0.5">The selected pre-made garment is compatible with the entered body measurements.</p>
                </div>
            </div>
        </div>
        @else
        <div class="tc-card border-2 border-red-300 bg-red-50" style="box-shadow:none;">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-red-500 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-red-800" style="font-family:'Poppins'">Not Compatible</h2>
                    <p class="text-sm text-red-700 mt-0.5">The selected pre-made garment is not compatible because the required adjustments exceed the acceptable tolerance values.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Per-measurement breakdown --}}
        @if(count($validationBreakdown))
        <div class="tc-card space-y-4">
            <h3 class="text-base font-bold text-zinc-800" style="font-family:'Poppins'">Measurement Breakdown</h3>
            <div class="text-xs text-zinc-500 flex gap-4 flex-wrap">
                <span>CM = Your measurement &nbsp;·&nbsp; GM = Garment standard &nbsp;·&nbsp; T = Tolerance &nbsp;·&nbsp; MV = |CM − GM|</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left py-2 px-3 font-semibold text-zinc-500 text-xs">Measurement</th>
                            <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs">Your CM (cm)</th>
                            <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs">Garment GM (cm)</th>
                            <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs">Tolerance T (cm)</th>
                            <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs">Variance MV</th>
                            <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-50">
                        @foreach($validationBreakdown as $row)
                        <tr class="hover:bg-zinc-50 transition-colors {{ !$row['is_valid'] ? 'bg-red-50/50' : '' }}">
                            <td class="py-2.5 px-3 font-medium text-zinc-800">{{ $row['label'] }}</td>
                            <td class="py-2.5 px-3 text-right text-zinc-700">{{ $row['cm'] }}</td>
                            <td class="py-2.5 px-3 text-right text-zinc-700">{{ $row['gm'] }}</td>
                            <td class="py-2.5 px-3 text-right text-zinc-700">± {{ $row['tolerance'] }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold {{ $row['is_valid'] ? 'text-zinc-700' : 'text-red-600' }}">{{ $row['variance'] }}</td>
                            <td class="py-2.5 px-3 text-right">
                                @if($row['is_valid'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Invalid
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-zinc-200 {{ $overallValid ? 'bg-emerald-50' : 'bg-red-50' }}">
                            <td colspan="5" class="py-2.5 px-3 font-bold text-sm {{ $overallValid ? 'text-emerald-800' : 'text-red-800' }}">Overall Result</td>
                            <td class="py-2.5 px-3 text-right">
                                @if($overallValid)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-500 text-white rounded-full text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Compatible
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Not Compatible
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        {{-- Actions based on result --}}
        @if($overallValid)
        {{-- VALID: continue options --}}
        <div class="tc-card space-y-4">
            <h3 class="text-base font-semibold text-zinc-800">What would you like to do next?</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button wire:click="proceedToVisualization"
                    class="flex items-center gap-3 p-4 border-2 border-primary-200 bg-primary-50 hover:border-primary-400 hover:bg-primary-100 rounded-xl transition-all group text-left">
                    <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-primary-700">AI Garment Visualization</p>
                        <p class="text-xs text-primary-600 mt-0.5">Preview how the garment looks on you (optional)</p>
                    </div>
                </button>
                <button wire:click="skipVisualization"
                    class="flex items-center gap-3 p-4 border-2 border-emerald-200 bg-emerald-50 hover:border-emerald-400 hover:bg-emerald-100 rounded-xl transition-all group text-left">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-emerald-700">Proceed to Order Confirmation</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Skip visualization and confirm your order now</p>
                    </div>
                </button>
            </div>
        </div>
        @else
        {{-- INVALID: main actions --}}
        <div class="tc-card space-y-4">
            <h3 class="text-base font-semibold text-zinc-800">What would you like to do?</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button wire:click="selectAnotherProduct"
                    class="flex items-center gap-3 p-4 border-2 border-primary-200 bg-primary-50 hover:border-primary-400 hover:bg-primary-100 rounded-xl transition-all text-left">
                    <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-primary-700">Select Another Pre-Made Garment</p>
                        <p class="text-xs text-primary-600 mt-0.5">Browse the catalog to find a better-fitting option</p>
                    </div>
                </button>
                <button wire:click="convertToCustomOrder"
                    class="flex items-center gap-3 p-4 border-2 border-amber-200 bg-amber-50 hover:border-amber-400 hover:bg-amber-100 rounded-xl transition-all text-left">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-700">Request a Custom-Made Garment</p>
                        <p class="text-xs text-amber-600 mt-0.5">Get a garment tailored precisely to your measurements</p>
                    </div>
                </button>
            </div>
            {{-- Edit measurements: secondary/optional --}}
            <div class="pt-2 border-t border-zinc-100">
                <button wire:click="editMeasurements"
                    class="flex items-center gap-2 text-sm text-zinc-500 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit measurements (only if you believe a value was entered incorrectly)
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- STEP 4 — AI Visualization placeholder + Order Confirmation + Payment  --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($step === 4)
    <div class="space-y-5">

        {{-- If order was already placed --}}
        @if($orderPlaced)
        <div class="tc-card border-2 border-emerald-300 bg-emerald-50 text-center space-y-4 py-10">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-emerald-800" style="font-family:'Poppins'">Order Placed!</h2>
                <p class="text-emerald-700 mt-1">Your order has been submitted successfully.</p>
                <p class="mt-2 font-mono font-bold text-emerald-900 text-lg">{{ $orderTracking }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="{{ route('customer.tracking') }}" wire:navigate
                    class="px-6 py-3 text-sm font-semibold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors">
                    Track My Order
                </a>
                <a href="{{ route('customer.orders') }}" wire:navigate
                    class="px-6 py-3 text-sm font-semibold bg-white border border-emerald-300 text-emerald-700 rounded-xl hover:bg-emerald-50 transition-colors">
                    View My Orders
                </a>
            </div>
        </div>

        @else

        {{-- AI Visualization placeholder (optional) --}}
        @if($showTryOn)
        <div class="tc-card space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-zinc-800">AI Garment Visualization</h3>
                    <p class="text-xs text-zinc-500">Powered by CatVTON (integration pending)</p>
                </div>
            </div>
            <div class="h-56 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 border-2 border-dashed border-primary-200 flex flex-col items-center justify-center gap-3">
                <svg class="w-10 h-10 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                <p class="text-sm font-medium text-primary-400">AI visualization will appear here</p>
                <p class="text-xs text-zinc-400">Real-time try-on processing — backend integration required</p>
            </div>
            <a href="{{ route('customer.virtual-tryon') }}" wire:navigate
                class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                Visit full Virtual Try-On page →
            </a>
        </div>
        @endif

        {{-- Order summary --}}
        <div class="tc-card space-y-4">
            <h3 class="text-base font-bold text-zinc-800" style="font-family:'Poppins'">Order Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Product</span>
                    <span class="font-semibold text-zinc-800">{{ $product->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Size</span>
                    <span class="font-semibold text-zinc-800">{{ $selectedSize }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Quantity</span>
                    <span class="font-semibold text-zinc-800">{{ $quantity }}</span>
                </div>
                <div class="flex justify-between border-t border-zinc-100 pt-2">
                    <span class="font-semibold text-zinc-700">Total</span>
                    <span class="font-bold text-xl text-primary-600">₱{{ number_format($product->price * $quantity, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payment placeholder --}}
        <div class="tc-card space-y-4">
            <h3 class="text-base font-bold text-zinc-800" style="font-family:'Poppins'">Payment</h3>
            <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-500 text-center">
                <svg class="w-8 h-8 text-zinc-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                <p class="font-medium text-zinc-600">PayMongo integration — coming soon</p>
                <p class="text-xs text-zinc-400 mt-1">Payment will be collected at pickup or via online payment once available.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['Cash on Pickup','GCash','Bank Transfer'] as $pm)
                    <span class="px-3 py-1.5 text-xs font-medium bg-zinc-100 text-zinc-600 rounded-lg border border-zinc-200">{{ $pm }}</span>
                @endforeach
            </div>
        </div>

        {{-- Confirm button --}}
        <div class="flex gap-3">
            <button wire:click="goToStep(3)" class="flex-1 px-5 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                ← Back
            </button>
            <button wire:click="placeOrder"
                wire:confirm="Place this order? Your measurements will be saved with this order."
                @if($orderSubmitting || $orderPlaced) disabled @endif
                class="flex-1 px-5 py-3 text-sm font-semibold bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="placeOrder">✓ Confirm & Place Order</span>
                <span wire:loading wire:target="placeOrder" class="flex items-center justify-center gap-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Placing Order...
                </span>
            </button>
        </div>

        @endif
    </div>
    @endif

    @else
    {{-- Product not found --}}
    <div class="tc-card text-center py-16">
        <p class="text-zinc-500">Product not found.</p>
        <a href="{{ route('customer.catalog') }}" wire:navigate class="mt-4 inline-block px-5 py-2.5 text-sm font-semibold bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors">
            Back to Catalog
        </a>
    </div>
    @endif

</div>
