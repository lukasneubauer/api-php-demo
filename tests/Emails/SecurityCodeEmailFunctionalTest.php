<?php

declare(strict_types=1);

namespace Tests\App\Emails;

use App\Emails\SecurityCodeEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mime\Email;

class SecurityCodeEmailFunctionalTest extends KernelTestCase
{
    public function testSend(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var SecurityCodeEmail $securityCodeEmail */
        $securityCodeEmail = $dic->get(SecurityCodeEmail::class);
        $securityCodeEmail->send('john.doe@example.com', 'H597MR1Y0');

        $expectedContent = <<<EOL
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bezpečnostní kód</title>
</head>
<body>

<p>Dobrý den,</p>

<p>zde je Váš bezpečnostní kód</p>

<p>H597MR1Y0</p>

<br>

<p>S pozdravem,</p>

<p>XYZ</p>

</body>
</html>

EOL;

        /** @var Email $emailMessage */
        $emailMessage = self::getMailerMessage(0, 'null://');
        $email = $emailMessage->getHtmlBody();
        $this->assertSame($expectedContent, $email);
    }
}
