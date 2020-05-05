<?php

namespace Laravel\Paddle;

use Carbon\Carbon;

class SubscriptionBuilder
{
    /**
     * The model that is subscribing.
     *
     * @var \Laravel\Paddle\Billable
     */
    protected $billable;

    /**
     * The name of the subscription.
     *
     * @var string
     */
    protected $name;

    /**
     * The plan of the subscription.
     *
     * @var int
     */
    protected $plan;

    /**
     * The quantity of the subscription.
     *
     * @var int
     */
    protected $quantity = 1;

    /**
     * The days until the trial will expire.
     *
     * @var int|null
     */
    protected $trialDays;

    /**
     * Indicates that the trial should end immediately.
     *
     * @var bool
     */
    protected $skipTrial = false;

    /**
     * The coupon code being applied to the customer.
     *
     * @var string|null
     */
    protected $coupon;

    /**
     * Create a new subscription builder instance.
     *
     * @param  \Laravel\Paddle\Billable  $owner
     * @param  string  $name
     * @param  int  $plan
     * @return void
     */
    public function __construct($owner, $name, $plan)
    {
        $this->name = $name;
        $this->plan = $plan;
        $this->billable = $owner;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param  int  $quantity
     * @return $this
     */
    public function quantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Specify the number of days of the trial.
     *
     * @param  int  $trialDays
     * @return $this
     */
    public function trialDays($trialDays)
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param  \Carbon\Carbon|\Carbon\CarbonInterface  $trialUntil
     * @return $this
     */
    public function trialUntil($trialUntil)
    {
        $this->trialDays = Carbon::now()->diffInDays($trialUntil);

        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * @return $this
     */
    public function skipTrial()
    {
        $this->skipTrial = true;

        return $this;
    }

    /**
     * The coupon to apply to a new subscription.
     *
     * @param  string  $coupon
     * @return $this
     */
    public function withCoupon($coupon)
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * Generate a pay link for a subscription.
     *
     * @param  array  $options
     * @return string
     */
    public function create(array $options = [])
    {
        $payload = array_merge($this->buildPayload(), $options);

        if (! is_null($trialDays = $this->getTrialEndForPayload())) {
            $payload['trial_days'] = $trialDays;
        }

        $payload['passthrough'] = "{$this->billable->getKey()},{$this->name}";

        return $this->billable->chargeProduct($this->plan, $payload);
    }

    /**
     * Build the payload for subscription creation.
     *
     * @return array
     */
    protected function buildPayload()
    {
        return  [
            'coupon_code' => (string) $this->coupon,
            'quantity' => $this->quantity,
        ];
    }

    /**
     * Get the days until the trial will expire for the Paddle payload.
     *
     * @return int|null
     */
    protected function getTrialEndForPayload()
    {
        if ($this->skipTrial) {
            return 0;
        }

        return $this->trialDays;
    }
}