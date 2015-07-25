<?php

namespace Vifeed\SystemBundle\Social\Google;

use Guzzle\Http\Client as Guzzle;

/**
 * Class GooglePlusApiProvider
 * @package Vifeed\SystemBundle\Social\Google
 */
class GooglePlusApiProvider
{
    protected $guzzle;

    /**
     *
     */
    public function __construct()
    {
        $this->guzzle = new Guzzle('https://clients6.google.com/rpc');
    }

    /**
     *
     */
    public function getSharesCount($url)
    {
        $req = $this->guzzle->post('', ['Content-type: application/json'],
                             '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]')
                      ->send()
                      ->json();

        if (isset($req[0]['result']['metadata']['globalCounts']['count'])) {
            return (int) $req[0]['result']['metadata']['globalCounts']['count'];
        }

        return 0;
    }
} 