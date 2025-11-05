<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.app')]
    #[Title('Payment Successful')]
    class extends Component {

    public $session = null;
    public $error = false;

    public function mount()
    {
        $sessionId = request()->get('session_id');

        if (!$sessionId) {
            $this->redirect('/', navigate: true);
            return;
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $this->session = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Exception $e) {
            $this->error = true;
            session()->flash('error', 'Unable to retrieve payment details.');
        }
    }
};
?>

<div class="min-h-screen py-12 flex items-center justify-center">
    <div class="max-w-md mx-auto px-4">
        <x-card>
            @if($error)
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-error/10 rounded-full flex items-center justify-center mb-6">
                        <x-icon name="o-x-circle" class="w-10 h-10 text-error" />
                    </div>
                    <h1 class="text-3xl font-bold mb-4">Error</h1>
                    <p class="text-gray-600 mb-8">
                        Unable to retrieve payment details. Please contact support if you were charged.
                    </p>
                    <x-button label="Go to Dashboard" link="/" class="btn-primary" />
                </div>
            @else
                <div class="text-center">
                    <!-- Success Icon -->
                    <div class="mx-auto w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mb-6">
                        <x-icon name="o-check-circle" class="w-10 h-10 text-success" />
                    </div>

                    <h1 class="text-3xl font-bold mb-4">Payment Successful! 🎉</h1>
                    <p class="text-gray-600 mb-8">
                        Your subscription is now active. Thank you for your purchase!
                    </p>

                    @if($session)
                        <div class="bg-gray-50 rounded-lg p-4 mb-8">
                            <p class="text-sm text-gray-600 mb-2">
                                <strong>Amount Paid:</strong> ${{ number_format($session->amount_total / 100, 2) }}
                            </p>
                            <p class="text-sm text-gray-600">
                                You can manage your subscription anytime from your account dashboard.
                            </p>
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <x-button label="Go to Dashboard" link="/" class="btn-primary flex-1" />
                        <x-button label="View Plans" link="/pricing" class="btn-outline flex-1" />
                    </div>
                </div>
            @endif
        </x-card>
    </div>
</div>