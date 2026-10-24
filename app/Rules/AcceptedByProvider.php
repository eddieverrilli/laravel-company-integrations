<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Makes one test call to the provider with the key, so a key that doesn't
 * work is a validation error rather than a saved integration that fails later.
 */
class AcceptedByProvider implements ValidationRule
{
    public function validate(string $attribute, #[\SensitiveParameter] mixed $value, Closure $fail): void
    {
        try {
            $response = Http::baseUrl(config('services.ads_api.url'))
                ->withToken($value)
                ->acceptJson()
                ->timeout(5)
                ->get('v1/me');
        } catch (ConnectionException) {
            $fail("We couldn't reach the provider to check this key. Try again.");

            return;
        }

        if ($response->clientError()) {
            $fail('The provider rejected this API key.');
        } elseif ($response->failed()) {
            $fail("The provider couldn't check this key just now. Try again.");
        }
    }
}
