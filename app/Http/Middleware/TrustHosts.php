<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $hosts = [
            // Allow requests from the app URL
            $this->allSubdomainsOfApplicationUrl(),
        ];

        // Add additional trusted hosts from environment variable
        $trustedHosts = env('TRUSTED_HOSTS');
        if ($trustedHosts) {
            $additionalHosts = explode(',', $trustedHosts);
            foreach ($additionalHosts as $host) {
                $host = trim($host);
                if ($host) {
                    // Convert wildcard domains to regex pattern
                    if (str_starts_with($host, '*.')) {
                        $pattern = str_replace('*.', '.*\.', $host);
                        $hosts[] = '/^' . preg_quote($pattern, '/') . '$/i';
                    } else {
                        $hosts[] = $host;
                    }
                }
            }
        }

        // Add local development hosts
        if (app()->environment('local')) {
            $hosts = array_merge($hosts, [
                'localhost',
                '127.0.0.1',
                '[::1]',
            ]);
        }

        // Add testing hosts in testing environment
        if (app()->environment('testing')) {
            $hosts[] = 'testing.example.com';
        }

        return array_unique($hosts);
    }

    /**
     * Get the patterns that should be trusted implicitly.
     *
     * @return array
     */
    protected function getTrustedHosts(): array
    {
        $patterns = [];

        foreach ($this->hosts() as $host) {
            if (is_string($host)) {
                // Convert simple wildcards to regex patterns
                if (str_contains($host, '*')) {
                    $pattern = str_replace('\\*', '.*', preg_quote($host, '/'));
                    $patterns[] = '/^' . $pattern . '$/i';
                } else {
                    $patterns[] = '/^' . preg_quote($host, '/') . '$/i';
                }
            } else {
                $patterns[] = $host;
            }
        }

        return $patterns;
    }

    /**
     * Determine if the request host is trusted.
     *
     * @param  string  $host
     * @return bool
     */
    protected function isTrustedHost(string $host): bool
    {
        foreach ($this->getTrustedHosts() as $pattern) {
            if (preg_match($pattern, $host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle an untrusted host.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function handleUntrustedHost($request): void
    {
        // Log untrusted host access attempt
        if (function_exists('audit_log')) {
            audit_log('untrusted_host_access', [
                'host' => $request->getHost(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        abort(400, 'Invalid Host Header');
    }
}
