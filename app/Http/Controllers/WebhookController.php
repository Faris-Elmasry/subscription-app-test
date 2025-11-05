<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];

        // Find user by Stripe customer ID
        $user = \App\Models\User::where('stripe_id', $session['customer'])->first();

        if ($user) {
            // Store subscription metadata
            $user->update([
                'subscribed' => true,
                'subscription_id' => $session['subscription'],
            ]);

            \Log::info('Subscription created', ['user' => $user->id]);
        }

        return response('Webhook handled', 200);
    }
}
