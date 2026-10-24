<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views render without built assets, so tests don't depend on `npm run build`.
        $this->withoutVite();

        // Any HTTP call a test hasn't faked throws instead of reaching the network.
        Http::preventStrayRequests();
    }
}
