<?php
// PHP 8.5+: Cookies Particionados (CHIPS — Cookies Having Independent Partitioned State)
// Útil para cookies em iframes de terceiros
// https://developer.chrome.com/docs/privacy-sandbox/chips/
setcookie(
    'widget_pref',
    'dark',
    [
        'expires'      => time() + 86400 * 30,
        'path'         => '/',
        'secure'       => true,
        'httponly'     => true,
        'samesite'     => 'None',       // Requer None para cross-site
        'partitioned'  => true,          // PHP 8.5+ NOVIDADE!
    ]
);
