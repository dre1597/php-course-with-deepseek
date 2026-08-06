<?php
// HTTP method
echo "Method: {$_SERVER['REQUEST_METHOD']}<br>\n";

// Request URI
echo "URI: {$_SERVER['REQUEST_URI']}<br>\n";

// Host header
echo "Host: {$_SERVER['HTTP_HOST']}<br>\n";

// Client User-Agent
echo "Browser: {$_SERVER['HTTP_USER_AGENT']}<br>\n";

// Client IP (considering proxies)
$ip = $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['HTTP_CLIENT_IP']
    ?? $_SERVER['REMOTE_ADDR'];
echo "IP: {$ip}<br>\n";

// Referrer (previous page)
$referrer = $_SERVER['HTTP_REFERER'] ?? 'None';
echo "Came from: {$referrer}<br>\n";

// Protocol (HTTP or HTTPS)
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$protocol = $https ? 'https' : 'http';
echo "Protocol: {$protocol}<br>\n";

// Full current URL
$currentUrl = "{$protocol}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
echo "URL: {$currentUrl}<br>\n";

// Document root
echo "Document Root: {$_SERVER['DOCUMENT_ROOT']}<br>\n";

// Server IP and port
echo "Server: {$_SERVER['SERVER_ADDR']}:{$_SERVER['SERVER_PORT']}<br>\n";

// Path and filename of the current script
echo "Script: {$_SERVER['SCRIPT_FILENAME']}<br>\n";
echo "Script name: {$_SERVER['SCRIPT_NAME']}<br>\n";
