<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsiteBundle\Twig\Navigation;

use Sulu\Component\Cache\MemoizeInterface;
use Sulu\Component\Cache\MemoizeTwigExtensionTrait;

/**
 * Provides memoized navigation functions.
 */
class MemoizedNavigationTwigExtension extends \Twig_Extension
{
    use MemoizeTwigExtensionTrait;

    /**
     * @param $lifeTime
     */
    public function __construct(NavigationTwigExtensionInterface $extension, MemoizeInterface $memoizeCache, $lifeTime)
    {
        $this->extension = $extension;
        $this->memoizeCache = $memoizeCache;
        $this->lifeTime = $lifeTime;
    }

    public function getName()
    {
        return $this->extension->getName();
    }
}
