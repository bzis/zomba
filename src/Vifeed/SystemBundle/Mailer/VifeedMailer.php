<?php

namespace Vifeed\SystemBundle\Mailer;

/**
 * Class VifeedMailer
 *
 * @package Vifeed\SystemBundle\Mailer
 */
class VifeedMailer
{
    private $mailer;
    private $sender;
    private $twig;

    public function __construct(\Swift_Mailer $mailer, $twig, $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->twig = $twig;
    }

    public function renderMessage($templateName, $context)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);

        return $template->render($context);
    }

    public function sendMessage($subject, $body, $to, $replyTo = null, $from = null)
    {
        if ($from === null) {
            $from = $this->sender;
        }
        $message = \Swift_Message::newInstance()
                                 ->setSubject($subject)
                                 ->setFrom($from)
                                 ->setTo($to)
                                 ->setBody($body, 'text/html')
                                 ->setReplyTo($replyTo);

        $this->mailer->send($message);
    }
} 