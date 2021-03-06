<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsocketBundle\App;

use Ratchet\ComponentInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Http\OriginCheck;
use Ratchet\Http\Router;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Reactor;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class was inspired by Ratchet\App but was decoupled from it because of trying to connect to 8843.
 */
class WebsocketApp
{
    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    public $routes;

    /**
     * @var \Ratchet\Server\IoServer
     */
    protected $_server;

    /**
     * The Host passed in construct used for same origin policy.
     *
     * @var string
     */
    protected $httpHost;

    /***
     * The port the socket is listening
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $_routeCounter = 0;

    /**
     * @param string $httpHost HTTP hostname clients intend to connect to. MUST match JS `new WebSocket('ws://$httpHost');`
     * @param int $port Port to listen on. If 80, assuming production, Flash on 843 otherwise expecting Flash to be proxied through 8843
     * @param string $address IP address to bind to. Default is localhost/proxy only. '0.0.0.0' for any machine
     * @param LoopInterface $loop Specific React\EventLoop to bind the application to. null will create one for you
     */
    public function __construct(
        $httpHost = 'localhost',
        $port = 8080,
        $address = '127.0.0.1',
        LoopInterface $loop = null
    ) {
        if (3 !== \strlen('???')) {
            throw new \DomainException(
                'Bad encoding, length of unicode character ??? should be 3. Ensure charset UTF-8 and check ini val mbstring.func_autoload'
            );
        }

        if (null === $loop) {
            $loop = LoopFactory::create();
        }

        $this->httpHost = $httpHost;
        $this->port = $port;

        $socket = new Reactor($loop);
        $socket->listen($port, $address);

        $this->routes = new RouteCollection();
        $this->_server = new IoServer(
            new HttpServer(new Router(new UrlMatcher($this->routes, new RequestContext()))),
            $socket,
            $loop
        );
    }

    /**
     * Add an endpoint/application to the server.
     *
     * @param string $path The URI the client will connect to
     * @param ComponentInterface $controller Your application to server for the route. If not specified, assumed to be for a WebSocket
     * @param array $allowedOrigins An array of hosts allowed to connect (same host by default), ['*'] for any
     * @param string $httpHost Override the $httpHost variable provided in the __construct
     *
     * @return ComponentInterface|WsServer
     */
    public function route($path, ComponentInterface $controller, array $allowedOrigins = [], $httpHost = null)
    {
        if ($controller instanceof HttpServerInterface || $controller instanceof WsServer) {
            $decorated = $controller;
        } elseif ($controller instanceof WampServerInterface) {
            $decorated = new WsServer(new WampServer($controller));
        } elseif ($controller instanceof MessageComponentInterface) {
            $decorated = new WsServer($controller);
        } else {
            $decorated = $controller;
        }

        if (null === $httpHost) {
            $httpHost = $this->httpHost;
        }

        $allowedOrigins = \array_values($allowedOrigins);
        if (0 === \count($allowedOrigins)) {
            $allowedOrigins[] = $httpHost;
        }
        if ('*' !== $allowedOrigins[0]) {
            $decorated = new OriginCheck($decorated, $allowedOrigins);
        }

        $this->routes->add(
            'rr-' . ++$this->_routeCounter,
            new Route($path, ['_controller' => $decorated], ['Origin' => $this->httpHost], [], $httpHost)
        );

        return $decorated;
    }

    /**
     * Run the server by entering the event loop.
     */
    public function run()
    {
        $this->_server->run();
    }
}
