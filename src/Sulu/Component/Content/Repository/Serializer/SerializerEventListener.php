<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Repository\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Sulu\Bundle\ContentBundle\Admin\ContentAdmin;
use Sulu\Component\Content\Document\RedirectType;
use Sulu\Component\Content\Document\WorkflowStage;
use Sulu\Component\Content\Repository\Content;
use Sulu\Component\Security\Authorization\AccessControl\AccessControlManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Appends additional data to content-serialization.
 */
class SerializerEventListener implements EventSubscriberInterface
{
    /**
     * @var AccessControlManagerInterface
     */
    private $accessControlManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        AccessControlManagerInterface $accessControlManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->accessControlManager = $accessControlManager;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    /**
     * Add data for serialization of content objects.
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var Content $content */
        $content = $event->getObject();
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        if (!($content instanceof Content)) {
            return;
        }

        foreach ($content->getData() as $key => $value) {
            $visitor->setData($key, $value);
        }

        $visitor->setData('publishedState', (WorkflowStage::PUBLISHED === $content->getWorkflowStage()));

        if (RedirectType::EXTERNAL === $content->getNodeType()) {
            $visitor->setData('linked', 'external');
        } elseif (RedirectType::INTERNAL === $content->getNodeType()) {
            $visitor->setData('linked', 'internal');
        }

        if (null !== $content->getLocalizationType()) {
            $visitor->setData('type', $content->getLocalizationType()->toArray());
        }

        $visitor->setData(
            '_permissions',
            $this->accessControlManager->getUserPermissionByArray(
                $content->getLocale(),
                ContentAdmin::SECURITY_CONTEXT_PREFIX . $content->getWebspaceKey(),
                $content->getPermissions(),
                $this->tokenStorage->getToken()->getUser()
            )
        );
    }
}
