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

use Sulu\Bundle\WebsiteBundle\Navigation\NavigationMapperInterface;
use Sulu\Component\Content\Mapper\ContentMapperInterface;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;

/**
 * Provides the navigation functions.
 */
class NavigationTwigExtension extends \Twig_Extension implements NavigationTwigExtensionInterface
{
    /**
     * @var ContentMapperInterface
     */
    private $contentMapper;

    /**
     * @var NavigationMapperInterface
     */
    private $navigationMapper;

    /**
     * @var RequestAnalyzerInterface
     */
    private $requestAnalyzer;

    public function __construct(
        ContentMapperInterface $contentMapper,
        NavigationMapperInterface $navigationMapper,
        RequestAnalyzerInterface $requestAnalyzer = null
    ) {
        $this->contentMapper = $contentMapper;
        $this->navigationMapper = $navigationMapper;
        $this->requestAnalyzer = $requestAnalyzer;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('sulu_navigation_root_flat', [$this, 'flatRootNavigationFunction']),
            new \Twig_SimpleFunction('sulu_navigation_root_tree', [$this, 'treeRootNavigationFunction']),
            new \Twig_SimpleFunction('sulu_navigation_flat', [$this, 'flatNavigationFunction']),
            new \Twig_SimpleFunction('sulu_navigation_tree', [$this, 'treeNavigationFunction']),
            new \Twig_SimpleFunction('sulu_breadcrumb', [$this, 'breadcrumbFunction']),
            new \Twig_SimpleFunction('sulu_navigation_is_active', [$this, 'navigationIsActiveFunction']),
        ];
    }

    public function flatRootNavigationFunction($context = null, $depth = 1, $loadExcerpt = false)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        return $this->navigationMapper->getRootNavigation($webspaceKey, $locale, $depth, true, $context, $loadExcerpt);
    }

    public function treeRootNavigationFunction($context = null, $depth = 1, $loadExcerpt = false)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        return $this->navigationMapper->getRootNavigation($webspaceKey, $locale, $depth, false, $context, $loadExcerpt);
    }

    public function flatNavigationFunction($uuid, $context = null, $depth = 1, $loadExcerpt = false, $level = null)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        if (null !== $level) {
            $breadcrumb = $this->contentMapper->loadBreadcrumb(
                $uuid,
                $locale,
                $webspaceKey
            );

            // return empty array if level does not exists
            if (!isset($breadcrumb[$level])) {
                return [];
            }

            $uuid = $breadcrumb[$level]->getUuid();
        }

        try {
            return $this->navigationMapper->getNavigation($uuid, $webspaceKey, $locale, $depth, true, $context, $loadExcerpt);
        } catch (DocumentNotFoundException $exception) {
            return [];
        }
    }

    public function treeNavigationFunction($uuid, $context = null, $depth = 1, $loadExcerpt = false, $level = null)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        if (null !== $level) {
            $breadcrumb = $this->contentMapper->loadBreadcrumb(
                $uuid,
                $locale,
                $webspaceKey
            );

            // return empty array if level does not exists
            if (!isset($breadcrumb[$level])) {
                return [];
            }

            $uuid = $breadcrumb[$level]->getUuid();
        }

        try {
            return $this->navigationMapper->getNavigation(
                $uuid,
                $webspaceKey,
                $locale,
                $depth,
                false,
                $context,
                $loadExcerpt
            );
        } catch (DocumentNotFoundException $exception) {
            return [];
        }
    }

    public function breadcrumbFunction($uuid)
    {
        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();

        try {
            return $this->navigationMapper->getBreadcrumb($uuid, $webspaceKey, $locale);
        } catch (DocumentNotFoundException $exception) {
            return [];
        }
    }

    /**
     * @param string $requestPath
     * @param string $itemPath
     *
     * @return bool
     */
    public function navigationIsActiveFunction($requestPath, $itemPath)
    {
        if ($requestPath === $itemPath) {
            return true;
        }

        return \preg_match(\sprintf('/%s([\/]|$)/', \preg_quote($itemPath, '/')), $requestPath);
    }

    public function getName()
    {
        return 'sulu_website_navigation';
    }
}
