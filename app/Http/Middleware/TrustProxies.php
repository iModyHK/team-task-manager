<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [
        // Add trusted proxy IP addresses here
        // '192.168.1.1',
        // '192.168.1.2',
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Sets the trusted proxies on the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function setTrustedProxyIpAddresses(Request $request): void
    {
        // Get proxies from environment variable if set
        $envProxies = env('TRUSTED_PROXIES');
        if ($envProxies) {
            $this->proxies = explode(',', $envProxies);
        }

        // Get headers from environment variable if set
        $envHeaders = env('TRUSTED_PROXY_HEADERS');
        if ($envHeaders) {
            $this->headers = (int) $envHeaders;
        }

        parent::setTrustedProxyIpAddresses($request);
    }

    /**
     * Add a trusted proxy.
     *
     * @param  string  $proxy
     * @return void
     */
    public function addTrustedProxy(string $proxy): void
    {
        if (!in_array($proxy, $this->proxies)) {
            $this->proxies[] = $proxy;
        }
    }

    /**
     * Remove a trusted proxy.
     *
     * @param  string  $proxy
     * @return void
     */
    public function removeTrustedProxy(string $proxy): void
    {
        $key = array_search($proxy, $this->proxies);
        if ($key !== false) {
            unset($this->proxies[$key]);
        }
    }

    /**
     * Get the list of trusted proxies.
     *
     * @return array
     */
    public function getTrustedProxies(): array
    {
        return is_array($this->proxies) ? $this->proxies : [];
    }

    /**
     * Get the trusted headers.
     *
     * @return int
     */
    public function getTrustedHeaders(): int
    {
        return $this->headers;
    }
}
