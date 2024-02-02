<?php

namespace App\CoreBundle\Component;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(
        MailerInterface $mailer
    ) {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $subject,
        string $text,
        string $html
    ): void {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('groshev.andrey17@gmail.com')
            ->subject($subject)
            ->text($text)
            ->html($html);

        $this->mailer->send($email);
    }
}
