<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.app')]
    #[Title('Payment Cancelled')]
    class extends Component {
    //
};
?>

<div class="min-h-screen py-12 flex items-center justify-center">
    <div class="max-w-md mx-auto px-4">
        <x-card>
            <div class="text-center">
                <!-- Cancel Icon -->
                <div class="mx-auto w-16 h-16 bg-warning/10 rounded-full flex items-center justify-center mb-6">
                    <x-icon name="o-x-circle" class="w-10 h-10 text-warning" />
                </div>

                <h1 class="text-3xl font-bold mb-4">Payment Cancelled</h1>
                <p class="text-gray-600 mb-8">
                    Your payment was cancelled. No charges were made to your account.
                </p>

                <div class="flex gap-4">
                    <x-button label="Back to Home" link="/" class="btn-outline flex-1" />
                    <x-button label="Try Again" link="/pricing" class="btn-primary flex-1" />
                </div>
            </div>
        </x-card>
    </div>
</div>