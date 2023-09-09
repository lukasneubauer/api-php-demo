<?php

declare(strict_types=1);

namespace App\Passwords;

class PasswordAlgorithms
{
    /** @var string */
    const BCRYPT = 'bcrypt';

    /** @var string */
    const ARGON2I = 'argon2i';

    /** @var string */
    const ARGON2ID = 'argon2id';
}
