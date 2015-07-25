<?php

namespace Vifeed\SystemBundle\Social\Facebook;

use Facebook\FacebookRequest;
use Facebook\FacebookSession;

/**
 * Class ApiProvider
 * @package Vifeed\SystemBundle\Social\Facebook
 */
class FacebookApiProvider
{
    private $appId;
    private $appSecret;
    private $session;

    /**
     *
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
    }

    /**
     *
     */
    public function getUrlStats($urls)
    {
        if (!is_array($urls)) {
            $urls = [$urls];
        }

        $query = '/fql?q=select%20like_count,%20comment_count,%20share_count%20from%20link_stat%20where%20url IN ("' .
              join('","', $urls) . '")';
        $request = new FacebookRequest($this->getSession(), 'GET', $query);
        $response = json_decode($request->execute()->getRawResponse(), true);

        if (sizeof($response['data'] !== sizeof($urls))) {
            // todo как-то залогировать
        }

        return $response['data'];
    }

    /**
     * @return FacebookSession
     */
    protected function getSession()
    {
        if (!$this->session) {
            $this->session = FacebookSession::newAppSession();
        }

        return $this->session;
    }


} 