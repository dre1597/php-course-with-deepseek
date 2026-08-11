<?php

class SecurityHeaders
{
    private array $headers;

    public function __construct(array $custom = [])
    {
        $this->headers = array_merge([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'DENY',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        ], $custom);
    }

    public function set(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function remove(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
