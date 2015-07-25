<?php

namespace Vifeed\BankPaymentBundle\Exception\Action;

use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Vifeed\PaymentBundle\Entity\Order;

/**
 * Class DownloadFile
 * @package Vifeed\PaymentBundle\Plugin\Exception\Action
 */
class DownloadFile extends VisitUrl
{

} 