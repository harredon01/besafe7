<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Stripe\Event as StripeEvent;
use App\Services\ZoomMeetings;
use App\Services\Stripe;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $zoom;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $stripe;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(ZoomMeetings $zoom, Stripe $stripe) {
        $this->zoom = $zoom;
        $this->stripe = $stripe;
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhookStripe(Request $request) {
        $payload = json_decode($request->getContent(), true);
        $this->stripe->webhook($payload);
    }

    public function handleWebhookZoom(Request $request) {
        $payload = json_decode($request->getContent(), true);
        $this->zoom->webhook($payload);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
}
