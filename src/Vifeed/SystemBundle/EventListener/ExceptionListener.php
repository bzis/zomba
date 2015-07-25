<?php

namespace Vifeed\SystemBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseListener;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
 * Class ExceptionListener
 * @package Vifeed\SystemBundle\EventListener
 */
class ExceptionListener extends BaseListener
{
    /**
     * @param \Exception $exception
     * @param string     $message
     * @param bool       $original
     */
    protected function logException(\Exception $exception, $message, $original = true)
    {
        $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        $context = array('exception' => $exception);
        if (null !== $this->logger) {
            if ($isCritical) {
                $this->logger->critical($message, $context);
            } else {
                if ((!$exception instanceof NotFoundHttpException) && (!$exception instanceof BadRequestHttpException)) {
                    $this->logger->error($message, $context);
                }
            }
        } elseif (!$original || $isCritical) {
            error_log($message);
        }
    }
} 
