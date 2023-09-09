<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Token;
use App\Generators\TokenGenerator;
use DateTime;

class TokenFactory
{
    private TokenGenerator $tokenGenerator;

    public function __construct(TokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function create(DateTime $now): Token
    {
        return new Token(
            $this->tokenGenerator->generateToken(),
            $now
        );
    }
}
