<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\SmartContent\Configuration;

use Sulu\Component\Content\Compat\PropertyParameter;

/**
 * Provides configuration for smart-content.
 */
class ProviderConfiguration implements ProviderConfigurationInterface
{
    /**
     * @var ComponentConfigurationInterface
     */
    private $datasource;

    /**
     * @var bool
     */
    private $audienceTargeting = false;

    /**
     * @var bool
     */
    private $tags = false;

    /**
     * @var bool
     */
    private $categories = false;

    /**
     * @var PropertyParameter[]
     */
    private $sorting = [];

    /**
     * @var bool
     */
    private $limit = false;

    /**
     * @var bool
     */
    private $presentAs = false;

    /**
     * @var bool
     */
    private $paginated = false;

    /**
     * @var string
     */
    private $deepLink;

    public function hasDatasource()
    {
        return null !== $this->datasource && false !== $this->datasource;
    }

    public function getDatasource()
    {
        return $this->datasource;
    }

    /**
     * @param ComponentConfigurationInterface $datasource
     */
    public function setDatasource($datasource)
    {
        $this->datasource = $datasource;
    }

    public function hasAudienceTargeting()
    {
        return $this->audienceTargeting;
    }

    /**
     * @param string $audienceTargeting
     */
    public function setAudienceTargeting($audienceTargeting)
    {
        $this->audienceTargeting = $audienceTargeting;
    }

    public function hasTags()
    {
        return $this->tags;
    }

    /**
     * @param bool $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function hasCategories()
    {
        return $this->categories;
    }

    /**
     * @param bool $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    public function hasSorting()
    {
        return \count($this->sorting) > 0;
    }

    /**
     * @param PropertyParameter[] $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    public function hasLimit()
    {
        return $this->limit;
    }

    /**
     * @param bool $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function hasPresentAs()
    {
        return $this->presentAs;
    }

    /**
     * @param bool $presentAs
     */
    public function setPresentAs($presentAs)
    {
        $this->presentAs = $presentAs;
    }

    public function hasPagination()
    {
        return $this->paginated;
    }

    /**
     * @param bool $paginated
     */
    public function setPaginated($paginated)
    {
        $this->paginated = $paginated;
    }

    public function getDeepLink()
    {
        return $this->deepLink;
    }

    /**
     * @param string $deepLink
     */
    public function setDeepLink($deepLink)
    {
        $this->deepLink = $deepLink;
    }
}
