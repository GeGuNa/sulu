<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsocketBundle\Connection;

use Ratchet\ConnectionInterface;

/**
 * Fall connection to use same interface as websockets.
 */
class FallbackConnection implements ConnectionInterface
{
    /**
     * Container for send data.
     *
     * @var mixed
     */
    private $data;

    /**
     * Id of connection.
     *
     * @var string
     */
    public $resourceId;

    public function __construct($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    public function send($data)
    {
        $this->data = $data;

        return $this;
    }

    public function close()
    {
        // do nothing
    }

    public function getData()
    {
        return $this->data;
    }
}
