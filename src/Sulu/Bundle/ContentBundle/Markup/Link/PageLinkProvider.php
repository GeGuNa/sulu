<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Markup\Link;

use Sulu\Component\Content\Repository\Content;
use Sulu\Component\Content\Repository\ContentRepositoryInterface;
use Sulu\Component\Content\Repository\Mapping\MappingBuilder;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Integrates pages into link-system.
 */
class PageLinkProvider implements LinkProviderInterface
{
    /**
     * @var ContentRepositoryInterface
     */
    protected $contentRepository;

    /**
     * @var WebspaceManagerInterface
     */
    protected $webspaceManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @param string $environment
     */
    public function __construct(
        ContentRepositoryInterface $contentRepository,
        WebspaceManagerInterface $webspaceManager,
        RequestStack $requestStack,
        $environment
    ) {
        $this->contentRepository = $contentRepository;
        $this->webspaceManager = $webspaceManager;
        $this->requestStack = $requestStack;
        $this->environment = $environment;
    }

    public function getConfiguration()
    {
        return new LinkConfiguration(
            'content.ckeditor.page-link',
            'ckeditor/link/page@sulucontent',
            [],
            ['noSpacing' => true]
        );
    }

    public function preload(array $hrefs, $locale, $published = true)
    {
        $request = $this->requestStack->getCurrentRequest();
        $scheme = 'http';
        $domain = null;
        if ($request) {
            $scheme = $request->getScheme();
            $domain = $request->getHost();
        }

        $contents = $this->contentRepository->findByUuids(
            \array_unique(\array_values($hrefs)),
            $locale,
            MappingBuilder::create()
                ->setResolveUrl(true)
                ->addProperties(['title', 'published'])
                ->setOnlyPublished($published)
                ->setHydrateGhost(false)
                ->getMapping()
        );

        return \array_map(
            function(Content $content) use ($locale, $scheme, $domain) {
                return $this->getLinkItem($content, $locale, $scheme, $domain);
            },
            $contents
        );
    }

    /**
     * Returns new link item.
     *
     * @param string $locale
     * @param string $scheme
     * @param string|null $domain
     *
     * @return LinkItem
     */
    protected function getLinkItem(Content $content, $locale, $scheme, $domain = null)
    {
        $published = !empty($content->getPropertyWithDefault('published'));
        $url = $this->webspaceManager->findUrlByResourceLocator(
            $content->getUrl(),
            $this->environment,
            $locale,
            $content->getWebspaceKey(),
            $domain,
            $scheme
        );

        return new LinkItem($content->getId(), $content->getPropertyWithDefault('title'), $url, $published);
    }
}
