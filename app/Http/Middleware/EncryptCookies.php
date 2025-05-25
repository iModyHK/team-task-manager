<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Debug and development cookies
        'XDEBUG_SESSION',
        'debug',
        
        // Third-party service cookies that handle their own encryption
        'google_analytics',
        'fb_pixel',
        
        // Performance monitoring cookies
        'server_timings',
        'performance_metrics',
        
        // Feature flags and A/B testing cookies
        'feature_flags',
        'ab_test_group',
        
        // Theme and display preferences that don't need encryption
        'theme_preference',
        'display_mode',
        'font_size',
    ];

    /**
     * Determine if the cookie should be encrypted.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isDisabled($name)
    {
        // Check the exception list
        if (in_array($name, $this->except)) {
            return true;
        }

        // Check for patterns in cookie names that should be excluded
        $excludePatterns = [
            '/^_ga/', // Google Analytics cookies
            '/^_pk_/', // Piwik/Matomo cookies
            '/^_hj/', // Hotjar cookies
            '/^mp_/', // Mixpanel cookies
        ];

        foreach ($excludePatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add cookies to the exception list.
     */
    public function addExcept(array $cookies): void
    {
        $this->except = array_merge($this->except, $cookies);
    }

    /**
     * Remove cookies from the exception list.
     */
    public function removeExcept(array $cookies): void
    {
        $this->except = array_diff($this->except, $cookies);
    }

    /**
     * Get the list of excepted cookies.
     */
    public function getExcept(): array
    {
        return $this->except;
    }

    /**
     * Check if a cookie is in the exception list.
     */
    public function isExcepted(string $cookie): bool
    {
        return in_array($cookie, $this->except);
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    protected function getEncryptionKey()
    {
        $key = $this->key ?: config('app.key');

        if (empty($key)) {
            throw new \RuntimeException('No application encryption key has been specified.');
        }

        return $key;
    }

    /**
     * Determine if cookies should be serialized.
     *
     * @return bool
     */
    protected function serialized()
    {
        return true;
    }
}
