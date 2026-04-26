<?php

namespace App\Services;

use Stripe\Stripe;

class PaymentService
{
    /**
     * VULN #5 — Hardcoded secret (Stripe API key)
     * Detectado por:
     *   - p/secrets               (regla generica)
     *   - .semgrep/no-hardcoded-credentials.yml  (regla custom de este repo)
     * Fix: Stripe::setApiKey(config('services.stripe.secret'))
     *      con STRIPE_SECRET en .env (nunca commitear).
     */
    public function __construct()
    {
        Stripe::setApiKey("sk_test_FAKEDEMOKEY01");

        $token = "Bearer FAKEDEMOTOKEN0123456789";
        $this->callExternalApi($token);
    }

    private function callExternalApi(string $token): void
    {
    }
}
