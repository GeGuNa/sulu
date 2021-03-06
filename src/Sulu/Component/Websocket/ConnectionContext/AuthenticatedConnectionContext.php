<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Websocket\ConnectionContext;

use Ratchet\ConnectionInterface;

/**
 * Websocket context which implies logged in user in a specific firewall.
 */
class AuthenticatedConnectionContext extends ConnectionContext implements AuthenticatedConnectionContextInterface
{
    /**
     * @var string
     */
    private $firewall;

    public function __construct($firewall, ConnectionInterface $conn)
    {
        parent::__construct($conn);

        $this->firewall = $firewall;
    }

    public function getFirewallUser()
    {
        return $this->getUser($this->firewall);
    }

    public function isValid()
    {
        return null !== $this->getUser($this->firewall);
    }
}
