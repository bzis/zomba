<?php

namespace Vifeed\SystemBundle\EventListener;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Vifeed\SystemBundle\Exception\WrongDateIntervalException;

class WrongDateIntervalExceptionListener
{

    private $viewHandler;

    public function __construct(ViewHandler $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof WrongDateIntervalException)) {
            return;
        }

        $view = new View($exception->getForm(), 400);
        $response = $this->viewHandler->handle($view);

        $event->setResponse($response);
    }
} 