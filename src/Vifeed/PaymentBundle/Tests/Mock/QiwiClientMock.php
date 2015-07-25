<?php

namespace Vifeed\PaymentBundle\Tests\Mock;

use Werkint\Qiwi\Client;
use Werkint\Qiwi\Status\StatusResult;

/**
 * Class QiwiClientMock
 * @package Vifeed\PaymentBundle\Tests\Mock
 */
class QiwiClientMock extends Client
{
    public function createBill(
          $phone, $amount, $txn_id, $comment,
          \DateTime $lifetime = null, $alarm = false, $create = true
    ) {
        return new StatusResult(0);
    }
} 