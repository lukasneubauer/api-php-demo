<?php

declare(strict_types=1);

namespace Tests\App\Emails;

use App\Emails\AccountActivationEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mime\Email;

class AccountActivationEmailFunctionalTest extends KernelTestCase
{
    public function testSend(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var AccountActivationEmail $accountActivationEmail */
        $accountActivationEmail = $dic->get(AccountActivationEmail::class);
        $accountActivationEmail->send('john.doe@example.com', 'http://example.com/account-activation');

        $expectedContent = <<<EOL
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrace</title>
</head>
<body>

<p>Dobrý den,</p>

<p>děkujeme za registraci na webu <a href="http://example.com">example.com</a>.</p>

<p>Váš účet prozatím není aktivní. Jeho aktivaci můžete provést kliknutím na následující odkaz: <a href="http://example.com/account-activation">http://example.com/account-activation</a>.</p>

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
