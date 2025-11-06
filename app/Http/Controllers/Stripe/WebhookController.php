<?php

namespace App\Http\Controllers\Stripe;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    /**
     * Handle checkout session completed
     * This is triggered when a customer completes the checkout
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];

        $user = $this->getUserByStripeId($session['customer']);

        if (!$user) {
            return;
        }

        // Get plan from metadata
        $plan = $session['metadata']['plan'] ?? 'basic';

        // Update user with plan info
        $user->update([
            'plan' => $plan,
        ]);

        \Log::info('Checkout completed', [
            'user_id' => $user->id,
            'plan' => $plan,
            'subscription_id' => $session['subscription']
        ]);
    }

    /**
     * Handle customer subscription created
     * Cashier handles most of this, but you can add custom logic
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        parent::handleCustomerSubscriptionCreated($payload);

        $subscription = $payload['data']['object'];
        $user = $this->getUserByStripeId($subscription['customer']);

        if ($user) {
            \Log::info('Subscription created for user', [
                'user_id' => $user->id,
                'subscription_id' => $subscription['id'],
                'status' => $subscription['status']
            ]);
        }
    }

    /**
     * Handle customer subscription updated
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        parent::handleCustomerSubscriptionUpdated($payload);

        $subscription = $payload['data']['object'];
        $user = $this->getUserByStripeId($subscription['customer']);

        if ($user) {
            \Log::info('Subscription updated', [
                'user_id' => $user->id,
                'status' => $subscription['status']
            ]);
        }
    }

    /**
     * Handle customer subscription deleted
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $payload['data']['object'];
        $user = $this->getUserByStripeId($subscription['customer']);

        if ($user) {
            $user->update(['plan' => null]);

            \Log::info('Subscription cancelled', [
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Handle invoice paid
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        parent::handleInvoicePaymentSucceeded($payload);

        $invoice = $payload['data']['object'];
        $user = $this->getUserByStripeId($invoice['customer']);

        if ($user) {
            \Log::info('Invoice paid', [
                'user_id' => $user->id,
                'amount' => $invoice['amount_paid'] / 100,
                'invoice_id' => $invoice['id']
            ]);
        }
    }

    /**
     * Handle invoice payment failed
     */
    protected function handleInvoicePaymentFailed(array $payload)
    {
        parent::handleInvoicePaymentFailed($payload);

        $invoice = $payload['data']['object'];
        $user = $this->getUserByStripeId($invoice['customer']);

        if ($user) {
            \Log::warning('Invoice payment failed', [
                'user_id' => $user->id,
                'invoice_id' => $invoice['id']
            ]);

            // Optional: Send notification to user
            // $user->notify(new PaymentFailedNotification());
        }
    }
}
