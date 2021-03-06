<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SnippetBundle\Twig;

use Sulu\Bundle\WebsiteBundle\Resolver\StructureResolverInterface;
use Sulu\Component\Content\Mapper\ContentMapperInterface;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;

/**
 * Provides Twig functions to handle snippets.
 */
class SnippetTwigExtension extends \Twig_Extension implements SnippetTwigExtensionInterface
{
    /**
     * @var ContentMapperInterface
     */
    private $contentMapper;

    /**
     * @var RequestAnalyzerInterface
     */
    private $requestAnalyzer;

    /**
     * @var StructureResolverInterface
     */
    private $structureResolver;

    /**
     * Constructor.
     */
    public function __construct(
        ContentMapperInterface $contentMapper,
        RequestAnalyzerInterface $requestAnalyzer,
        StructureResolverInterface $structureResolver
    ) {
        $this->contentMapper = $contentMapper;
        $this->requestAnalyzer = $requestAnalyzer;
        $this->structureResolver = $structureResolver;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('sulu_snippet_load', [$this, 'loadSnippet']),
        ];
    }

    public function loadSnippet($uuid, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->requestAnalyzer->getCurrentLocalization()->getLocale();
        }

        try {
            $snippet = $this->contentMapper->load($uuid, $this->requestAnalyzer->getWebspace()->getKey(), $locale);

            return $this->structureResolver->resolve($snippet);
        } catch (DocumentNotFoundException $ex) {
            return;
        }
    }

    public function getName()
    {
        return 'sulu_snippet';
    }
}
