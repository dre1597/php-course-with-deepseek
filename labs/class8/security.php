<?php

$input = '<script>alert("XSS")</script>';

// Without escape (DANGEROUS!):
echo $input;
// <script>alert("XSS")</script> — would execute in the browser

// With escape (SAFE):
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
// &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt; — displayed as text

$data = "O'Brian said: \"Hello\"";

echo htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
// O'Brian said: &quot;Hello&quot;

echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
// O&#039;Brian said: &quot;Hello&quot;

// Always use ENT_QUOTES for maximum security

$text = 'Action & Heart';

echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
// Action & Heart (accented characters preserved)

echo htmlentities($text, ENT_QUOTES, 'UTF-8');
// Action &amp; Heart (no entities — these are ASCII-safe chars)

$html = '<p>This is a <strong>text</strong> with a <a href="#">link</a>.</p>';

// Remove all tags
echo strip_tags($html);
// This is a text with a link.

// Allow specific tags
echo strip_tags($html, '<strong><em>');
// This is a <strong>text</strong> with a link.

/**
 * Escapes a value for safe HTML output.
 * If the value is null, returns an empty string.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// In templates:
$user = ['name' => '<b>John</b>', 'bio' => 'Dev & "ethical" hacker'];
echo '<h1>' . e($user['name']) . '</h1>';
echo '<p>' . e($user['bio']) . '</p>';
// <h1>&lt;b&gt;John&lt;/b&gt;</h1>
// <p>Dev &amp; &quot;ethical&quot; hacker</p>
