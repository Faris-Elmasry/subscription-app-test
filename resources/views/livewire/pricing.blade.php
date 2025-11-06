<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.app')]
    #[Title('Customize Your Plan')]
    class extends Component {

    public array $quantities = [];
    public array $prices = [];
    public array $includedAmounts = [];
    public float $total = 0;
    public array $itemTotals = [];

    public function mount()
    {
        return redirect()->route('index');
        $items = config('pricing.items');

        // Initialize quantities with included amounts
        foreach ($items as $key => $item) {
            $this->quantities[$key] = $item['included'];
            $this->prices[$key] = $item['price_per_unit'];
            $this->includedAmounts[$key] = $item['included'];
        }

        $this->calculateTotal();
    }

    public function updatedQuantities()
    {
        // Recalculate whenever any quantity changes
        $this->calculateTotal();
    }

    public function increment($item, $amount)
    {
        $this->quantities[$item] += $amount;
        $this->calculateTotal();
    }

    public function decrement($item, $amount)
    {
        $minValue = $this->includedAmounts[$item];
        $this->quantities[$item] = max($minValue, $this->quantities[$item] - $amount);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = 0;
        $this->itemTotals = [];

        foreach ($this->quantities as $key => $quantity) {
            $included = $this->includedAmounts[$key];
            $billable = max(0, $quantity - $included);
            $itemTotal = $billable * $this->prices[$key];

            $this->itemTotals[$key] = [
                'total' => $quantity,
                'included' => $included,
                'billable' => $billable,
                'cost' => $itemTotal
            ];

            $this->total += $itemTotal;
        }
    }

    public function checkout()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Store in session
        session([
            'custom_plan' => [
                'quantities' => $this->quantities,
                'total' => $this->total,
                'itemTotals' => $this->itemTotals,
            ]
        ]);

        return redirect()->route('checkout', ['plan' => 'custom']);
    }
} ?>

<div class="min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Scale Your Workspace</h1>
            <p class="text-gray-600">Adjust the sliders to customize your plan. Only pay for what you use above the
                included amounts.</p>
        </div>

        <!-- Dynamic Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Pages Card -->
            <x-card class="relative">
                <div class="mb-4">
                    <h3 class="text-xl font-bold mb-1">📄 Pages</h3>
                    <p class="text-sm text-gray-500">€{{ number_format($prices['pages'], 2) }} per page above
                        {{ $includedAmounts['pages'] }}
                    </p>
                </div>

                <!-- Current Count -->
                <div class="bg-blue-50 rounded-lg p-4 mb-4">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ $quantities['pages'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $itemTotals['pages']['included'] }} included +
                            {{ $itemTotals['pages']['billable'] }} billable
                        </p>
                    </div>
                </div>

                <!-- Quantity Input -->
                <div class="mb-4">
                    <input type="range" wire:model.live="quantities.pages" min="{{ $includedAmounts['pages'] }}"
                        max="1000" step="10" class="range range-primary w-full" />
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>{{ $includedAmounts['pages'] }}</span>
                        <span>1000</span>
                    </div>
                </div>

                <!-- Manual Input -->
                <input type="number" wire:model.live="quantities.pages" min="{{ $includedAmounts['pages'] }}"
                    class="input input-bordered w-full mb-4" placeholder="Enter quantity" />

                <!-- Quick Actions -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button wire:click="decrement('pages', 50)" class="btn btn-sm btn-outline">-50</button>
                    <button wire:click="increment('pages', 50)" class="btn btn-sm btn-outline">+50</button>
                    <button wire:click="increment('pages', 100)" class="btn btn-sm btn-outline">+100</button>
                </div>

                <!-- Cost Display -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Monthly Cost</span>
                        <span class="text-2xl font-bold text-blue-600">
                            €{{ number_format($itemTotals['pages']['cost'], 2) }}
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Forms Card -->
            <x-card class="relative">
                <div class="mb-4">
                    <h3 class="text-xl font-bold mb-1">📝 Forms</h3>
                    <p class="text-sm text-gray-500">€{{ number_format($prices['forms'], 2) }} per form above
                        {{ $includedAmounts['forms'] }}
                    </p>
                </div>

                <!-- Current Count -->
                <div class="bg-green-50 rounded-lg p-4 mb-4">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600">{{ $quantities['forms'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $itemTotals['forms']['included'] }} included +
                            {{ $itemTotals['forms']['billable'] }} billable
                        </p>
                    </div>
                </div>

                <!-- Quantity Input -->
                <div class="mb-4">
                    <input type="range" wire:model.live="quantities.forms" min="{{ $includedAmounts['forms'] }}"
                        max="200" step="5" class="range range-success w-full" />
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>{{ $includedAmounts['forms'] }}</span>
                        <span>200</span>
                    </div>
                </div>

                <!-- Manual Input -->
                <input type="number" wire:model.live="quantities.forms" min="{{ $includedAmounts['forms'] }}"
                    class="input input-bordered w-full mb-4" placeholder="Enter quantity" />

                <!-- Quick Actions -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button wire:click="decrement('forms', 10)" class="btn btn-sm btn-outline">-10</button>
                    <button wire:click="increment('forms', 10)" class="btn btn-sm btn-outline">+10</button>
                    <button wire:click="increment('forms', 20)" class="btn btn-sm btn-outline">+20</button>
                </div>

                <!-- Cost Display -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Monthly Cost</span>
                        <span class="text-2xl font-bold text-green-600">
                            €{{ number_format($itemTotals['forms']['cost'], 2) }}
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Users Card -->
            <x-card class="relative">
                <div class="mb-4">
                    <h3 class="text-xl font-bold mb-1">👥 Users</h3>
                    <p class="text-sm text-gray-500">€{{ number_format($prices['users'], 2) }} per user above
                        {{ $includedAmounts['users'] }}
                    </p>
                </div>

                <!-- Current Count -->
                <div class="bg-purple-50 rounded-lg p-4 mb-4">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ $quantities['users'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $itemTotals['users']['included'] }} included +
                            {{ $itemTotals['users']['billable'] }} billable
                        </p>
                    </div>
                </div>

                <!-- Quantity Input -->
                <div class="mb-4">
                    <input type="range" wire:model.live="quantities.users" min="{{ $includedAmounts['users'] }}"
                        max="100" step="1" class="range range-secondary w-full" />
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>{{ $includedAmounts['users'] }}</span>
                        <span>100</span>
                    </div>
                </div>

                <!-- Manual Input -->
                <input type="number" wire:model.live="quantities.users" min="{{ $includedAmounts['users'] }}"
                    class="input input-bordered w-full mb-4" placeholder="Enter quantity" />

                <!-- Quick Actions -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button wire:click="decrement('users', 5)" class="btn btn-sm btn-outline">-5</button>
                    <button wire:click="increment('users', 5)" class="btn btn-sm btn-outline">+5</button>
                    <button wire:click="increment('users', 10)" class="btn btn-sm btn-outline">+10</button>
                </div>

                <!-- Cost Display -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Monthly Cost</span>
                        <span class="text-2xl font-bold text-purple-600">
                            €{{ number_format($itemTotals['users']['cost'], 2) }}
                        </span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Summary Card -->
        <x-card class="max-w-2xl mx-auto bg-gradient-to-r from-blue-50 to-purple-50">
            <h2 class="text-2xl font-bold mb-6 text-center">Your Custom Plan Summary</h2>

            <!-- Breakdown -->
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center p-3 bg-white rounded-lg">
                    <div>
                        <span class="font-semibold">📄 Pages</span>
                        <span class="text-sm text-gray-500 ml-2">({{ $quantities['pages'] }} total)</span>
                    </div>
                    <span class="font-bold text-blue-600">€{{ number_format($itemTotals['pages']['cost'], 2) }}</span>
                </div>

                <div class="flex justify-between items-center p-3 bg-white rounded-lg">
                    <div>
                        <span class="font-semibold">📝 Forms</span>
                        <span class="text-sm text-gray-500 ml-2">({{ $quantities['forms'] }} total)</span>
                    </div>
                    <span class="font-bold text-green-600">€{{ number_format($itemTotals['forms']['cost'], 2) }}</span>
                </div>

                <div class="flex justify-between items-center p-3 bg-white rounded-lg">
                    <div>
                        <span class="font-semibold">👥 Users</span>
                        <span class="text-sm text-gray-500 ml-2">({{ $quantities['users'] }} total)</span>
                    </div>
                    <span class="font-bold text-purple-600">€{{ number_format($itemTotals['users']['cost'], 2) }}</span>
                </div>
            </div>

            <!-- Total -->
            <div class="border-t-2 border-gray-300 pt-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold">Total Monthly Cost</span>
                    <span
                        class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        €{{ number_format($total, 2) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 text-right mt-1">Billed monthly • Cancel anytime</p>
            </div>

            <!-- CTA -->
            <x-button label="Continue to Checkout" wire:click="checkout" class="btn-primary w-full btn-lg"
                spinner="checkout">
                <x-slot:append>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </x-slot:append>
            </x-button>
        </x-card>

        <!-- Info Section -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 mb-4">
                💡 <strong>How it works:</strong> You get included amounts for free, then pay only for what you use
                beyond that.
            </p>
            <p class="text-gray-600">
                Prefer fixed plans?
                <a href="/" class="text-blue-600 hover:underline font-semibold">View standard plans</a>
            </p>
        </div>
    </div>
</div>