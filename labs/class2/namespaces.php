<?php

namespace App\Models;

class User
{
    public function __construct(
        public string $name
    ) {}
}

// Full name: \App\Models\User

namespace Project\Module\Submodule;

// Full name: \Project\Module\Submodule\MyClass
class MyClass
{
}
