<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Tests\Unit\SmartContent;

use PHPCR\ItemNotFoundException;
use PHPCR\SessionInterface;
use Prophecy\Argument;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use Sulu\Bundle\ContentBundle\Document\BasePageDocument;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStore;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\Content\Query\ContentQueryBuilderInterface;
use Sulu\Component\Content\Query\ContentQueryExecutorInterface;
use Sulu\Component\Content\SmartContent\ContentDataItem;
use Sulu\Component\Content\SmartContent\ContentDataProvider;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\DatasourceItem;

class ContentDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array|null $initValue
     *
     * @return ContentQueryBuilderInterface
     */
    private function getContentQueryBuilder($initValue = null)
    {
        $mock = $this->prophesize(ContentQueryBuilderInterface::class);

        if (null !== $initValue) {
            $mock->init($initValue)->shouldBeCalled();
        }

        return $mock->reveal();
    }

    /**
     * @param int $limit
     * @param int $page
     * @param array $result
     *
     * @return ContentQueryExecutorInterface
     */
    private function getContentQueryExecutor($limit = -1, $page = 1, $result = [])
    {
        $mock = $this->prophesize(ContentQueryExecutorInterface::class);

        $mock->execute(
            'sulu_io',
            ['en'],
            Argument::type(ContentQueryBuilderInterface::class),
            true,
            -1,
            ($limit > -1 ? $limit + 1 : null),
            ($limit > -1 ? $limit * ($page - 1) : null)
        )->willReturn($result);

        return $mock->reveal();
    }

    /**
     * @param array $pages
     *
     * @return DocumentManagerInterface
     */
    private function getDocumentManager($pages = [])
    {
        $mock = $this->prophesize(DocumentManagerInterface::class);

        foreach ($pages as $uuid => $value) {
            $mock->find($uuid, 'en')->willReturn($value);
        }

        return $mock->reveal();
    }

    /**
     * @return LazyLoadingValueHolderFactory
     */
    private function getProxyFactory()
    {
        $writer = new FileWriterGeneratorStrategy(new FileLocator(\sys_get_temp_dir()));

        $configuration = new Configuration();
        $configuration->setGeneratorStrategy($writer);

        return new LazyLoadingValueHolderFactory($configuration);
    }

    private function getSession($throw = false)
    {
        $mock = $this->prophesize(SessionInterface::class);

        if ($throw) {
            $mock->getNodeByIdentifier(Argument::any())->willThrow(ItemNotFoundException::class);
        }

        return $mock->reveal();
    }

    public function testGetConfiguration()
    {
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(),
            $this->getContentQueryExecutor(),
            $this->getDocumentManager(),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            false
        );

        $configuration = $provider->getConfiguration();

        $this->assertInstanceOf(ProviderConfigurationInterface::class, $configuration);
    }

    public function testGetDefaultParameter()
    {
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(),
            $this->getContentQueryExecutor(),
            $this->getDocumentManager(),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            false
        );

        $parameter = $provider->getDefaultPropertyParameter();

        foreach ($parameter as $p) {
            $this->assertInstanceOf(PropertyParameter::class, $p);
        }

        $this->assertArrayHasKey('properties', $parameter);
    }

    public function testResolveDataItemsNoDataSource()
    {
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(),
            $this->getContentQueryExecutor(),
            $this->getDocumentManager(),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            false
        );

        $result = $provider->resolveDataItems(
            ['excluded' => ['123-123-123']],
            ['properties' => ['my-properties' => true]],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            2,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $this->assertEquals([], $result->getItems());
    }

    public function testResolveDataItemsNoResult()
    {
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123'],
                    'published' => false,
                ]
            ),
            $this->getContentQueryExecutor(2, 2, []),
            $this->getDocumentManager(),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            true
        );

        $result = $provider->resolveDataItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            2,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $this->assertEquals([], $result->getItems());
        $this->assertFalse($result->getHasNextPage());
    }

    public function testResolveDataItemsHasNextPage()
    {
        $data = [
            ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'],
            ['uuid' => '123-123-456', 'title' => 'My-Page-1', 'path' => '/my-page-1'],
            ['uuid' => '123-123-789', 'title' => 'My-Page-2', 'path' => '/my-page-2'],
        ];

        $document1 = $this->prophesize(BasePageDocument::class);
        $document2 = $this->prophesize(BasePageDocument::class);

        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123'],
                    'published' => false,
                ]
            ),
            $this->getContentQueryExecutor(2, 1, $data),
            $this->getDocumentManager(['123-123-123' => $document1->reveal(), '123-123-456' => $document2->reveal()]),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            true
        );

        $result = $provider->resolveDataItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            1,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $items = $result->getItems();
        $this->assertEquals($data[0]['uuid'], $items[0]->getId());
        $this->assertTrue($items[0]->getResource()->initializeProxy());
        $this->assertEquals($document1->reveal(), $items[0]->getResource()->getWrappedValueHolderValue());
        $this->assertEquals($data[1]['uuid'], $items[1]->getId());
        $this->assertTrue($items[1]->getResource()->initializeProxy());
        $this->assertEquals($document2->reveal(), $items[1]->getResource()->getWrappedValueHolderValue());
        $this->assertTrue($result->getHasNextPage());
    }

    public function testResolveDataItemsOnlyPublished()
    {
        $data = [
            ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'],
            ['uuid' => '123-123-456', 'title' => 'My-Page-1', 'path' => '/my-page-1'],
            ['uuid' => '123-123-789', 'title' => 'My-Page-2', 'path' => '/my-page-2'],
        ];

        $document1 = $this->prophesize(BasePageDocument::class);
        $document2 = $this->prophesize(BasePageDocument::class);

        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123'],
                    'published' => true,
                ]
            ),
            $this->getContentQueryExecutor(2, 1, $data),
            $this->getDocumentManager(['123-123-123' => $document1->reveal(), '123-123-456' => $document2->reveal()]),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            false
        );

        $result = $provider->resolveDataItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            1,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $items = $result->getItems();
        $this->assertEquals($data[0]['uuid'], $items[0]->getId());
        $this->assertTrue($items[0]->getResource()->initializeProxy());
        $this->assertEquals($document1->reveal(), $items[0]->getResource()->getWrappedValueHolderValue());
        $this->assertEquals($data[1]['uuid'], $items[1]->getId());
        $this->assertTrue($items[1]->getResource()->initializeProxy());
        $this->assertEquals($document2->reveal(), $items[1]->getResource()->getWrappedValueHolderValue());
        $this->assertTrue($result->getHasNextPage());
    }

    public function testResolveResourceItems()
    {
        $data = [
            ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'],
            ['uuid' => '123-123-456', 'title' => 'My-Page-1', 'path' => '/my-page-1'],
            ['uuid' => '123-123-789', 'title' => 'My-Page-2', 'path' => '/my-page-2'],
        ];

        $document1 = $this->prophesize(BasePageDocument::class);
        $document2 = $this->prophesize(BasePageDocument::class);

        $referenceStore = new ReferenceStore();
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123'],
                    'published' => false,
                ]
            ),
            $this->getContentQueryExecutor(2, 1, $data),
            $this->getDocumentManager(['123-123-123' => $document1->reveal(), '123-123-456' => $document2->reveal()]),
            $this->getProxyFactory(),
            $this->getSession(),
            $referenceStore,
            true
        );

        $result = $provider->resolveResourceItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            1,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $items = $result->getItems();
        $this->assertEquals($data[0]['uuid'], $items[0]->getId());
        $this->assertTrue($items[0]->getResource()->initializeProxy());
        $this->assertEquals($document1->reveal(), $items[0]->getResource()->getWrappedValueHolderValue());
        $this->assertEquals($data[1]['uuid'], $items[1]->getId());
        $this->assertTrue($items[1]->getResource()->initializeProxy());
        $this->assertEquals($document2->reveal(), $items[1]->getResource()->getWrappedValueHolderValue());
        $this->assertTrue($result->getHasNextPage());
        $this->assertEquals(['123-123-123', '123-123-456'], $referenceStore->getAll());
    }

    public function testResolveDataItemsNoPagination()
    {
        $data = [
            ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'],
            ['uuid' => '123-123-456', 'title' => 'My-Page-1', 'path' => '/my-page-1'],
            ['uuid' => '123-123-789', 'title' => 'My-Page-2', 'path' => '/my-page-2'],
        ];

        $document1 = $this->prophesize(BasePageDocument::class);
        $document2 = $this->prophesize(BasePageDocument::class);
        $document3 = $this->prophesize(BasePageDocument::class);

        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123'],
                    'published' => false,
                ]
            ),
            $this->getContentQueryExecutor(-1, null, $data),
            $this->getDocumentManager(
                ['123-123-123' => $document1->reveal(), '123-123-456' => $document2->reveal(), '123-123-789' => $document3->reveal()]
            ),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            true
        );

        $result = $provider->resolveDataItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en']
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);

        $items = $result->getItems();
        $this->assertEquals($data[0]['uuid'], $items[0]->getId());
        $this->assertTrue($items[0]->getResource()->initializeProxy());
        $this->assertEquals($document1->reveal(), $items[0]->getResource()->getWrappedValueHolderValue());
        $this->assertEquals($data[1]['uuid'], $items[1]->getId());
        $this->assertTrue($items[1]->getResource()->initializeProxy());
        $this->assertEquals($document2->reveal(), $items[1]->getResource()->getWrappedValueHolderValue());
        $this->assertEquals($data[2]['uuid'], $items[2]->getId());
        $this->assertTrue($items[2]->getResource()->initializeProxy());
        $this->assertEquals($document3->reveal(), $items[2]->getResource()->getWrappedValueHolderValue());
        $this->assertFalse($result->getHasNextPage());
    }

    public function testResolveDataItemsWithDeletedDataSource()
    {
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(),
            $this->getContentQueryExecutor(),
            $this->getDocumentManager(),
            $this->getProxyFactory(),
            $this->getSession(true),
            new ReferenceStore(),
            true
        );

        $result = $provider->resolveDataItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            10
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $this->assertCount(0, $result->getItems());
    }

    public function testResolveDatasource()
    {
        $data = ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'];

        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                ['ids' => [$data['uuid']], 'properties' => ['my-properties' => true], 'published' => false]
            ),
            $this->getContentQueryExecutor(0, 1, [$data]),
            $this->getDocumentManager([$data['uuid'] => $data]),
            $this->getProxyFactory(),
            $this->getSession(),
            new ReferenceStore(),
            false
        );

        $result = $provider->resolveDatasource(
            $data['uuid'],
            ['properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection')],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en']
        );

        $this->assertInstanceOf(DatasourceItem::class, $result);
        $this->assertEquals($data['uuid'], $result->getId());
        $this->assertEquals($data['title'], $result->getTitle());
        $this->assertEquals($data['path'], $result->getPath());
        $this->assertNull($result->getImage());
    }

    public function testContentDataItem()
    {
        $data = ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'];
        $resource = new \stdClass();
        $item = new ContentDataItem($data, $resource);

        $this->assertEquals($data['uuid'], $item->getId());
        $this->assertEquals($data['title'], $item->getTitle());
        $this->assertEquals($resource, $item->getResource());

        $this->assertNull($item->getImage());
    }

    public function testResolveResourceItemsExcluded()
    {
        $data = [
            ['uuid' => '123-123-123', 'title' => 'My-Page', 'path' => '/my-page'],
            ['uuid' => '123-123-456', 'title' => 'My-Page-1', 'path' => '/my-page-1'],
            ['uuid' => '123-123-789', 'title' => 'My-Page-2', 'path' => '/my-page-2'],
        ];

        $document1 = $this->prophesize(BasePageDocument::class);
        $document2 = $this->prophesize(BasePageDocument::class);

        $documents = [$document1->reveal(), $document2->reveal()];

        $referenceStore = new ReferenceStore();
        $referenceStore->add('123-456-789');
        $provider = new ContentDataProvider(
            $this->getContentQueryBuilder(
                [
                    'config' => ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
                    'properties' => ['my-properties' => true],
                    'excluded' => ['123-123-123', '123-456-789'],
                    'published' => false,
                ]
            ),
            $this->getContentQueryExecutor(2, 1, $data),
            $this->getDocumentManager(['123-123-123' => $document1->reveal(), '123-123-456' => $document2->reveal()]),
            $this->getProxyFactory(),
            $this->getSession(),
            $referenceStore,
            true
        );

        $result = $provider->resolveResourceItems(
            ['dataSource' => '123-123-123', 'excluded' => ['123-123-123']],
            [
                'properties' => new PropertyParameter('properties', ['my-properties' => true], 'collection'),
                'exclude_duplicates' => new PropertyParameter('exclude_duplicates', true),
            ],
            ['webspaceKey' => 'sulu_io', 'locale' => 'en'],
            5,
            1,
            2
        );

        $this->assertInstanceOf(DataProviderResult::class, $result);

        $items = $result->getItems();
        for ($i = 0, $length = \count($items); $i < $length; ++$i) {
            $this->assertEquals($data[$i]['uuid'], $items[$i]->getId());
            $this->assertEquals($data[$i], $items[$i]->jsonSerialize());

            $this->assertTrue($items[$i]->getResource()->initializeProxy());
            $this->assertEquals($documents[$i], $items[$i]->getResource()->getWrappedValueHolderValue());
        }

        $this->assertTrue($result->getHasNextPage());
        $this->assertEquals(['123-456-789', '123-123-123', '123-123-456'], $referenceStore->getAll());
    }
}
