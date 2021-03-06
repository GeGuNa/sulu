<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SuluSearchExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('jms_serializer', [
            'metadata' => [
                'directories' => [
                    [
                        'path' => \realpath(__DIR__ . '/..') . '/Resources/config/serializer/massive',
                        'namespace_prefix' => 'Massive\Bundle\SearchBundle\Search',
                    ],
                    [
                        'path' => \realpath(__DIR__ . '/..') . '/Resources/config/serializer/sulu',
                        'namespace_prefix' => 'Sulu\Bundle\SearchBundle\Search',
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('massive_search', [
            'services' => [
                'factory' => 'sulu_search.search.factory',
            ],
            'persistence' => [
                'doctrine_orm' => [
                    'enabled' => true,
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_search.indexes', $config['indexes']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('search.xml');
        $loader->load('build.xml');
    }
}
