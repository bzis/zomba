<?php

namespace Vifeed\UserBundle\Manager;

use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * Class WsseTokenManager
 *
 * @package Vifeed\UserBundle\Manager
 */
class WsseTokenManager
{
    const PREFIX = 'wsse.token';
    const TTL = 3000;

    /** @var \Predis\Client|\Redis */
    private $redis;

    /**
     * @param \Predis\Client|\Redis $redisClient
     */
    public function __construct($redisClient)
    {
        $this->redis = $redisClient;
    }

    /**
     * @param int $userId
     *
     * @return string
     */
    public function createUserToken($userId)
    {
        $generator = new SecureRandom();
        $rand = $generator->nextBytes(12);
        $wsseToken = sha1($rand);

        $this->redis->setex(self::PREFIX . ':' . $userId, self::TTL, $wsseToken);

        return $wsseToken;
    }

    /**
     * @param int    $userId
     * @param string $token
     *
     * @return bool
     */
    public function isTokenValid($userId, $token)
    {
        return $token === $this->getUserToken($userId);
    }

    /**
     * @param int $userId
     *
     * @return bool|string
     */
    public function getUserToken($userId)
    {
        return $this->redis->get(self::PREFIX . ':' . $userId);
    }

    /**
     * @param int $userId
     */
    public function deleteUserToken($userId)
    {
        $this->redis->del(self::PREFIX . ':' . $userId);
    }


}
 