<?php

namespace Popcorn\Auth;

use GuzzleHttp\Promise\PromiseInterface;

class ClientFactory {
    public static function create(): PromiseInterface {
        $request = new \GuzzleHttp\Psr7\Request('GET', Config::$OKTA_JWK_KEY_PATH);

        $client = new \GuzzleHttp\Client(['base_uri' => Config::$OKTA_SERVER_HOSTNAME]);

        return $client->sendAsync($request);
    }
}