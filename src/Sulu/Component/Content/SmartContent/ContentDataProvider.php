<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\SmartContent;

use PHPCR\ItemNotFoundException;
use PHPCR\SessionInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Sulu\Bundle\ContentBundle\Document\PageDocument;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\Content\Query\ContentQueryBuilderInterface;
use Sulu\Component\Content\Query\ContentQueryExecutorInterface;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\Configuration\Builder;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderAliasInterface;
use Sulu\Component\SmartContent\DataProviderInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\DatasourceItem;

/**
 * DataProvider for content.
 */
class ContentDataProvider implements DataProviderInterface, DataProviderAliasInterface
{
    /**
     * @var ContentQueryBuilderInterface
     */
    private $contentQueryBuilder;

    /**
     * @var ContentQueryExecutorInterface
     */
    private $contentQueryExecutor;

    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    /**
     * @var ProviderConfigurationInterface
     */
    private $configuration;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ReferenceStoreInterface
     */
    private $referenceStore;

    /**
     * @var bool
     */
    private $showDrafts;

    public function __construct(
        ContentQueryBuilderInterface $contentQueryBuilder,
        ContentQueryExecutorInterface $contentQueryExecutor,
        DocumentManagerInterface $documentManager,
        LazyLoadingValueHolderFactory $proxyFactory,
        SessionInterface $session,
        ReferenceStoreInterface $referenceStore,
        $showDrafts
    ) {
        $this->contentQueryBuilder = $contentQueryBuilder;
        $this->contentQueryExecutor = $contentQueryExecutor;
        $this->documentManager = $documentManager;
        $this->proxyFactory = $proxyFactory;
        $this->session = $session;
        $this->referenceStore = $referenceStore;
        $this->showDrafts = $showDrafts;
    }

    public function getConfiguration()
    {
        if (!$this->configuration) {
            return $this->initConfiguration();
        }

        return $this->configuration;
    }

    /**
     * Initiate configuration.
     *
     * @return ProviderConfigurationInterface
     */
    private function initConfiguration()
    {
        $this->configuration = Builder::create()
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableAudienceTargeting()
            ->enableDatasource(
                'content-datasource@sulucontent',
                [
                    'rootUrl' => '/admin/api/nodes?language={locale}&fields=title,order,published&webspace-nodes=all',
                    'selectedUrl' => '/admin/api/nodes/{datasource}?tree=true&language={locale}&fields=title,order,published&webspace-nodes=all',
                    'resultKey' => 'nodes',
                ]
            )
            ->enableSorting(
                [
                    ['column' => 'title', 'title' => 'smart-content.title'],
                    ['column' => 'published', 'title' => 'smart-content.published'],
                    ['column' => 'created', 'title' => 'smart-content.created'],
                    ['column' => 'changed', 'title' => 'smart-content.changed'],
                    ['column' => 'authored', 'title' => 'smart-content.authored'],
                ]
            )
            ->setDeepLink('content/contents/{webspace}/{locale}/edit:{id}/details')
            ->getConfiguration();

        return $this->configuration;
    }

    public function getDefaultPropertyParameter()
    {
        return [
            'properties' => new PropertyParameter('properties', [], 'collection'),
        ];
    }

    public function resolveDatasource($datasource, array $propertyParameter, array $options)
    {
        $properties = \array_key_exists('properties', $propertyParameter) ?
            $propertyParameter['properties']->getValue() : [];

        $this->contentQueryBuilder->init(
            [
                'ids' => [$datasource],
                'properties' => $properties,
                'published' => false,
            ]
        );

        $result = $this->contentQueryExecutor->execute(
            $options['webspaceKey'],
            [$options['locale']],
            $this->contentQueryBuilder,
            true,
            -1,
            1,
            0
        );

        if (0 === \count($result)) {
            return;
        }

        return new DatasourceItem($result[0]['uuid'], $result[0]['title'], '/' . \ltrim($result[0]['path'], '/'));
    }

    public function resolveDataItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {
        list($items, $hasNextPage) = $this->resolveFilters(
            $filters,
            $propertyParameter,
            $options,
            $limit,
            $page,
            $pageSize
        );

        $items = $this->decorateDataItems($items, $options['locale']);

        return new DataProviderResult($items, $hasNextPage);
    }

    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {
        list($items, $hasNextPage) = $this->resolveFilters(
            $filters,
            $propertyParameter,
            $options,
            $limit,
            $page,
            $pageSize
        );
        $items = $this->decorateResourceItems($items, $options['locale']);

        return new DataProviderResult($items, $hasNextPage);
    }

    /**
     * Resolves filters.
     */
    private function resolveFilters(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {
        $emptyFilterResult = [[], false];

        if (!\array_key_exists('dataSource', $filters)
            || '' === $filters['dataSource']
            || (null !== $limit && $limit < 1)
        ) {
            return $emptyFilterResult;
        }

        try {
            $this->session->getNodeByIdentifier($filters['dataSource']);
        } catch (ItemNotFoundException $e) {
            return $emptyFilterResult;
        }

        $properties = \array_key_exists('properties', $propertyParameter) ?
            $propertyParameter['properties']->getValue() : [];

        $excluded = $filters['excluded'];
        if (\array_key_exists('exclude_duplicates', $propertyParameter)
            && $propertyParameter['exclude_duplicates']->getValue()
        ) {
            $excluded = \array_merge($excluded, $this->referenceStore->getAll());
        }

        $this->contentQueryBuilder->init(
            [
                'config' => $filters,
                'properties' => $properties,
                'excluded' => $excluded,
                'published' => !$this->showDrafts,
            ]
        );

        $hasNextPage = false;
        if (null !== $pageSize) {
            $result = $this->loadPaginated($options, $limit, $page, $pageSize);
            $hasNextPage = (\count($result) > $pageSize);
            $items = \array_splice($result, 0, $pageSize);
        } else {
            $items = $this->load($options, $limit);
        }

        return [$items, $hasNextPage];
    }

    /**
     * Load paginated data.
     *
     * @param int $limit
     * @param int $page
     * @param int $pageSize
     *
     * @return array
     */
    private function loadPaginated(array $options, $limit, $page, $pageSize)
    {
        $pageSize = \intval($pageSize);
        $offset = ($page - 1) * $pageSize;

        $position = $pageSize * $page;
        if (null !== $limit && $position >= $limit) {
            $pageSize = $limit - $offset;
            $loadLimit = $pageSize;
        } else {
            $loadLimit = $pageSize + 1;
        }

        return $this->contentQueryExecutor->execute(
            $options['webspaceKey'],
            [$options['locale']],
            $this->contentQueryBuilder,
            true,
            -1,
            $loadLimit,
            $offset
        );
    }

    /**
     * Load data.
     *
     * @param int $limit
     *
     * @return array
     */
    private function load(array $options, $limit)
    {
        return $this->contentQueryExecutor->execute(
            $options['webspaceKey'],
            [$options['locale']],
            $this->contentQueryBuilder,
            true,
            -1,
            $limit
        );
    }

    /**
     * Decorates result with item class.
     *
     * @param string $locale
     *
     * @return ContentDataItem[]
     */
    private function decorateDataItems(array $data, $locale)
    {
        return \array_map(
            function($item) use ($locale) {
                return new ContentDataItem($item, $this->getResource($item['uuid'], $locale));
            },
            $data
        );
    }

    /**
     * Decorates result with item class.
     *
     * @param string $locale
     *
     * @return ArrayAccessItem[]
     */
    private function decorateResourceItems(array $data, $locale)
    {
        return \array_map(
            function($item) use ($locale) {
                $this->referenceStore->add($item['uuid']);

                return new ArrayAccessItem($item['uuid'], $item, $this->getResource($item['uuid'], $locale));
            },
            $data
        );
    }

    /**
     * Returns Proxy Document for uuid.
     *
     * @param string $uuid
     * @param string $locale
     *
     * @return object
     */
    private function getResource($uuid, $locale)
    {
        return $this->proxyFactory->createProxy(
            PageDocument::class,
            function(
                &$wrappedObject,
                LazyLoadingInterface $proxy,
                $method,
                array $parameters,
                &$initializer
            ) use ($uuid, $locale) {
                $initializer = null;
                $wrappedObject = $this->documentManager->find($uuid, $locale);

                return true;
            }
        );
    }

    public function getAlias()
    {
        return 'content';
    }
}
