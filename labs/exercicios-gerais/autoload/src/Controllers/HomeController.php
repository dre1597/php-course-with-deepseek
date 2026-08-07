<?php

namespace App\Controllers;

use App\Models\User;

class HomeController
{
    public function index(): string
    {
        return 'Welcome to the Home Page.';
    }

    public function greet(User $user): string
    {
        return "Hello, {$user->name}!";
    }
}
