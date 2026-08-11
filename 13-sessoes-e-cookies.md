# Module 13: Sessions and Cookies

## Overview

HTTP is a **stateless** protocol. Each request is independent. Sessions and cookies allow the server to recognize users across requests. Sessions store data on the server; cookies store data in the client's browser.

---

## 1. Sessions: `session_start()`

`session_start()` must be called **before any HTML output**, echo, print, or even whitespace outside of `<?php ?>`.

```php
<?php
// ALWAYS at the top of the file, before any HTML
session_start();

// Now $_SESSION is available
$_SESSION['user_id'] = 42;
$_SESSION['name'] = 'John Doe';
$_SESSION['logged_in_at'] = time();

echo "Session started for {$_SESSION['name']}<br>\n";
```

> **Warning:** If there is any output before `session_start()`, PHP will emit: `Warning: session_start(): Cannot start session when headers already sent`.

---

## 2. `$_SESSION`: Storing and Retrieving Data

```php
<?php
session_start();

// Storing data
$_SESSION['user'] = [
    'id'    => 1,
    'name'  => 'John',
    'email' => 'john@email.com',
    'role'  => 'admin',
];

// Store preferences
$_SESSION['theme'] = 'dark';
$_SESSION['cart'] = [
    ['product_id' => 10, 'quantity' => 2],
    ['product_id' => 15, 'quantity' => 1],
];

// Retrieving data
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    echo "Welcome, {$user['name']}!<br>\n";
    echo "Role: {$user['role']}<br>\n";
}

// Session operations
$totalItems = count($_SESSION['cart']);

// Remove a specific item
unset($_SESSION['cart'][0]);

// Add to cart
$_SESSION['cart'][] = ['product_id' => 20, 'quantity' => 3];
```

### Check if session is active

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Or
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session active<br>\n";
}
```

---

## 3. `session_unset()` and `session_destroy()`

```php
<?php
session_start();

// session_unset() — clears all session variables
// but keeps the session active
session_unset();
echo "Variables cleared. Session remains active.<br>\n";

// session_destroy() — destroys the session on the server
// The session cookie still exists in the browser!
$_SESSION = []; // clear the array

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // expires in the past
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
echo "Session destroyed.<br>\n";
```

### Complete logout (recipe)

```php
<?php
// logout.php
session_start();

// 1. Clear session data
$_SESSION = [];

// 2. Remove the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to login page
header('Location: /login.php');
exit;
```

---

## 4. `session_regenerate_id()`

Regenerate the session ID after login to prevent **session fixation**.

```php
<?php
session_start();

// After successful login
$_SESSION['user_id'] = $user['id'];

// Regenerate the ID — important for security!
// true: removes the old session file
session_regenerate_id(true);

echo "Login successful. Session ID regenerated.<br>\n";
```

> **Tip:** Always call `session_regenerate_id(true)` after login, logout, and user permission changes.

---

## 5. Session Configuration

```php
<?php
// Configure before session_start()

// Session lifetime on the server (in seconds)
// 3600 = 1 hour, 86400 = 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours

// Session cookies
ini_set('session.cookie_lifetime', 0);      // 0 = until browser closes
ini_set('session.cookie_path', '/');         // available site-wide
ini_set('session.cookie_domain', '');        // current domain
ini_set('session.cookie_secure', '1');       // HTTPS only
ini_set('session.cookie_httponly', '1');     // inaccessible via JavaScript
ini_set('session.cookie_samesite', 'Lax');   // CSRF protection

// Session cookie name (change from default PHPSESSID)
session_name('MYAPP_SESSID');

// Directory where session files are saved
// session.save_path — cannot be changed via ini_set in production
// Configure in php.ini

// Garbage collection probability
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100); // 1% chance per request

session_start();
```

### Configuration via `session_set_cookie_params()`

```php
<?php
// Cleaner alternative to ini_set
session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'domain'   => 'mysite.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();
```

### Typical configuration in `php.ini`

```ini
session.save_handler = files
session.save_path = "/tmp"
session.gc_maxlifetime = 1440          ; 24 minutes (default)
session.cookie_lifetime = 0            ; expires when browser closes
session.cookie_httponly = On           ; inaccessible via JS
session.cookie_secure = On             ; HTTPS only
session.cookie_samesite = "Lax"        ; CSRF
session.use_strict_mode = On           ; rejects uninitialized IDs
session.use_only_cookies = On          ; disallows ID in URL
```

---

## 6. Cookies: `setcookie()`

Cookies store data in the client's browser. They are sent with every subsequent HTTP request to the same domain.

```php
<?php
// setcookie(name, value, expires, path, domain, secure, httponly, samesite)

// Simple cookie
setcookie('theme', 'dark');

// Cookie with expiration time
// time() + seconds
setcookie('remember_login', 'yes', time() + (86400 * 30)); // 30 days
setcookie('locale', 'en-US', time() + (86400 * 365)); // 1 year

// Cookie with specific path (only available in /admin)
setcookie('admin_token', 'abc123', time() + 3600, '/admin');

// Cookie with all security options
setcookie(
    'token',
    'encoded-value',
    [
        'expires'  => time() + 86400,
        'path'     => '/',
        'domain'   => '',               // current domain
        'secure'   => true,             // HTTPS only
        'httponly' => true,             // inaccessible via JavaScript
        'samesite' => 'Strict',         // Lax, Strict or None
    ]
);
```

> **PHP 8.5+** — New `partitioned` flag

```php
<?php
// PHP 8.5+: Partitioned Cookies (CHIPS — Cookies Having Independent Partitioned State)
// Useful for cookies in third-party iframes
// https://developer.chrome.com/docs/privacy-sandbox/chips/
setcookie(
    'widget_pref',
    'dark',
    [
        'expires'      => time() + 86400 * 30,
        'path'         => '/',
        'secure'       => true,
        'httponly'     => true,
        'samesite'     => 'None',       // Requires None for cross-site
        'partitioned'  => true,         // PHP 8.5+ NEW!
    ]
);
```

### `setrawcookie()` — Cookie without URL-encoding

```php
<?php
// setcookie applies urlencode
setcookie('name', 'John Doe'); // cookie stored as: John+Doe

// setrawcookie does NOT apply urlencode (you are responsible)
setrawcookie('token', rawurlencode('abcd/xyz'));
```

---

## 7. `$_COOKIE` — Reading Cookies

```php
<?php
// Cookies set with setcookie will only be available
// in $_COOKIE on the NEXT request

// Safe read with null coalescing operator
$theme = $_COOKIE['theme'] ?? 'light';
$locale = $_COOKIE['locale'] ?? 'en-US';

// Check existence
if (isset($_COOKIE['remember_login'])) {
    echo "User chose 'remember login'.<br>\n";
}

// List all received cookies
echo "<h3>Received cookies:</h3>\n";
echo "<ul>\n";
foreach ($_COOKIE as $name => $value) {
    echo "<li>" . htmlspecialchars($name) . " = " . htmlspecialchars($value) . "</li>\n";
}
echo "</ul>\n";
```

### Removing a Cookie

```php
<?php
// To remove, set expiration time in the past
setcookie('theme', '', time() - 3600);
setcookie('locale', '', time() - 3600, '/');

// With array options
setcookie('remember_login', '', [
    'expires' => time() - 3600,
    'path'    => '/',
    'secure'  => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
```

---

## 8. Session Cookie vs Persistent Cookie

```php
<?php
// Session cookie: set WITHOUT expires or with lifetime 0
// Disappears when browser closes
setcookie('visited_page', '1', 0);
setcookie('visited_page', '1', ['expires' => 0]);

// Persistent cookie: has a defined expiration time
// Survives browser close
setcookie('remember_user', 'john', time() + (86400 * 30)); // 30 days
```

---

## 9. Flash Messages with Sessions

Flash messages are displayed only once and then removed. Ideal for post-redirect feedback.

```php
<?php
session_start();

// flash.php — Flash message functions

function flash(string $key, string $message = null): ?string {
    if ($message !== null) {
        // SET: stores the message
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    // GET: retrieves and removes
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flashSuccess(string $message): void {
    flash('success', $message);
}

function flashError(string $message): void {
    flash('error', $message);
}

function flashInfo(string $message): void {
    flash('info', $message);
}

// Usage:

// In save.php (after processing the form)
flashSuccess('Record saved successfully!');
header('Location: /list.php');
exit;

// In list.php (in the view)
$success = flash('success');
if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php
$error = flash('error');
if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif;
```

### Complete Flash Messages class

```php
<?php
class FlashMessages {
    private const KEY = '_flash_messages';

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    public static function set(string $type, string $message): void {
        self::init();
        $_SESSION[self::KEY][$type] = $message;
    }

    public static function get(string $type): ?string {
        self::init();
        $msg = $_SESSION[self::KEY][$type] ?? null;
        unset($_SESSION[self::KEY][$type]);
        return $msg;
    }

    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error', $msg); }
    public static function warning(string $msg): void { self::set('warning', $msg); }
    public static function info(string $msg): void    { self::set('info', $msg); }

    public static function all(): array {
        self::init();
        $messages = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];
        return $messages;
    }

    public static function render(): string {
        $html = '';
        foreach (self::all() as $type => $msg) {
            $html .= sprintf(
                '<div class="flash flash-%s">%s</div>',
                htmlspecialchars($type),
                htmlspecialchars($msg)
            );
        }
        return $html;
    }
}

// Usage
FlashMessages::success('File uploaded!');
FlashMessages::error('Connection failed.');
echo FlashMessages::render();
```

---

## 10. Basic Login with Session

```php
<?php
// login.php
session_start();

$error = '';
$email = '';

// Hardcoded users for demo
$users = [
    'admin@email.com' => [
        'name'  => 'Administrator',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'id'    => 1,
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Fill in all fields.';
    } elseif (isset($users[$email])) {
        $user = $users[$email];

        if (password_verify($password, $user['password'])) {
            // Login successful!
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $email,
            ];

            session_regenerate_id(true); // prevents session fixation

            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Incorrect email or password.';
        }
    } else {
        // Use generic message to avoid revealing if the email exists
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login</title>
<style>
    body { font-family: sans-serif; max-width: 380px; margin: 60px auto; }
    .error { background: #fee; color: #c00; padding: 10px; border-radius: 4px; }
    label { display: block; margin: 12px 0 4px; font-weight: 600; }
    input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { margin-top: 16px; padding: 10px 24px; background: #2563eb; color: white;
             border: none; border-radius: 4px; cursor: pointer; }
</style></head>
<body>
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</body>
```

### Protected page (dashboard)

```php
<?php
// dashboard.php
session_start();

// Check if logged in
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>ID: <?= $user['id'] ?></p>
    <a href="/logout.php">Logout</a>
</body>
```

### Helper function to protect pages

```php
<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

// On any protected page:
requireLogin();
// User is logged in, continue...
```

---

## 11. Security: Session Fixation and Session Hijacking

### Session Fixation

The attacker sets a known session ID (e.g., via URL `?PHPSESSID=123`) and tricks the victim into using it. After login, the attacker uses the same ID to access the authenticated session.

**Mitigation:**
```php
<?php
session_start();

// 1. Enable strict mode (php.ini)
// session.use_strict_mode = On

// 2. Regenerate ID after login
session_regenerate_id(true);
```

### Session Hijacking

The attacker steals the victim's session ID (e.g., via XSS, network sniffing).

**Mitigations:**
```php
<?php
// 1. Bind session to IP and User-Agent
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Potentially stolen session — force logout
    session_destroy();
    header('Location: /login.php?error=session');
    exit;
}

// 2. HttpOnly cookies (inaccessible via JavaScript)
ini_set('session.cookie_httponly', '1');

// 3. Secure cookies (HTTPS only)
ini_set('session.cookie_secure', '1');

// 4. SameSite=Strict to prevent CSRF
ini_set('session.cookie_samesite', 'Strict');
```

---

## 12. Practical Example: Shopping Cart with Session

```php
<?php
// cart.php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sample products
$products = [
    1 => ['name' => 'PHP T-Shirt',           'price' => 59.90],
    2 => ['name' => 'Programmer Mug',        'price' => 39.90],
    3 => ['name' => 'PHP Elephant Sticker',  'price' =>  9.90],
    4 => ['name' => 'Modern PHP Book',       'price' => 129.90],
];

// Action: add
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if (isset($products[$id])) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name'  => $products[$id]['name'],
                'price' => $products[$id]['price'],
                'qty'   => 1,
            ];
        }
        $_SESSION['flash'] = "{$products[$id]['name']} added to cart!";
    }
}

// Action: remove
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

// Action: clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

// Calculate total
$total = 0;
$counter = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
    $counter += $item['qty'];
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Shopping Cart</title>
<style>
    body { font-family: sans-serif; max-width: 700px; margin: 30px auto; padding: 0 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    .flash { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
    .btn { display: inline-block; padding: 6px 12px; text-decoration: none; border-radius: 4px;
           color: white; font-size: 0.85rem; }
    .btn-add { background: #2563eb; }
    .btn-remove { background: #dc2626; }
    .btn-clear { background: #6b7280; }
    .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .product { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
    .total { font-size: 1.2rem; font-weight: bold; text-align: right; }
</style></head>
<body>
    <h1>Cart (<?= $counter ?> items)</h1>

    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <h2>Products</h2>
    <div class="products">
        <?php foreach ($products as $id => $p): ?>
            <div class="product">
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                $ <?= number_format($p['price'], 2, '.', ',') ?><br>
                <a href="?add=<?= $id ?>" class="btn btn-add">Add</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
        <h2>Your Cart</h2>
        <table>
            <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>$ <?= number_format($item['price'], 2, '.', ',') ?></td>
                <td><?= $item['qty'] ?></td>
                <td>$ <?= number_format($item['price'] * $item['qty'], 2, '.', ',') ?></td>
                <td><a href="?remove=<?= $id ?>" class="btn btn-remove">X</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: $ <?= number_format($total, 2, '.', ',') ?></p>
        <a href="?clear=1" class="btn btn-clear">Clear Cart</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</body>
```

---

## 13. Cookies with Arrays (Serialization)

```php
<?php
// Cookies store strings. To store arrays, serialize or json_encode.

// Store preferences as JSON
$preferences = ['theme' => 'dark', 'font' => 'large', 'notifications' => false];
setcookie('prefs', json_encode($preferences), time() + (86400 * 365), '/');

$prefs = json_decode($_COOKIE['prefs'] ?? '{}', true);
echo "Theme: " . ($prefs['theme'] ?? 'light') . "<br>\n";

$visits = (int) ($_COOKIE['visits'] ?? 0);
$visits++;
setcookie('visits', (string) $visits, time() + (86400 * 365), '/');
echo "You visited this page {$visits} time(s).<br>\n";
```

> **Warning:** Cookies have a limit of ~4KB per cookie and ~50 cookies per domain. Do not store large data in cookies.

---

## 14. `__serialize()` and `__unserialize()` — Objects in Sessions

When you store an object in `$_SESSION`, PHP serializes it. On the next request, it's unserialized back into an object. **The constructor does NOT run on unserialization.** This means typed properties may be left uninitialized, causing a fatal error:

> `Typed property Class::$prop must not be accessed before initialization`

### The Problem

```php
<?php
class Cart
{
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function getItems(): array
    {
        return $this->items; // ERROR after deserialization!
    }
}

// Request 1
$_SESSION['cart'] = new Cart();

// Request 2 — PHP unserializes $_SESSION['cart']
// Constructor is skipped → $items is uninitialized → BOOM
$cart = $_SESSION['cart'];
$cart->getItems(); // Fatal error
```

### The Solution

Implement `__serialize()` to control what gets saved, and `__unserialize()` to properly restore the object:

```php
<?php
class Cart
{
    private array $items = [];

    public function add(string $name): void
    {
        $this->items[] = $name;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function __serialize(): array
    {
        return ['items' => $this->items];
    }

    public function __unserialize(array $data): void
    {
        $this->items = $data['items'];
    }
}

// Request 1
$_SESSION['cart'] = new Cart();

// Request 2 — __unserialize runs, all properties initialized
$cart = $_SESSION['cart'];
$cart->getItems(); // works!
```

### Handling Legacy Sessions

If you add `__serialize`/`__unserialize` to an existing class, old session data won't have the new keys:

```php
<?php
public function __unserialize(array $data): void
{
    $this->items = $data['items'] ?? [];   // fallback
    $this->total = $data['total'] ?? 0;
}
```

### `__serialize` vs `__sleep` (Legacy)

`__sleep()` and `__wakeup()` are the older counterparts. Prefer `__serialize()` / `__unserialize()`:

```php
<?php
// Legacy — avoid for new code
public function __sleep(): array { return ['items']; }
public function __wakeup(): void  { $this->items = []; }
```

> **Rule of thumb:** Any class with typed properties stored in `$_SESSION` needs `__serialize()` / `__unserialize()`.

---

## Navigation

- [← Module 12: Forms and Superglobals](./12-formularios-e-superglobais.md)
- [→ Module 14: Database](./14-banco-de-dados.md)

---

## References

- [PHP: Sessions](https://www.php.net/manual/en/book.session.php)
- [PHP: session_start](https://www.php.net/manual/en/function.session-start.php)
- [PHP: setcookie](https://www.php.net/manual/en/function.setcookie.php)
- [PHP: session_set_cookie_params](https://www.php.net/manual/en/function.session-set-cookie-params.php)
- [PHP: session_regenerate_id](https://www.php.net/manual/en/function.session-regenerate-id.php)
- [OWASP: Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [MDN: HTTP Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies)
- [MDN: Set-Cookie SameSite](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
