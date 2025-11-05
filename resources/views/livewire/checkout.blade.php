<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Price;

new
    #[Layout('components.layouts.app')]
    #[Title('Checkout')]
    class extends Component {

    public string $plan = '';
    public array $planDetails = [];
    public bool $isCustomPlan = false;
    public array $lineItems = [];

    public function mount($plan)
    {
        $this->plan = $plan;

        if ($plan === 'custom') {
            $customPlan = session('custom_plan');
            if (!$customPlan) {
                return $this->redirect('/pricing', navigate: true);
            }

            $this->isCustomPlan = true;
            $this->planDetails = [
                'name' => 'Custom Flexible Plan',
                'price' => number_format($customPlan['total'], 2),
                'quantities' => $customPlan['quantities'],
                'itemTotals' => $customPlan['itemTotals'],
            ];

            // Prepare line items for display
            $this->prepareLineItems();
        } else {
            // Fixed plans
            $plans = [
                'basic' => [
                    'name' => 'Basic',
                    'price' => '9.99',
                    'stripe_price_id' => env('STRIPE_PRICE_BASIC'),
                ],
                'pro' => [
                    'name' => 'Pro',
                    'price' => '29.99',
                    'stripe_price_id' => env('STRIPE_PRICE_PRO'),
                ],
                'premium' => [
                    'name' => 'Premium',
                    'price' => '99.99',
                    'stripe_price_id' => env('STRIPE_PRICE_PREMIUM'),
                ],
            ];

            if (!isset($plans[$plan])) {
                return $this->redirect('/', navigate: true);
            }

            $this->planDetails = $plans[$plan];
        }
    }

    private function prepareLineItems()
    {
        $items = config('pricing.items');
        $this->lineItems = [];

        foreach ($this->planDetails['itemTotals'] as $key => $totals) {
            if ($totals['billable'] > 0) {
                $this->lineItems[] = [
                    'name' => $items[$key]['name'],
                    'quantity' => $totals['billable'],
                    'unit_price' => $items[$key]['price_per_unit'],
                    'total' => $totals['cost'],
                ];
            }
        }
    }

    public function checkout()
    {
        try {
            if (!auth()->check()) {
                $this->dispatch('error', 'You must be logged in to subscribe.');
                return;
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $user = auth()->user();

            // Create or get Stripe customer
            if (!$user->stripe_id) {
                $user->createAsStripeCustomer([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            }

            if ($this->isCustomPlan) {
                // Build dynamic line items for Stripe
                $stripeLineItems = $this->buildStripeLineItems();

                if (empty($stripeLineItems)) {
                    $this->dispatch('error', 'Please select at least one billable item.');
                    return;
                }

                // Create Stripe Checkout Session with dynamic pricing
                $checkoutSession = Session::create([
                    'customer' => $user->stripe_id,
                    'mode' => 'subscription',
                    'line_items' => $stripeLineItems,
                    'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('checkout.cancel'),
                    'allow_promotion_codes' => true,
                    'billing_address_collection' => 'auto',
                    'metadata' => [
                        'plan_type' => 'custom',
                        'pages' => $this->planDetails['quantities']['pages'],
                        'forms' => $this->planDetails['quantities']['forms'],
                        'users' => $this->planDetails['quantities']['users'],
                    ],
                ]);

                return redirect()->away($checkoutSession->url);

            } else {
                // Fixed plan using Cashier
                if (!isset($this->planDetails['stripe_price_id'])) {
                    $this->dispatch('error', 'Invalid plan selected.');
                    return;
                }

                $checkout = $user
                    ->newSubscription('default', $this->planDetails['stripe_price_id'])
                    ->checkout([
                        'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('checkout.cancel'),
                    ]);

                return redirect()->away($checkout->url);
            }

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->dispatch('error', 'Stripe error: ' . $e->getMessage());
            \Log::error('Stripe checkout error', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->dispatch('error', 'Something went wrong: ' . $e->getMessage());
            \Log::error('Checkout error', ['error' => $e->getMessage()]);
        }
    }

    private function buildStripeLineItems(): array
    {
        $items = config('pricing.items');
        $stripeLineItems = [];

        foreach ($this->planDetails['itemTotals'] as $key => $totals) {
            if ($totals['billable'] > 0) {
                $stripeLineItems[] = [
                    'price' => $items[$key]['stripe_price_id'],
                    'quantity' => $totals['billable'],
                ];
            }
        }

        return $stripeLineItems;
    }
} ?>

<div class="min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <x-card>
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">Complete Your Order</h1>
                <p class="text-gray-600">Subscribe to {{ $planDetails['name'] }}</p>
            </div>

            @if(session('error'))
                <x-alert title="Error" description="{{ session('error') }}" icon="o-exclamation-triangle"
                    class="alert-error mb-6" />
            @endif

            <!-- Plan Summary -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $planDetails['name'] }}</h3>
                    <span
                        class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        €{{ $planDetails['price'] }}/mo
                    </span>
                </div>

                @if($isCustomPlan)
                    <!-- Custom Plan Breakdown -->
                    <div class="space-y-3 mt-4">
                        @foreach($lineItems as $item)
                            <div class="flex justify-between items-center bg-white rounded-lg p-3">
                                <div>
                                    <span class="font-semibold">{{ $item['name'] }}</span>
                                    <span class="text-sm text-gray-500 ml-2">
                                        {{ $item['quantity'] }} × €{{ number_format($item['unit_price'], 2) }}
                                    </span>
                                </div>
                                <span class="font-bold">€{{ number_format($item['total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="text-sm text-gray-600">
                            <strong>Total Resources:</strong>
                            {{ $planDetails['quantities']['pages'] }} pages,
                            {{ $planDetails['quantities']['forms'] }} forms,
                            {{ $planDetails['quantities']['users'] }} users
                        </p>
                    </div>
                @else
                    <p class="text-gray-600 text-sm">Billed monthly • Cancel anytime</p>
                @endif
            </div>

            <!-- Customer Info -->
            <div class="mb-8">
                <h3 class="font-semibold mb-4">Billing Information</h3>
                <div class="space-y-2 text-sm bg-gray-50 rounded-lg p-4">
                    <p><span class="text-gray-600">Name:</span> <strong>{{ auth()->user()->name }}</strong></p>
                    <p><span class="text-gray-600">Email:</span> <strong>{{ auth()->user()->email }}</strong></p>
                </div>
            </div>

            <!-- Important Info -->
            @if($isCustomPlan)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                You can upgrade or downgrade your plan anytime from your account dashboard.
                                Changes will be prorated automatically.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
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
