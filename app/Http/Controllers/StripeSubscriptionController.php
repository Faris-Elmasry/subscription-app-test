<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;

class StripeSubscriptionController extends Controller
{
    public function checkout(Request $request)
    {
        // 1️⃣ استخدم المفتاح السري من Stripe Dashboard
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = auth()->user();

        // 2️⃣ تأكد من وجود customer في Stripe
        if (!$user->stripe_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            $user->update(['stripe_id' => $customer->id]);
        } else {
            $customer = Customer::retrieve($user->stripe_id);
        }

        // 3️⃣ إنشاء session جديدة للـ checkout
        $session = StripeSession::create([
            'mode' => 'subscription',
            'customer' => $customer->id,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Test Plan (Dynamic)',
                    ],
                    'unit_amount' => 1000, // 10.00$
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
        ]);

        // 4️⃣ إعادة التوجيه إلى صفحة الدفع
        return redirect()->away($session->url);
    }
}
