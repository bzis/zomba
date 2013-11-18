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
        } else {
            $response->setETag(md5('public'));
            $homepage = 'Public';
        }

        $response->headers->addCacheControlDirective('must-revalidate', true);
        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }

        return $this->render('VifeedFrontendBundle:'.$homepage.':homepage.html.twig', [], $response);
    }
}
