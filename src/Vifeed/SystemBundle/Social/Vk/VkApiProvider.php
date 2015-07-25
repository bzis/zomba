<?php

namespace Vifeed\SystemBundle\Social\Vk;

/**
 * Class VkApiProvider
 *
 * @package Vifeed\SystemBundle\Social\Vk
 */
class VkApiProvider
{
    private $vk;

    /**
     *
     */
    public function __construct(VkApi $vkApi)
    {
        $this->vk = $vkApi;
    }

    /**
     *
     */
    public function getLikeCount($url, $shares = false)
    {
        $parameters = [
              'type'     => 'site',
              'owner_id' => $this->vk->getAppId(),
              'page_url' => $url,
              'count'    => 1
        ];
        if ($shares) {
            $parameters['filter'] = 'copies';
        }
        $response = $this->vk->api('likes.getList', $parameters);

        if (isset($response['response']) && isset($response['response']['count'])) {
            return $response['response']['count'];
        } else {
            if (isset($response['error'])) {
                // todo залогать ошибку
            }
            return 0;
        }


    }
} 