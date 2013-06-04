<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
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

            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

            new Cupon\CiudadBundle\CiudadBundle(),
            new Cupon\UsuarioBundle\UsuarioBundle(),
            new Cupon\TiendaBundle\TiendaBundle(),
            new Cupon\OfertaBundle\OfertaBundle(),
            new Cupon\BackendBundle\BackendBundle(),
            
            new Ideup\SimplePaginatorBundle\IdeupSimplePaginatorBundle(),

            // Descomenta las siguientes líneas para probar SonataAdminBundle:
            // 
            // new Sonata\jQueryBundle\SonatajQueryBundle(),
            // new Sonata\BlockBundle\SonataBlockBundle(),
            // new Sonata\CacheBundle\SonataCacheBundle(),
            // new Sonata\AdminBundle\SonataAdminBundle(),
            // new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            // new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            //
            // Antes de usar SonataAdminBundle, añade las siguientes dependencias
            // en el archivo composer.json:
            // 
            //     "sonata-project/admin-bundle": "dev-master",
            //     "sonata-project/cache-bundle": "dev-master",
            //     "sonata-project/doctrine-orm-admin-bundle": "dev-master"
            //
            // Y no olvides instalarlas ejecutando el siguiente comando:
            //     $ php composer.phar update 
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
        // Si tu archivo YAML contiene código PHP, utiliza el siguiente código:
        //
        // use Symfony\Component\Yaml\Yaml;
        //
        // Yaml::setPhpParsing(true);
        // $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        // Yaml::setPhpParsing(false);

        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
