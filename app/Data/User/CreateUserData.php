<?php

namespace App\Data\User;

final readonly class CreateUserData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $name,
    ){}
}
