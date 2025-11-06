<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * Mass assignable attributes.
     *
     * Using guarded = ['id'] allows all fields except id to be fillable.
     */
    protected $guarded = ['id'];

    /**
     * Hidden attributes (for security).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute type casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relationships
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    /**
     * Subscription Helpers (Cashier)
     */

    // Check if user has an active subscription
    public function hasActiveSubscription(): bool
    {
        return $this->subscribed('default');
    }

    // Check if user is on a specific plan (basic, pro, premium, etc.)
    public function isOnPlan(string $plan): bool
    {
        return $this->plan === $plan && $this->hasActiveSubscription();
    }

    // Get the current plan name assigned in database
    public function getCurrentPlan(): ?string
    {
        return $this->plan;
    }

    // Check if user subscription is in grace period
    public function isOnGracePeriod(): bool
    {
        return $this->subscription('default')?->onGracePeriod() ?? false;
    }

    // The date subscription access ends if canceled
    public function subscriptionEndsAt(): ?\Carbon\Carbon
    {
        return $this->subscription('default')?->ends_at;
    }
}
