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

use Ratchet\ConnectionInterface;

/**
 * Interface MessageHandlerInterface.
 */
interface MessageHandlerInterface
{
    /**
     * Processes given message.
     */
    public function handle(ConnectionInterface $conn, array $message, MessageHandlerContext $context);

    /**
     * Connection lost.
     */
    public function onClose(ConnectionInterface $conn, MessageHandlerContext $context);
}
