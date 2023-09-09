<?php

declare(strict_types=1);

namespace App\Emails;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as Twig;

class ForgottenPasswordEmail
{
    private MailerInterface $mailer;

    private Email $emailMessage;

    private Twig $twig;

    private string $emailTemplatePath;

    private string $fromEmail;

    private string $subject;

    private string $projectName;

    public function __construct(
        MailerInterface $mailer,
        MessageFactory $messageFactory,
        Translator $translator,
        Twig $twig,
        string $locale,
        string $emailTemplatePath,
        string $fromEmail,
        string $subject,
        string $projectName
    ) {
        $this->mailer = $mailer;
        $this->emailMessage = $messageFactory->createMessage();
        $this->twig = $twig;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->fromEmail = $fromEmail;
        $this->subject = $translator->trans($subject, [], null, $locale);
        $this->projectName = $projectName;
    }

    public function send(string $to, string $link): void
    {
        $parameters = [
            'subject' => $this->subject,
            'link' => $link,
            'project_name' => $this->projectName,
        ];

        $this->emailMessage->subject($this->subject)
            ->from($this->fromEmail)
            ->to($to)
            ->html($this->twig->render($this->emailTemplatePath, $parameters));

        $this->mailer->send($this->emailMessage);
    }
}
