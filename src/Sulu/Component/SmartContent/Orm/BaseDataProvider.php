<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\SmartContent\Orm;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\Configuration\Builder;
use Sulu\Component\SmartContent\Configuration\BuilderInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfiguration;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * Provides basic functionality for contact and account providers.
 */
abstract class BaseDataProvider implements DataProviderInterface
{
    /**
     * Creates a new configuration object.
     *
     * @return BuilderInterface
     */
    protected static function createConfigurationBuilder()
    {
        return Builder::create();
    }

    /**
     * @var DataProviderRepositoryInterface
     */
    protected $repository;

    /**
     * @var ProviderConfigurationInterface
     */
    protected $configuration;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ReferenceStoreInterface
     */
    private $referenceStore;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        SerializerInterface $serializer,
        ReferenceStoreInterface $referenceStore = null
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->referenceStore = $referenceStore;
    }

    public function getDefaultPropertyParameter()
    {
        return [];
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function resolveDatasource($datasource, array $propertyParameter, array $options)
    {
        return;
    }

    public function resolveDataItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {
        list($result, $hasNextPage) = $this->resolveFilters(
            $filters,
            $options['locale'],
            $limit,
            $page,
            $pageSize,
            $this->getOptions($propertyParameter, $options)
        );

        return new DataProviderResult($this->decorateDataItems($result), $hasNextPage);
    }

    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ) {
        list($result, $hasNextPage) = $this->resolveFilters(
            $filters,
            $options['locale'],
            $limit,
            $page,
            $pageSize,
            $this->getOptions($propertyParameter, $options)
        );

        return new DataProviderResult($this->decorateResourceItems($result, $options['locale']), $hasNextPage);
    }

    /**
     * Resolves filters.
     */
    private function resolveFilters(
        array $filters,
        $locale,
        $limit = null,
        $page = 1,
        $pageSize = null,
        $options = []
    ) {
        $result = $this->repository->findByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        $hasNextPage = false;
        if (null !== $pageSize && \count($result) > $pageSize) {
            $hasNextPage = true;
            $result = \array_splice($result, 0, $pageSize);
        }

        return [$result, $hasNextPage];
    }

    /**
     * Initiate configuration.
     *
     * @return ProviderConfigurationInterface
     *
     * @deprecated use self::createConfigurationBuilder instead
     */
    protected function initConfiguration($tags, $categories, $limit, $presentAs, $paginated, $sorting)
    {
        $configuration = new ProviderConfiguration();
        $configuration->setTags($tags);
        $configuration->setCategories($categories);
        $configuration->setLimit($limit);
        $configuration->setPresentAs($presentAs);
        $configuration->setPaginated($paginated);
        $configuration->setSorting($sorting);

        return $configuration;
    }

    /**
     * Decorates result as resource item.
     *
     * @param string $locale
     *
     * @return ArrayAccessItem[]
     */
    protected function decorateResourceItems(array $data, $locale)
    {
        return \array_map(
            function($item) {
                $itemData = $this->serializer->serialize($item, 'array', $this->getSerializationContext());
                $id = $this->getIdForItem($item);

                if ($this->referenceStore) {
                    $this->referenceStore->add($id);
                }

                return new ArrayAccessItem($id, $itemData, $item);
            },
            $data
        );
    }

    /**
     * Returns id for given entity.
     *
     * @param object $entity
     *
     * @return int
     */
    protected function getIdForItem($entity)
    {
        return $entity->getId();
    }

    /**
     * Creates serialization context. Can be used to add own groups.
     *
     * @return SerializationContext
     */
    protected function getSerializationContext()
    {
        return SerializationContext::create()->setSerializeNull(true);
    }

    /**
     * Returns additional options for query creation.
     *
     * @param PropertyParameter[] $propertyParameter
     *
     * @return array
     */
    protected function getOptions(
        array $propertyParameter,
        array $options = []
    ) {
        return [];
    }

    /**
     * Decorates result as data item.
     *
     * @return ItemInterface[]
     */
    abstract protected function decorateDataItems(array $data);
}
