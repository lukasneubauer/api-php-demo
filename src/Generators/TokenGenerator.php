<?php

declare(strict_types=1);

namespace App\Generators;

use App\Entities\Token;
use Nette\Utils\Random;

class TokenGenerator
{
    public function generateToken(): string
    {
        return Random::generate(Token::LENGTH, Token::PATTERN);
    }
}
