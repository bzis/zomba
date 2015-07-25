<?php

namespace Vifeed\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $response->setETag(md5((string) $this->getUser()->getType()));
            switch ($this->getUser()->getType()) {
                case 'publisher':
                    $homepage = 'Publisher';
                    break;
                case 'advertiser':
                    $homepage = 'Advertiser';
                    break;
            }
            $response->setMaxAge(60 * 30);
        } else {
            $response->setETag(md5('public'));
            $homepage = 'Public';
            $response->setMaxAge(60 * 60 * 10);
        }
        $response->headers->set('Vary', 'Cookie');
        // $response->headers->addCacheControlDirective('must-revalidate', true);
        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }

        return $this->render('VifeedFrontendBundle:'.$homepage.':homepage.html.twig', [], $response);
    }

    public function productOverviewAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:product-overview.html.twig', [], $response);
    }

    public function forCompaniesAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-companies.html.twig', [], $response);
    }

    public function forMoviesAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-movies.html.twig', [], $response);
    }

    public function forMusiciansAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-musicians.html.twig', [], $response);
    }

    public function forGamesAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-games.html.twig', [], $response);
    }

    public function forPartnersAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-partners.html.twig', [], $response);
    }

    public function forPublishersAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:for-publishers.html.twig', [], $response);
    }

    public function pressAboutUsAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:press-about-us.html.twig', [], $response);
    }

    public function aboutUsAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:about-us.html.twig', [], $response);
    }

    public function contactsAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:contacts.html.twig', [], $response);
    }

    public function informationAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        return $this->render('VifeedFrontendBundle:Public:information.html.twig', [], $response);
    }


    public function mirrorAction()
    {
        return $this->redirect($this->generateUrl('vifeed_frontend_homepage'), 301);
    }
}
