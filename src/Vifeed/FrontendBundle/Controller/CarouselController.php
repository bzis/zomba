<?php

namespace Vifeed\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CarouselController extends Controller
{
    public function indexAction($enabledSlides)
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(100800);
        $response->setETag(md5('public'));

        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }

        $slides = [
          'main' => [
            'type' => 'advertiser',
            'title' => 'Как это работает',
            'text' => 'Zombakka — это простая и быстрая платформа<br>для продвижения ваших видео.',
            'btnTitle' => 'Узнать больше'
          ],
          'music' => [
            'type' => 'advertiser',
            'title' => 'Для музыкантов',
            'text' => 'Сервис продвижения видео<br> для независимых исполнителей.',
            'btnUrl' => $this->generateUrl('for_musicians'),
            'btnTitle' => 'Запустить кампанию'
          ],
          'partners' => [
            'type' => 'advertiser',
            'title' => 'Для партнеров',
            'text' => 'Специальные условия для<br> рекламных агентств и партнеров.',
            'btnUrl' => $this->generateUrl('for_partners'),
            'btnTitle' => 'Узнать больше'
          ],
          'movies' => [
            'type' => 'advertiser',
            'title' => 'Для фильмов',
            'text' => 'Сервис для продвижения фильмов от киностудий<br> или независимых режиссеров.',
            'btnUrl' => $this->generateUrl('for_movies'),
            'btnTitle' => 'Запустить кампанию'
          ],
          'games' => [
            'type' => 'advertiser',
            'title' => 'Для игр',
            'text' => 'Сервис для продвижения игр от ведущих<br> издателей или инди разработчиков.',
            'btnUrl' => $this->generateUrl('for_games'),
            'btnTitle' => 'Запустить кампанию'
          ],
          'companies' => [
            'type' => 'advertiser',
            'title' => 'Для компаний',
            'text' => 'Сервис для продвижения брендов<br> вирусными технологиями.',
            'btnUrl' => $this->generateUrl('for_companies'),
            'btnTitle' => 'Запустить кампанию'
          ],
          'publishers' => [
            'type' => 'publisher',
            'title' => 'Для владельцев площадок',
            'text' => 'Сервис для безграничного заработка<br> на просмотрах видео, размещенных на ваших площадках.',
            'btnUrl' => $this->generateUrl('for_publishers'),
            'btnTitle' => 'Начать зарабатывать'
          ]
        ];

        foreach ($slides as $key => $slide) {
            if (!in_array($key, $enabledSlides)) {
                unset($slides[$key]);
            }
        };

        return $this->render('VifeedFrontendBundle:Public:carousel.html.twig', ['slides' => $slides], $response);
    }
}
