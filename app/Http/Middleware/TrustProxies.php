<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The proxy header mappings.
     *
     * @var array
     */
    protected $headers = [
        \Illuminate\Http\Request::HEADER_FORWARDED => 'FORWARDED',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB => 'X_FORWARDED_AWS_ELB',
    ];
}
