<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Sulu\Component\Content\Document\Behavior\RedirectTypeBehavior;
use Sulu\Component\Content\Document\RedirectType;

/**
 * Adds information about the redirects to the serialized document.
 */
class RedirectTypeSubscriber implements EventSubscriberInterface
{
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
     * Adds the type of redirect and the redirect location to the serialization.
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var RedirectTypeBehavior $document */
        $document = $event->getObject();

        if (!$document instanceof RedirectTypeBehavior) {
            return;
        }

        $visitor = $event->getVisitor();

        $redirectType = $document->getRedirectType();

        if (RedirectType::INTERNAL == $redirectType && null !== $document->getRedirectTarget()) {
            $visitor->addData('linked', 'internal');
            $visitor->addData('internal_link', $document->getRedirectTarget()->getUuid());
        } elseif (RedirectType::EXTERNAL == $redirectType) {
            $visitor->addData('linked', 'external');
            $visitor->addData('external', $document->getRedirectExternal());
        } else {
            $visitor->addData('linked', null);
        }
    }
}
