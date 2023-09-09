<?php

declare(strict_types=1);

namespace App\Emails;

use Symfony\Component\Mime\Email;

class MessageFactory
{
    public function createMessage(): Email
    {
        return new Email();
    }
}
