<?php

namespace Codenteq\Iyzico\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Codenteq\Iyzico\Events\WebhookReceived;
use Codenteq\Iyzico\Http\Middleware\VerifyWebhookSignature;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(VerifyWebhookSignature::class);
    }

    public function handleWebhook(Request $request): Response
    {
        $payload = $request->all();

        WebhookReceived::dispatch($payload);

        $method = 'handle'.str_replace('.', '', ucwords($payload['iyziEventType'], '.'));

        if (method_exists($this, $method)) {
            $this->{$method}($payload);
        }

        return new Response('Webhook Handled', 200);
    }

    protected function handleSubscriptionPaymentSucceeded(array $payload)
    {
        // Ödeme başarılı webhook'u işleme
    }

    protected function handleSubscriptionPaymentFailed(array $payload)
    {
        // Ödeme başarısız webhook'u işleme
    }
}
