<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Websocket\MessageDispatcher;

use Sulu\Component\Websocket\ConnectionContext\AuthenticatedConnectionContextInterface;

/**
 * Message handler connection authenticated context.
 */
class AuthenticatedMessageHandlerContext extends MessageHandlerContext implements AuthenticatedConnectionContextInterface
{
    public function __construct(AuthenticatedConnectionContextInterface $context, $handlerName)
    {
        parent::__construct($context, $handlerName);
    }

    public function getFirewallUser()
    {
        return $this->context->getFirewallUser();
    }
}
