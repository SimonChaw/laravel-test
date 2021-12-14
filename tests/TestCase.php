<?php

namespace Tests;

use App\Models\ApiToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static string $token;

    public function setUp(): void {
        parent::setUp();

        $token = Str::random(60);

        // Revoke old tokens
        ApiToken::whereNotNull('api_token')->delete();

        ApiToken::create(['api_token' => hash('sha256', $token)]);

        self::$token = $token;
    }
}
