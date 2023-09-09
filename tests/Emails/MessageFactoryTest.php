<?php

declare(strict_types=1);

namespace Tests\App\Emails;

use App\Emails\MessageFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Email;

class MessageFactoryTest extends TestCase
{
    public function testCreateEmailMessage(): void
    {
        $emailMessage = (new MessageFactory())->createMessage();
        $this->assertInstanceOf(Email::class, $emailMessage);
    }
}
