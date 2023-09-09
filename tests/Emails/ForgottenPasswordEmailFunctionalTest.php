<?php

declare(strict_types=1);

namespace Tests\App\Emails;

use App\Emails\ForgottenPasswordEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mime\Email;

class ForgottenPasswordEmailFunctionalTest extends KernelTestCase
{
    public function testSend(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var ForgottenPasswordEmail $forgottenPasswordEmail */
        $forgottenPasswordEmail = $dic->get(ForgottenPasswordEmail::class);
        $forgottenPasswordEmail->send('john.doe@example.com', 'http://example.com/forgotten-password');

        $expectedContent = <<<EOL
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vyžádání nového hesla</title>
</head>
<body>

<p>Dobrý den,</p>

<p>obdrželi jsme od Vás požadavek na obnovu zapomenutého hesla.</p>

<p>Kliknutím na odkaz níže budete přesměřováni na stránku, na které budete mít možnost zadat si heslo zcela nové.</p>

<p><strong>Pokud jste o obnovu hesla nepožádali, prosím ignorujte tento e-mail.</strong></p>

<p><a href="http://example.com/forgotten-password">http://example.com/forgotten-password</a></p>

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
