<?php

// === 18 — Basic Autoloading (spl_autoload_register and PSR-4 with Composer) ===

// Basic autoloading configuration
spl_autoload_register(function (string $class): void {
    // Converts namespace to file path
    // Ex: App\Models\User -> src/Models/User.php
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Now any class in the App namespace will be loaded:
// use App\Models\User;
// use App\Services\EmailService;

// ---

// Composer autoloading (PSR-4)
// composer.json:
/*
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
*/

// At the application entry point:
// require_once __DIR__ . '/vendor/autoload.php';
//
// use App\Controllers\HomeController;
// use App\Models\User;
//
// $controller = new HomeController();  // loaded
// $user     = new User();              // loaded
