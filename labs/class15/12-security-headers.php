<?php
// Headers de segurança recomendados
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY'); // ou SAMEORIGIN
header('X-XSS-Protection: 0');    // obsoleto mas não custa

// Content Security Policy (CSP)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Desabilitar exposição da versão do PHP
// No php.ini: expose_php =

function applySecurityHeaders(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (!headers_sent()) {
        header('Content-Security-Policy: '
            . "default-src 'self'; "
            . "script-src 'self'; "
            . "style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' data: https:; "
            . "font-src 'self'; "
            . "connect-src 'self'; "
            . "frame-ancestors 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self';"
        );
    }
}

// Chamar no início de cada requisição
applySecurityHeaders();
