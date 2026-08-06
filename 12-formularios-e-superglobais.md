# Module 12: Forms and Superglobals

## Overview

Forms are the primary way users interact with web applications. PHP offers superglobals like `$_GET`, `$_POST`, and `$_REQUEST` to capture data sent by the client. In this module, you'll learn to create forms, validate data, sanitize inputs, and protect your application.

---

## 1. Superglobals: Overview

PHP provides **superglobal** variables — accessible in any scope (functions, classes, global scope) without needing `global $variable`.

| Superglobal | Description |
|-------------|-------------|
| `$_GET`     | Parameters via URL query string |
| `$_POST`    | Data sent via POST method (request body) |
| `$_REQUEST` | Combination of `$_GET`, `$_POST`, and `$_COOKIE` (order defined by `request_order`) |
| `$_SERVER`  | Server and HTTP request information |
| `$_ENV`     | Environment variables |
| `$_FILES`   | Files sent via upload |
| `$_COOKIE`  | Cookies sent by the client |
| `$_SESSION` | Session data (requires `session_start()`) |
| `$GLOBALS`  | All variables in the global scope |

---

## 2. `$_GET` — URL Parameters

```php
<?php
// URL: http://localhost/page.php?name=John&age=28&city=São+Paulo

echo "Name: " . ($_GET['name'] ?? 'Not provided') . "<br>\n";
echo "Age: " . ($_GET['age'] ?? 'Not provided') . "<br>\n";
echo "City: " . ($_GET['city'] ?? 'Not provided') . "<br>\n";

// Iterate over all GET parameters
foreach ($_GET as $key => $value) {
    echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>\n";
}
```

> **Warning:** Data in `$_GET` is visible in the URL. Never send passwords or sensitive data via GET.

### Search with GET Form

```html
<!-- search.php -->
<form method="get" action="search.php">
    <label for="q">Search:</label>
    <input type="text" name="q" id="q"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Search</button>
</form>

<?php
if (!empty($_GET['q'])) {
    $searchTerm = htmlspecialchars($_GET['q']);
    echo "<p>You searched for: <strong>{$searchTerm}</strong></p>\n";
}
?>
```

---

## 3. `$_POST` — Data in the Request Body

```html
<!-- register.html -->
<form method="post" action="register.php">
    <label>Name:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required minlength="8">

    <button type="submit">Register</button>
</form>
```

```php
<?php
// register.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$name     = $_POST['name']     ?? '';
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
$errors = [];

if (trim($name) === '') {
    $errors[] = 'The name is required.';
}

if (trim($email) === '') {
    $errors[] = 'The email is required.';
}

if (strlen($password) < 8) {
    $errors[] = 'The password must be at least 8 characters.';
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p style='color:red'>{$error}</p>\n";
    }
    exit;
}

echo "<p>Registration successful!</p>\n";
echo "<p>Welcome, " . htmlspecialchars($name) . "!</p>\n";
```

---

## 4. `$_REQUEST` — Warnings

`$_REQUEST` contains data from `$_GET`, `$_POST`, and `$_COOKIE`, in the order defined by the `request_order` directive (default: `GP` — GET then POST).

```php
<?php
// Avoid using $_REQUEST in production — it's unclear where the data comes from.
$searchTerm = $_REQUEST['searchTerm'] ?? '';

// Prefer being explicit:
$searchTerm = $_GET['searchTerm'] ?? $_POST['searchTerm'] ?? '';
```

> **Warning:** `$_REQUEST` can be altered by cookies, which may cause unexpected behavior. In production code, always access the specific superglobal.

---

## 5. `$_SERVER` — Request Information

```php
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
```

### Useful `$_SERVER` Keys

| Key | Description | Example |
|-----|-------------|---------|
| `REQUEST_METHOD` | HTTP method | `GET`, `POST` |
| `REQUEST_URI` | Request URI | `/page.php?id=1` |
| `HTTP_HOST` | Hostname | `localhost:8080` |
| `REMOTE_ADDR` | Client IP | `192.168.1.10` |
| `HTTP_REFERER` | Origin page | `https://google.com` |
| `DOCUMENT_ROOT` | Site root | `/var/www/html` |
| `CONTENT_TYPE` | Content type | `application/json` |

---

## 6. `$_ENV` and `$GLOBALS`

### `$_ENV` — Environment Variables

```php
<?php
// Defined in .env, docker-compose, or the operating system
// Example: export APP_DEBUG=true && php script.php

$debugMode = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
if ($debugMode) {
    echo "Debug mode active<br>\n";
}

// Database password NEVER in the code
$dbPassword = $_ENV['DB_PASSWORD'] ?? '';
```

> **Tip:** Use libraries like `vlucas/phpdotenv` to load variables from a `.env` file. Never commit `.env` — add it to `.gitignore`.

### `$GLOBALS` — All global scope variables

```php
<?php
$config = ['app' => 'MyApp', 'version' => '3.0'];
$dbHost = 'localhost';

// $GLOBALS contains ALL global variables
echo $GLOBALS['config']['app']; // MyApp
echo $GLOBALS['dbHost'];        // localhost

// It also contains superglobals
print_r($GLOBALS['_SERVER']);
```

---

## 7. GET vs POST: HTTP Semantics

| Characteristic | GET | POST |
|----------------|-----|------|
| **Visibility** | Data in the URL | Data in the body |
| **Cache** | Cacheable | Not cacheable |
| **History** | Stays in history | Does not stay |
| **Size** | Limited (~2048 chars in URL) | No practical limit (server-defined) |
| **Idempotent** | Yes | No |
| **Typical use** | Search, filters, pagination | Registration, login, data submission |

```php
<?php
// GET: search, list, filter — does not change state
// POST: create, update, delete — changes state

// Example of method-based routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && $path === '/users') {
    // List users
} elseif ($method === 'POST' && $path === '/users') {
    // Create user
}
```

---

## 8. Creating HTML Forms with PHP

### Self-submitting Form

```php
<?php
// contact.php — processes the form on the same page
$sent = false;
$errors = [];
$name = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = 'The name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email.';
    }

    if (strlen($message) < 10) {
        $errors[] = 'The message must be at least 10 characters.';
    }

    if (empty($errors)) {
        $sent = true;
        // Here you'd save to the database or send an email
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; }
        .error { color: red; }
        .success { color: green; }
        label { display: block; margin-top: 12px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Contact</h1>

    <?php if ($sent): ?>
        <p class="success">Message sent successfully! Thank you, <?= htmlspecialchars($name) ?>.</p>
    <?php else: ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="contact.php" novalidate>
            <label for="name">Name</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($name) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email) ?>">

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5"><?= htmlspecialchars($message) ?></textarea>

            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</body>
</html>
```

---

## 9. Filters: `filter_var()` and `filter_input()`

PHP provides the **filter** extension to validate and sanitize data consistently.

### Validation with `filter_var()`

```php
<?php
// Common validations
$email   = 'test@example.com';
$url     = 'https://www.php.net';
$ip      = '192.168.0.1';
$integer = '42';
$boolean = 'yes';

var_dump(filter_var($email, FILTER_VALIDATE_EMAIL));      // 'test@example.com' (valid)
var_dump(filter_var('invalid-email', FILTER_VALIDATE_EMAIL)); // false
var_dump(filter_var($url, FILTER_VALIDATE_URL));          // 'https://www.php.net'
var_dump(filter_var($ip, FILTER_VALIDATE_IP));            // '192.168.0.1'
var_dump(filter_var($integer, FILTER_VALIDATE_INT));      // 42
var_dump(filter_var('42.5', FILTER_VALIDATE_INT));        // false

// Validate number with range
$age = 25;
var_dump(filter_var($age, FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 0,
        'max_range' => 150,
    ]
])); // 25

// Validate with flags
var_dump(filter_var($boolean, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE));
// true ('yes' counts as true)

// Values considered true: '1', 'true', 'on', 'yes'
// Values considered false: '0', 'false', 'off', 'no'
```

### Sanitization with `filter_var()`

```php
<?php
// Sanitization — cleans the value by removing unwanted characters

$email  = 'john@@example.com<script>';
$string = '<h1>Hello!</h1><script>alert("xss")</script>';
$url    = 'https://example.com/<script>';
$number = '+55 (11) 99999-8888';

// Remove invalid email characters
echo filter_var($email, FILTER_SANITIZE_EMAIL);
// john@@example.comscript — removed <> but kept Unicode characters

// Remove HTML tags
echo filter_var($string, FILTER_SANITIZE_STRING);
// Hello!alert("xss") — removed the tags

// Sanitize URL removing invalid characters
echo filter_var($url, FILTER_SANITIZE_URL);
// https://example.com/script

// Remove everything except digits, +, and -
echo filter_var($number, FILTER_SANITIZE_NUMBER_INT);
// +5511999998888

// Remove everything except digits and float characters (.,e,E,+,-)
echo filter_var('$ 1,299.90', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
// 1,299.90
```

### `filter_input()` — Validate from Superglobals

```php
<?php
// URL: http://localhost/page.php?id=42&email=test@example.com&color=%23ff0000

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    die('Invalid ID.');
}

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);

$color = filter_input(INPUT_GET, 'color', FILTER_SANITIZE_STRING);

// filter_input_array — validate multiple fields at once
$filters = [
    'name'  => FILTER_SANITIZE_STRING,
    'email' => FILTER_VALIDATE_EMAIL,
    'age' => [
        'filter'  => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0, 'max_range' => 150],
    ],
];

$data = filter_input_array(INPUT_POST, $filters);

foreach ($data as $field => $value) {
    if ($value === false || $value === null) {
        echo "Field '{$field}' is invalid.<br>\n";
    }
}
```

### Useful Filters Table

| Validation Filter | Sanitization Filter |
|-------------------|---------------------|
| `FILTER_VALIDATE_BOOL` | `FILTER_SANITIZE_STRING` |
| `FILTER_VALIDATE_DOMAIN` (PHP 7+) | `FILTER_SANITIZE_EMAIL` |
| `FILTER_VALIDATE_EMAIL` | `FILTER_SANITIZE_URL` |
| `FILTER_VALIDATE_FLOAT` | `FILTER_SANITIZE_NUMBER_INT` |
| `FILTER_VALIDATE_INT` | `FILTER_SANITIZE_NUMBER_FLOAT` |
| `FILTER_VALIDATE_IP` | `FILTER_SANITIZE_ENCODED` |
| `FILTER_VALIDATE_MAC` | `FILTER_SANITIZE_SPECIAL_CHARS` |
| `FILTER_VALIDATE_REGEXP` | |
| `FILTER_VALIDATE_URL` | |

---

## 10. Validation and Sanitization in Practice

```php
<?php
class FormValidator {
    private array $errors = [];

    public function validate(array $data, array $rules): array {
        $cleanData = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            // Required field
            if (in_array('required', $fieldRules) && trim((string) $value) === '') {
                $this->errors[$field] = "The field '{$field}' is required.";
                continue;
            }

            // Default sanitization: strip tags and extra spaces
            $value = trim(strip_tags((string) $value));

            // Email
            if (in_array('email', $fieldRules) && $value !== '') {
                $validatedEmail = filter_var($value, FILTER_VALIDATE_EMAIL);
                if ($validatedEmail === false) {
                    $this->errors[$field] = "Invalid email.";
                } else {
                    $value = $validatedEmail;
                }
            }

            // Integer
            if (in_array('int', $fieldRules)) {
                $int = filter_var($value, FILTER_VALIDATE_INT);
                if ($int === false) {
                    $this->errors[$field] = "Must be an integer.";
                } else {
                    $value = $int;
                }
            }

            // Minimum length
            foreach ($fieldRules as $rule) {
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen((string) $value) < $min) {
                        $this->errors[$field] = "Must be at least {$min} characters.";
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen((string) $value) > $max) {
                        $this->errors[$field] = "Must be at most {$max} characters.";
                    }
                }
            }

            $cleanData[$field] = $value;
        }

        return $cleanData;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

// Usage
$validator = new FormValidator();
$rules = [
    'name'  => ['required', 'min:3', 'max:100'],
    'email' => ['required', 'email'],
    'age'   => ['int', 'min:0', 'max:150'],
];
$data = $validator->validate($_POST, $rules);

if ($validator->hasErrors()) {
    print_r($validator->getErrors());
} else {
    echo "Valid data!";
    print_r($data);
}
```

---

## 11. Basic CSRF (Cross-Site Request Forgery)

```php
<?php
session_start();

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verifyCSRF(): void {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Invalid CSRF token. Request rejected.');
    }
}

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    // Process data securely...
    echo "Action executed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Protected Form</title></head>
<body>
    <form method="post">
        <?= csrfField() ?>
        <label>Name: <input type="text" name="name"></label>
        <button type="submit">Send</button>
    </form>
</body>
</html>
```

> **Tip:** The `hash_equals()` function performs a constant-time comparison, preventing **timing attacks** when comparing tokens.

---

## 12. Redirect with `header()`

```php
<?php
// Simple redirect
header('Location: /destination-page.php');
exit; // ALWAYS call exit/die after header('Location: ...')

// Redirect with HTTP code
header('Location: /new-page.php', true, 301); // 301 = permanent
exit;

// Redirect back to previous page
$referrer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
header("Location: {$referrer}");
exit;

// Redirect with flash message (see Module 13 — Sessions)
session_start();
$_SESSION['flash_message'] = 'Operation completed successfully!';
header('Location: /index.php');
exit;
```

> **Warning:** `header()` must be called **before any output** (HTML, echo, whitespace). Otherwise, it will generate a "headers already sent" error.

---

## 13. `$_FILES` for Upload (Summary)

```php
<?php
// See Module 11 for full details

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];

    $tmpName      = $file['tmp_name'];
    $originalName = $file['name'];
    $fileSize     = $file['size'];
    $error        = $file['error'];

    if ($error === UPLOAD_ERR_OK) {
        $destination = __DIR__ . '/uploads/' . basename($originalName);
        move_uploaded_file($tmpName, $destination);
        echo "Upload complete!";
    } else {
        echo "Upload error: code {$error}";
    }
}
```

---

## 14. `request_parse_body()` (PHP 8.4+)

> **PHP 8.4+**

Starting with PHP 8.4, the `request_parse_body()` function can be used to explicitly process the request body, useful in APIs and different SAPIs (CGI, FastCGI, etc.).

```php
<?php
// PHP 8.4+
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$postData, $fileData] = request_parse_body();

    // $postData contains form fields (equivalent to $_POST)
    // $fileData contains uploaded files (equivalent to $_FILES)

    $name = $postData['name'] ?? '';

    if (isset($fileData['document'])) {
        $file = $fileData['document'];
        move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $file['name']);
    }
}
```

> **Tip:** `request_parse_body()` is useful when running PHP in CLI mode or on CGI servers where `$_POST` might not be available.

---

## 15. Complete Example: Newsletter with Validation and CSRF

```php
<?php
// newsletter.php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    // Validation
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $errors[] = 'Please provide a valid email.';
    }

    // Check if already subscribed (simulated with file)
    $subscribersFile = __DIR__ . '/newsletter.txt';
    $subscribers = file_exists($subscribersFile)
        ? file($subscribersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if (in_array($email, $subscribers)) {
        $errors[] = 'This email is already subscribed.';
    }

    if (empty($errors)) {
        file_put_contents($subscribersFile, $email . "\n", FILE_APPEND | LOCK_EX);
        $success = true;
        $email = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; display: flex;
               justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 400px; width: 100%; }
        h2 { margin-bottom: 0.5rem; }
        p.description { color: #666; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        input[type="email"] { width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 4px;
                              font-size: 1rem; }
        button { width: 100%; padding: 0.7rem; background: #2563eb; color: white; border: none;
                 border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .success { color: #16a34a; font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="card">
    <?php if ($success): ?>
        <h2>Thanks for subscribing!</h2>
        <p class="success">You'll receive our news shortly.</p>
    <?php else: ?>
        <h2>Subscribe to our Newsletter</h2>
        <p class="description">Get PHP tips straight to your inbox. No spam.</p>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="field">
                <label for="email">Your best email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>"
                       placeholder="example@email.com" required>
            </div>
            <button type="submit">Subscribe</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
```

---

## Navigation

- [← Module 11: File Handling](./11-manipulacao-de-arquivos.md)
- [→ Module 13: Sessions and Cookies](./13-sessoes-e-cookies.md)

---

## References

- [PHP: Predefined Variables (Superglobals)](https://www.php.net/manual/en/reserved.variables.php)
- [PHP: Filters — filter_var](https://www.php.net/manual/en/function.filter-var.php)
- [PHP: Filters — filter_input](https://www.php.net/manual/en/function.filter-input.php)
- [PHP: header()](https://www.php.net/manual/en/function.header.php)
- [PHP: File Upload](https://www.php.net/manual/en/features.file-upload.php)
- [PHP: request_parse_body (8.4+)](https://www.php.net/manual/en/function.request-parse-body.php)
- [OWASP: CSRF](https://owasp.org/www-community/attacks/csrf)
- [PHP: hash_equals](https://www.php.net/manual/en/function.hash-equals.php)
