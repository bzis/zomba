<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function getCharset()
    {
        return 'UTF-8';
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\Payment\CoreBundle\JMSPaymentCoreBundle(),
            new Karser\RobokassaBundle\KarserRobokassaBundle(),
            new cayetanosoriano\HashidsBundle\cayetanosorianoHashidsBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Chewbacco\Payment\QiwiWalletBundle\ChewbaccoPaymentQiwiWalletBundle(),
            new JMS\Payment\PaypalBundle\JMSPaymentPaypalBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Vresh\TwilioBundle\VreshTwilioBundle(),

            new Vifeed\CampaignBundle\VifeedCampaignBundle(),
            new Vifeed\UserBundle\VifeedUserBundle(),
            new Vifeed\SystemBundle\VifeedSystemBundle(),
            new Vifeed\PlatformBundle\VifeedPlatformBundle(),
            new Vifeed\VideoViewBundle\VifeedVideoViewBundle(),
            new Vifeed\VideoPromoBundle\VifeedVideoPromoBundle(),
            new Vifeed\PaymentBundle\VifeedPaymentBundle(),
            new Vifeed\FrontendBundle\VifeedFrontendBundle(),
            new Vifeed\GeoBundle\VifeedGeoBundle(),
            new Vifeed\TagBundle\VifeedTagBundle(),
            new Vifeed\BankPaymentBundle\BankPaymentBundle(),
            // new Nelmio\SecurityBundle\NelmioSecurityBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
