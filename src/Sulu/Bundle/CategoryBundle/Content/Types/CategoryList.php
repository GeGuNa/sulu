<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CategoryBundle\Content\Types;

use PHPCR\NodeInterface;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\ComplexContentType;
use Sulu\Component\Content\ContentTypeExportInterface;

/**
 * Content Type for the CategoryList, uses the CategoryManager-Service and the Datagrid from Husky.
 */
class CategoryList extends ComplexContentType implements ContentTypeExportInterface
{
    /**
     * Responsible for persisting the categories in the database.
     *
     * @var CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * Holds the template for rendering this content type in the admin.
     *
     * @var string
     */
    private $template;

    public function __construct(CategoryManagerInterface $categoryManager, $template)
    {
        $this->categoryManager = $categoryManager;
        $this->template = $template;
    }

    public function read(NodeInterface $node, PropertyInterface $property, $webspaceKey, $languageCode, $segmentKey)
    {
        $categoryIds = $node->getPropertyValueWithDefault($property->getName(), []);
        $property->setValue($categoryIds);
    }

    public function getContentData(PropertyInterface $property)
    {
        $ids = $property->getValue();
        if (!\is_array($ids) || empty($ids)) {
            return [];
        }

        $data = [];
        $entities = $this->categoryManager->findByIds($ids);
        $categories = $this->categoryManager->getApiObjects($entities, $property->getStructure()->getLanguageCode());

        foreach ($categories as $category) {
            $data[] = $category->toArray();
        }

        return $data;
    }

    public function write(
        NodeInterface $node,
        PropertyInterface $property,
        $userId,
        $webspaceKey,
        $languageCode,
        $segmentKey
    ) {
        $categoryIds = [];
        $value = $property->getValue();

        if (null === $value) {
            $node->setProperty($property->getName(), null);

            return;
        }

        foreach ($value as $category) {
            if (\is_numeric($category)) {
                // int value for id
                $categoryIds[] = $category;
            } else {
                // full category object use only id to save
                $categoryIds[] = $category['id'];
            }
        }

        $node->setProperty($property->getName(), $categoryIds);
    }

    public function remove(NodeInterface $node, PropertyInterface $property, $webspaceKey, $languageCode, $segmentKey)
    {
        if ($node->hasProperty($property->getName())) {
            $property = $node->getProperty($property->getName());
            $property->remove();
        }
    }

    /**
     * returns a template to render a form.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function exportData($propertyValue)
    {
        if (\is_array($propertyValue) && \count($propertyValue) > 0) {
            return \json_encode($propertyValue);
        }

        return '';
    }

    public function importData(
        NodeInterface $node,
        PropertyInterface $property,
        $value,
        $userId,
        $webspaceKey,
        $languageCode,
        $segmentKey = null
    ) {
        $property->setValue(\json_decode($value));
        $this->write($node, $property, $userId, $webspaceKey, $languageCode, $segmentKey);
    }
}
