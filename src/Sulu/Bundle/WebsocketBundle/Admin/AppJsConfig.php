<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsocketBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\JsConfig;
use Sulu\Component\Websocket\AppManagerInterface;

/**
 * Provides config for websocket apps.
 */
class AppJsConfig extends JsConfig
{
    /**
     * @var AppManagerInterface
     */
    private $appManager;

    public function __construct($appManager)
    {
        $this->appManager = $appManager;
    }

    public function getName()
    {
        return 'sulu.websocket.apps';
    }

    public function getParameters()
    {
        return $this->appManager->getApps();
    }
}
