<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.app')]
    #[Title('Checkout')]
    class extends Component {
    public string $plan = '';
    public array $planDetails = [];

    public function mount($plan)
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 9.99,
                'price_id' => env('STRIPE_BASIC_PRICE_ID'), // Add to .env
                'interval' => 'month',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 29.99,
                'price_id' => env('STRIPE_PRO_PRICE_ID'), // Add to .env
                'interval' => 'month',
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 99.99,
                'price_id' => env('STRIPE_PREMIUM_PRICE_ID'), // Add to .env
                'interval' => 'month',
            ],
        ];

        if (!isset($plans[$plan])) {
            return $this->redirect('/pricing', navigate: true);
        }

        $this->plan = $plan;
        $this->planDetails = $plans[$plan];
    }

    public function checkout()
    {
        try {
            if (!auth()->check()) {
                $this->dispatch('error', 'You must be logged in to subscribe.');
                return;
            }

            $user = auth()->user();

            // Check if user already has an active subscription
            if ($user->subscribed('default')) {
                $this->dispatch('error', 'You already have an active subscription.');
                return;
            }

            // Create checkout session using Cashier
            $checkout = $user->newSubscription('default', $this->planDetails['price_id'])
                ->checkout([
                    'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('checkout.cancel'),
                    'billing_address_collection' => 'auto',
                    'allow_promotion_codes' => true,
                    'metadata' => [
                        'plan' => $this->plan,
                        'user_id' => $user->id,
                    ],
                ]);

            return redirect()->away($checkout->url);
        } catch (\Exception $e) {
            \Log::error('Stripe checkout error', ['error' => $e->getMessage()]);
            $this->dispatch('error', 'Checkout error: ' . $e->getMessage());
        }
    }
}
?>

<div class="min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <x-card>
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">Complete Your Order</h1>
                <p class="text-gray-600">Subscribe to {{ $planDetails['name'] }} Plan</p>
            </div>

            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">{{ $planDetails['name'] }}</h3>
                    <span
                        class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        ${{ $planDetails['price'] }}/{{ $planDetails['interval'] }}
                    </span>
                </div>
                <p class="text-gray-600 text-sm mt-4">Billed monthly • Cancel anytime</p>
            </div>

            <div class="mb-8">
                <h3 class="font-semibold mb-4">Billing Information</h3>
                <div class="space-y-2 text-sm bg-gray-50 rounded-lg p-4">
                    <p><span class="text-gray-600">Name:</span> <strong>{{ auth()->user()->name }}</strong></p>
                    <p><span class="text-gray-600">Email:</span> <strong>{{ auth()->user()->email }}</strong></p>
                </div>
            </div>

            <div class="flex gap-4">
                <x-button label="← Back" link="/pricing" class="btn-outline flex-1" />
                <x-button label="Proceed to Payment" wire:click="checkout" class="btn-primary flex-1"
                    spinner="checkout">
                    <x-slot:append>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </x-slot:append>
                </x-button>
            </div>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    🔒 Secure payment powered by Stripe • Your data is encrypted
                </p>
            </div>
        </x-card>
    </div>
</div>