<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\EmailService;
use App\Util\Validation as Val;

class UserController
{
    public function register(string $name, string $email): User
    {
        Val::email($email);

        $user = new User($name);
        $emailService = new EmailService();
        $emailService->sendWelcome($email);

        return $user;
    }
}
