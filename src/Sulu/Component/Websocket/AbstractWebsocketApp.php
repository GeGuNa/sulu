<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Websocket;

use Doctrine\Common\Cache\Cache;
use Ratchet\ConnectionInterface;
use Sulu\Component\Websocket\ConnectionContext\ConnectionContext;
use Sulu\Component\Websocket\ConnectionContext\ConnectionContextInterface;

/**
 * Abstract websocket app.
 */
abstract class AbstractWebsocketApp implements WebsocketAppInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $clients;

    /**
     * @var Cache
     */
    private $contexts;

    /**
     * @var string
     */
    protected $name;

    /**
     * initialize clients container.
     */
    public function __construct(Cache $contextsCache)
    {
        $this->clients = new \SplObjectStorage();
        $this->contexts = $contextsCache;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        if (!$this->getContext($conn)->isValid()) {
            $conn->close();
        } else {
            $this->clients->attach($conn);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->onClose($conn);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns websocket context for given connection.
     *
     * @return ConnectionContextInterface
     */
    protected function getContext(ConnectionInterface $conn)
    {
        $id = ConnectionContext::getIdFromConnection($conn);
        if (!$this->contexts->contains($id)) {
            $this->saveContext($this->createContext($conn));
        }

        return $this->contexts->fetch($id);
    }

    /**
     * Saves websocket context.
     */
    protected function saveContext(ConnectionContextInterface $context)
    {
        $this->contexts->save($context->getId(), $context);
    }

    /**
     * Returns new created websocket context object.
     *
     * @return ConnectionContextInterface
     */
    protected function createContext(ConnectionInterface $conn)
    {
        return new ConnectionContext($conn);
    }

    public function __toString()
    {
        return $this->getName();
    }
}
