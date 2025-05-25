<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Authentication fields
        'current_password',
        'password',
        'password_confirmation',
        
        // Common fields that might need whitespace
        'description',
        'content',
        'message',
        'notes',
        'comments',
        'address',
        'bio',
        
        // Rich text editor fields
        'body',
        'html_content',
        'rich_text',
        
        // Code fields
        'code',
        'source_code',
        'css',
        'javascript',
        'html',
        
        // Markdown fields
        'markdown',
        'md_content',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true) || !is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        // Normalize whitespace (convert multiple spaces to single space)
        if ($this->shouldNormalizeWhitespace($key)) {
            $trimmed = preg_replace('/\s+/', ' ', $trimmed);
        }

        // Remove invisible characters
        if ($this->shouldRemoveInvisibleChars($key)) {
            $trimmed = preg_replace('/[\x00-\x1F\x7F]/u', '', $trimmed);
        }

        return $trimmed;
    }

    /**
     * Determine if whitespace should be normalized for the given key.
     */
    protected function shouldNormalizeWhitespace(string $key): bool
    {
        $normalizeFields = [
            'name',
            'title',
            'subject',
            'heading',
            'label',
            'username',
            'search',
            'tags',
        ];

        return in_array($key, $normalizeFields) || 
            str_contains($key, '_name') || 
            str_contains($key, '_title');
    }

    /**
     * Determine if invisible characters should be removed for the given key.
     */
    protected function shouldRemoveInvisibleChars(string $key): bool
    {
        $sensitiveFields = [
            'email',
            'username',
            'phone',
            'url',
            'slug',
            'path',
            'filename',
        ];

        return in_array($key, $sensitiveFields) || 
            str_contains($key, '_email') || 
            str_contains($key, '_url');
    }

    /**
     * Add attributes to the exception list.
     */
    public function addExcept(array $attributes): void
    {
        $this->except = array_merge($this->except, $attributes);
    }

    /**
     * Remove attributes from the exception list.
     */
    public function removeExcept(array $attributes): void
    {
        $this->except = array_diff($this->except, $attributes);
    }

    /**
     * Get the list of excepted attributes.
     */
    public function getExcept(): array
    {
        return $this->except;
    }

    /**
     * Check if an attribute is in the exception list.
     */
    public function isExcepted(string $attribute): bool
    {
        return in_array($attribute, $this->except);
    }
}
