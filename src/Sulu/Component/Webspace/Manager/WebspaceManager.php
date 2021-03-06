<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Webspace\Manager;

use Sulu\Component\Util\WildcardUrlUtil;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Component\Webspace\Manager\Dumper\PhpWebspaceCollectionDumper;
use Sulu\Component\Webspace\Portal;
use Sulu\Component\Webspace\PortalInformation;
use Sulu\Component\Webspace\Url\ReplacerInterface;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * This class is responsible for loading, reading and caching the portal configuration files.
 */
class WebspaceManager implements WebspaceManagerInterface
{
    /**
     * @var WebspaceCollection
     */
    private $webspaceCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var ReplacerInterface
     */
    private $urlReplacer;

    public function __construct(
        LoaderInterface $loader,
        ReplacerInterface $urlReplacer,
        $options = []
    ) {
        $this->loader = $loader;
        $this->urlReplacer = $urlReplacer;
        $this->setOptions($options);
    }

    /**
     * Returns the webspace with the given key.
     *
     * @param $key string The key to search for
     *
     * @return Webspace
     */
    public function findWebspaceByKey($key)
    {
        return $this->getWebspaceCollection()->getWebspace($key);
    }

    /**
     * Returns the portal with the given key.
     *
     * @param string $key The key to search for
     *
     * @return Portal
     */
    public function findPortalByKey($key)
    {
        return $this->getWebspaceCollection()->getPortal($key);
    }

    public function findPortalInformationByUrl($url, $environment)
    {
        $portalInformations = $this->getWebspaceCollection()->getPortalInformations($environment);
        foreach ($portalInformations as $portalInformation) {
            if ($this->matchUrl($url, $portalInformation->getUrl())) {
                return $portalInformation;
            }
        }

        return;
    }

    public function findPortalInformationsByUrl($url, $environment)
    {
        return \array_filter(
            $this->getWebspaceCollection()->getPortalInformations($environment),
            function(PortalInformation $portalInformation) use ($url) {
                return $this->matchUrl($url, $portalInformation->getUrl());
            }
        );
    }

    public function findPortalInformationsByWebspaceKeyAndLocale($webspaceKey, $locale, $environment)
    {
        return \array_filter(
            $this->getWebspaceCollection()->getPortalInformations($environment),
            function(PortalInformation $portalInformation) use ($webspaceKey, $locale) {
                return $portalInformation->getWebspace()->getKey() === $webspaceKey
                    && $portalInformation->getLocale() === $locale;
            }
        );
    }

    public function findPortalInformationsByPortalKeyAndLocale($portalKey, $locale, $environment)
    {
        return \array_filter(
            $this->getWebspaceCollection()->getPortalInformations($environment),
            function(PortalInformation $portalInformation) use ($portalKey, $locale) {
                return $portalInformation->getPortal()
                    && $portalInformation->getPortal()->getKey() === $portalKey
                    && $portalInformation->getLocale() === $locale;
            }
        );
    }

    public function findUrlsByResourceLocator(
        $resourceLocator,
        $environment,
        $languageCode,
        $webspaceKey = null,
        $domain = null,
        $scheme = 'http'
    ) {
        $urls = [];
        $portals = $this->getWebspaceCollection()->getPortalInformations(
            $environment,
            [RequestAnalyzerInterface::MATCH_TYPE_FULL]
        );
        foreach ($portals as $portalInformation) {
            $sameLocalization = $portalInformation->getLocalization()->getLocale() === $languageCode;
            $sameWebspace = null === $webspaceKey || $portalInformation->getWebspace()->getKey() === $webspaceKey;
            $url = $this->createResourceLocatorUrl($scheme, $portalInformation->getUrl(), $resourceLocator);
            if ($sameLocalization && $sameWebspace && $this->isFromDomain($url, $domain)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function findUrlByResourceLocator(
        $resourceLocator,
        $environment,
        $languageCode,
        $webspaceKey = null,
        $domain = null,
        $scheme = 'http'
    ) {
        $sameDomainUrls = [];
        $fullMatchedUrls = [];
        $partialMatchedUrls = [];

        $portals = $this->getWebspaceCollection()->getPortalInformations(
            $environment,
            [
                RequestAnalyzerInterface::MATCH_TYPE_FULL,
                RequestAnalyzerInterface::MATCH_TYPE_PARTIAL,
                RequestAnalyzerInterface::MATCH_TYPE_REDIRECT,
            ]
        );
        foreach ($portals as $portalInformation) {
            $sameLocalization = (
                null === $portalInformation->getLocalization()
                || $portalInformation->getLocalization()->getLocale() === $languageCode
            );
            $sameWebspace = null === $webspaceKey || $portalInformation->getWebspace()->getKey() === $webspaceKey;
            $url = $this->createResourceLocatorUrl($scheme, $portalInformation->getUrl(), $resourceLocator);
            if ($sameLocalization && $sameWebspace) {
                if (RequestAnalyzerInterface::MATCH_TYPE_FULL === $portalInformation->getType()) {
                    if ($this->isFromDomain($url, $domain)) {
                        if ($portalInformation->isMain()) {
                            \array_unshift($sameDomainUrls, $url);
                        } else {
                            $sameDomainUrls[] = $url;
                        }
                    } elseif ($portalInformation->isMain()) {
                        \array_unshift($fullMatchedUrls, $url);
                    } else {
                        $fullMatchedUrls[] = $url;
                    }
                } else {
                    $partialMatchedUrls[] = $url;
                }
            }
        }

        $fullMatchedUrls = \array_merge($sameDomainUrls, $fullMatchedUrls, $partialMatchedUrls);

        return \reset($fullMatchedUrls);
    }

    public function getPortals()
    {
        return $this->getWebspaceCollection()->getPortals();
    }

    public function getUrls($environment)
    {
        $urls = [];

        foreach ($this->getWebspaceCollection()->getPortalInformations($environment) as $portalInformation) {
            $urls[] = $portalInformation->getUrl();
        }

        return $urls;
    }

    public function getPortalInformations($environment)
    {
        return $this->getWebspaceCollection()->getPortalInformations($environment);
    }

    public function getPortalInformationsByWebspaceKey($environment, $webspaceKey)
    {
        return \array_filter(
            $this->getWebspaceCollection()->getPortalInformations($environment),
            function(PortalInformation $portal) use ($webspaceKey) {
                return $portal->getWebspaceKey() === $webspaceKey;
            }
        );
    }

    public function getAllLocalizations()
    {
        $localizations = [];

        foreach ($this->getWebspaceCollection() as $webspace) {
            /** @var Webspace $webspace */
            foreach ($webspace->getAllLocalizations() as $localization) {
                $localizations[$localization->getLocale()] = $localization;
            }
        }

        return $localizations;
    }

    public function getAllLocalesByWebspaces()
    {
        $webspaces = [];
        foreach ($this->getWebspaceCollection() as $webspace) {
            /** @var Webspace $webspace */
            $locales = [];
            $defaultLocale = $webspace->getDefaultLocalization();
            $locales[$defaultLocale->getLocale()] = $defaultLocale;
            foreach ($webspace->getAllLocalizations() as $localization) {
                if (!\array_key_exists($localization->getLocale(), $locales)) {
                    $locales[$localization->getLocale()] = $localization;
                }
            }
            $webspaces[$webspace->getKey()] = $locales;
        }

        return $webspaces;
    }

    /**
     * Returns all the webspaces managed by this specific instance.
     *
     * @return WebspaceCollection
     */
    public function getWebspaceCollection()
    {
        if (null === $this->webspaceCollection) {
            $class = $this->options['cache_class'];
            $cache = new ConfigCache(
                $this->options['cache_dir'] . '/' . $class . '.php',
                $this->options['debug']
            );

            if (!$cache->isFresh()) {
                $webspaceCollectionBuilder = new WebspaceCollectionBuilder(
                    $this->loader,
                    $this->urlReplacer,
                    $this->options['config_dir']
                );
                $webspaceCollection = $webspaceCollectionBuilder->build();
                $dumper = new PhpWebspaceCollectionDumper($webspaceCollection);
                $cache->write(
                    $dumper->dump(
                        [
                            'cache_class' => $class,
                            'base_class' => $this->options['base_class'],
                        ]
                    ),
                    $webspaceCollection->getResources()
                );
            }

            require_once $cache->getPath();

            $this->webspaceCollection = new $class();
        }

        return $this->webspaceCollection;
    }

    /**
     * Sets the options for the manager.
     *
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = [
            'config_dir' => null,
            'cache_dir' => null,
            'debug' => false,
            'cache_class' => 'WebspaceCollectionCache',
            'base_class' => 'WebspaceCollection',
        ];

        // overwrite the default values with the given options
        $this->options = \array_merge($this->options, $options);
    }

    /**
     * Url is from domain.
     *
     * @param $url
     * @param $domain
     *
     * @return array
     */
    protected function isFromDomain($url, $domain)
    {
        if (!$domain) {
            return true;
        }

        $parsedUrl = \parse_url($url);
        // if domain or subdomain
        if (
            isset($parsedUrl['host'])
            && (
                $parsedUrl['host'] == $domain
                || \fnmatch('*.' . $domain, $parsedUrl['host'])
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Matches given url with portal-url.
     *
     * @param string $url
     * @param string $portalUrl
     *
     * @return bool
     */
    protected function matchUrl($url, $portalUrl)
    {
        return WildcardUrlUtil::match($url, $portalUrl);
    }

    /**
     * Return a valid resource locator url.
     *
     * @param string $scheme
     * @param string $portalUrl
     * @param string $resourceLocator
     *
     * @return string
     */
    private function createResourceLocatorUrl($scheme, $portalUrl, $resourceLocator)
    {
        if (false !== \strpos($portalUrl, '/')) {
            // trim slash when resourceLocator is not domain root
            $resourceLocator = \rtrim($resourceLocator, '/');
        }

        return \rtrim(\sprintf('%s://%s', $scheme, $portalUrl), '/') . $resourceLocator;
    }
}
