<?php

namespace App\Listeners;

use App\Events\StripeInvoicePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleStripeInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(StripeInvoicePaid $event)
    {
        $payload = $event->payload;

        $invoiceId  = $payload['data']['object']['id'] ?? null;
        $customerId = $payload['data']['object']['customer'] ?? null;
        $amountPaid = $payload['data']['object']['amount_paid'] ?? null;

        Log::info("Invoice paid", [
            'invoice'  => $invoiceId,
            'customer' => $customerId,
            'amount'   => $amountPaid,
        ]);

        // Example: update user records
        // $user = User::where('stripe_id', $customerId)->first();
        // if ($user) { $user->markInvoicePaid($invoiceId); }
    }
}
