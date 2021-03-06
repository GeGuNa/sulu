<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RouteBundle\DependencyInjection;

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Container extension for sulu-route-bundle.
 */
class SuluRouteExtension extends Extension
{
    use PersistenceExtensionTrait;

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('sulu_route.mappings', $config['mappings']);

        $container->setParameter(
            'sulu_route.content_types.route.template',
            $config['content_types']['route']['template']
        );

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('routing.xml');
        $loader->load('manager.xml');
        $loader->load('generator.xml');

        $this->configurePersistence($config['objects'], $container);
    }
}
