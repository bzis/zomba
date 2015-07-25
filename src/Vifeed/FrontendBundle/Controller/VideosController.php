<?php

namespace Vifeed\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class VideosController extends Controller
{
    public function indexAction($enabledVideos)
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(100800);
        $response->setETag(md5('public'));

        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }

        $videos = [
          'films' => [
            'title' => 'Brick Mansions Promo',
            'before' => '320 700',
            'after' => '743 168',
          ],
          'films2' => [
            'title' => 'Несносный дед. Русский трейлер',
            'before' => '215 521',
            'after' => '629 878',
          ],
          'for_company1' => [
            'title' => 'Как остановить время',
            'before' => '850 932',
            'after' => '1 475 031',
          ],
          'for_company2' => [
            'title' => 'Шашлычок 72 или как защитить ваш офис',
            'before' => '397 000',
            'after' => '813 935',
          ],
          'game1' => [
            'title' => 'Вечеринка компании Wargaming',
            'before' => '12 834',
            'after' => '122 839',
          ],
          'game2' => [
            'title' => 'Pro Игры - Metro: Last Light',
            'before' => '154 000',
            'after' => '487 392',
          ],
          'music2' => [
            'title' => 'NuSkOOl — Lambretta',
            'before' => '1 629',
            'after' => '39 991',
          ],
          'music1' => [
            'title' => 'LITTLE BIG - Everyday I\'m drinking',
            'before' => '1 789 013',
            'after' => '3 610 259',
          ]
        ];


        foreach ($videos as $key => $video) {
            if (!in_array($key, $enabledVideos)) {
                unset($videos[$key]);
            }
        };

        return $this->render(
            'VifeedFrontendBundle:Public:videos.html.twig',
            ['videos' => $videos],
            $response
        );
    }
}
