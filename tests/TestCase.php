<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * To Fix error 419 in request to web middleware
     * Only use for laravel sanctum auth
     */
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $res = parent::json('get', '/sanctum/csrf-cookie');
        $cookies = $res->headers->getCookies();
        $XSRF_TOKEN = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $XSRF_TOKEN = $cookie->getValue();
            }
        }
        $headers = array_merge($headers, [
            'X-XSRF-TOKEN' => $XSRF_TOKEN,
        ]);
        return parent::json($method, $uri, $data, $headers);
    }
}
