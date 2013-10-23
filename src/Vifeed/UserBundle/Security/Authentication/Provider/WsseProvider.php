<?php

namespace Vifeed\UserBundle\Security\Authentication\Provider;

use Predis\Client;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Vifeed\UserBundle\Manager\WsseTokenManager;
use Vifeed\UserBundle\Security\Authentication\Token\WsseApiToken;

/**
 * Class WsseProvider
 *
 * @package Vifeed\UserBundle\Security\Authentication\Provider
 */
class WsseProvider implements AuthenticationProviderInterface
{
    const PREFIX = 'wsse.nonce';
    const TTL = 300;

    private $userProvider;

    /** @var \Predis\Client|\Redis */
    private $redis;

    private $tokenManager;

    /**
     * @param UserProviderInterface                       $userProvider
     * @param \Predis\Client|\Redis                       $redisClient
     * @param \Vifeed\UserBundle\Manager\WsseTokenManager $tokenManager
     */
    public function __construct(UserProviderInterface $userProvider, $redisClient, WsseTokenManager $tokenManager)
    {
        $this->userProvider = $userProvider;
        $this->redis = $redisClient;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param TokenInterface $token
     *
     * @return TokenInterface|WsseApiToken
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {
            $wsseToken = $this->tokenManager->getUserToken($user->getId());
            if ($this->validateDigest($token->digest, $token->nonce, $token->created, $wsseToken)) {

                $authenticatedToken = new WsseApiToken($user->getRoles());
                $authenticatedToken->setUser($user);

                return $authenticatedToken;
            }
        }

        throw new AuthenticationException('The WSSE authentication failed.');
    }

    /**
     * @param string $digest
     * @param string $nonce
     * @param string $created
     * @param string $secret
     *
     * @return bool
     * @throws \Symfony\Component\Security\Core\Exception\NonceExpiredException
     */
    protected function validateDigest($digest, $nonce, $created, $secret)
    {
        // Check created time is not in the future
        if (strtotime($created) > time()) {
            return false;
        }

        // Expire timestamp after 5 minutes
        if (time() - strtotime($created) > self::TTL) {
            return false;
        }

        // Validate nonce is unique within 5 minutes
        if ($this->redis->exists(self::PREFIX . ':' . $nonce)) {
            throw new NonceExpiredException('Previously used nonce detected');
        }
        $this->redis->setex(self::PREFIX . ':' . $nonce, self::TTL, time());

        // Validate Secret
        $expected = base64_encode(sha1(base64_decode($nonce) . $created . $secret, true));

        return $digest === $expected;
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseApiToken;
    }
}
