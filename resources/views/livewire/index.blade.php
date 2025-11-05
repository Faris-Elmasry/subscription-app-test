<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.app')]
    #[Title('Subscription Plans')]
    class extends Component {

    public array $plans = [];

    public function mount()
    {
        $this->plans = [
            [
                'name' => 'Basic',
                'price' => '9.99',
                'stripe_price_id' => 'price_basic_monthly',
                'popular' => false,
                'features' => [
                    'Basic Features',
                    'Email Support',
                    '1 User',
                ]
            ],
            [
                'name' => 'Pro',
                'price' => '29.99',
                'stripe_price_id' => 'price_pro_monthly',
                'popular' => true,
                'features' => [
                    'All Basic Features',
                    'Priority Support',
                    '5 Users',
                    'Advanced Analytics',
                ]
            ],
            [
                'name' => 'Premium',
                'price' => '99.99',
                'stripe_price_id' => 'price_premium_monthly',
                'popular' => false,
                'features' => [
                    'All Pro Features',
                    '24/7 Phone Support',
                    'Unlimited Users',
                    'Custom Integrations',
                    'Dedicated Account Manager',
                ]
            ],
        ];
    }

    public function subscribe($planName)
    {
        if (!auth()->check()) {
            return redirect('/register');
        }

        return redirect()->route('checkout', ['plan' => strtolower($planName)]);
    }
} ?>

<div class="min-h-screen py-12">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Choose Your Plan</h1>
        <p class="text-gray-600">Select the perfect plan for your needs</p>
    </div>

    <!-- Plans Grid -->
    <!-- Plans Horizontal Layout -->
    <!-- Horizontal Pricing Row -->
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-8 justify-center">
            @foreach($plans as $plan)
                <x-card class="w-full md:w-1/3 hover:shadow-xl transition
                                {{ $plan['popular'] ? 'border-2 border-primary scale-105' : '' }}">
                    @if($plan['popular'])
                        <x-badge value="POPULAR" class="badge-primary mb-4" />
                    @endif

                    <h3 class="text-2xl font-bold mb-4">{{ $plan['name'] }}</h3>

                    <div class="mb-6">
                        <span class="text-4xl font-bold">${{ $plan['price'] }}</span>
                        <span class="text-gray-600">/month</span>
                    </div>

                    <ul class="mb-8 space-y-3">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-center gap-2">
                                <x-icon name="o-check-circle" class="w-5 h-5 text-success" />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <x-button label="Subscribe to {{ $plan['name'] }}" wire:click="subscribe('{{ $plan['name'] }}')"
                        class="btn-primary w-full" spinner="subscribe('{{ $plan['name'] }}')" />
                </x-card>
            @endforeach
        </div>
    </div>

    <!-- Already have account -->
    <div class="text-center mt-12">
        <p class="text-gray-600">
            Already have an account?
            <x-button label="Sign in" link="/login" class="btn-link" />
        </p>
    </div>
</div>