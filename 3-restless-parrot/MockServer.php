<?php

namespace Mock;

include_once('autoload.php');

use Mock\Request as Request;
use Mock\Response as Response;
use Mock\Router as Router;

/**
 * The MockServer is a singleton class. The server that it implements,
 * should be running in the background. If you check the php-showcase
 * program you'll see that for this project we actually do this extra step.
 * 
 * Fun fact: Strictly speaking, this class is not a real singleton.
 * Can you find out why?
 */
final class MockServer {
    const DEFAULT_PORT = 8010;
    const MAX_REQUEST_BYTES = 2048;

    /** @var MockServer */
    private static $instance;

    /** @var int */
    private $port;
    /** @var resource */
    private $socket;
    /** @var Router */
    private $router;
    /** @var string[]Response[] */
    public static $resources = [];
    /** @var DateTime */
    public static $bootTime;

    /**
     * @throws Exception
     */
    private function __construct() {
        echo "*** \033[46mRestless Parrot Mock Server\033[0m ***\n\n";
        self::$bootTime = new \DateTime();
        $this->router = new Router();
        $this->bind();
        $this->listen();
    }

    /**
     * Creates a socket (endpoint for communication), and
     * binds a name to the socket.
     *
     * @return void
     * @throws Exception
     */
    private function bind(): void {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->port = !empty($argv) && (int) $argv >= 1024 ? (int) $argv : self::DEFAULT_PORT;
        if (!socket_bind($this->socket, 'localhost', $this->port)) {
            throw new \Exception(socket_strerror(socket_last_error()));
        }
    }

    /**
     * Constantly listens for incoming connections, and
     * handles them if possible.
     *
     * @return void
     * @throws Exception
     */
    private function listen(): void {
        echo "Listening on \033[0;36mhttp://localhost:" . $this->port . "/\033[0m" . PHP_EOL .
        "Press \033[0;31mCtrl+C\033[0m to stop the server.\n";
        socket_listen($this->socket);
        // socket_set_nonblock($this->socket); 

        while(true) {
            if(($connection = socket_accept($this->socket)) === false) {
                usleep(100);
                continue;
            } else if ($connection > 0) {
                try {
                    $this->handle($connection);
                } catch (\Exception $e) {
                    echo 'Failed to handle a request: ' . $e->getMessage();
                }
                socket_close($connection);
            } else {
                throw new \Exception(socket_strerror($connection));
            }
        }
    }

    /**
     * Reads the data packet from the connection, and
     * attempts to handle the request, in one of the following ways:
     * - If the request has been registered with the server before, it simply
     *   sends the associated response.
     * - Otherwise, the request is sent to the router so it can find a
     *   suitable controller, which in turn will do the honors of generating
     *   a response.
     *
     * @param resource $connection
     * @return void
     * @throws Exception
     */
    private function handle(&$connection): void {
        if(($data = socket_read($connection, self::MAX_REQUEST_BYTES)) === false) {
            throw new \Exception('Read error: ' . socket_strerror(socket_last_error()));
        } else if (empty($data)) {
            throw new \Exception('No data!');
        }
        $request = new Request($data);

        // We have to instruct the browser to chill out because we are doing CORS...
        if ($request->isMethodOf(Request::OPTIONS)) {
            $body = json_encode((object) ['message' => 'Perfectly Splendid']);
            $response = Response::getDefaultMockServerRespose();
            $response->setBody($body);
            $response->setHeader('Content-Length', strlen($body));
            $this->respond($connection, $response);
            return;
        }

        // Since this a mock server, lets check our request map first
        $mockRequestKey = (string) $request;
        if (array_key_exists($mockRequestKey, self::$resources)) {
            $this->respond($connection, self::$resources[$mockRequestKey]['response']);
            return;
        }
        // Unfortunately the hash will only match if the request is verbatim,
        // Which is not guaranteed with browser stuff at all...
        foreach (self::$resources as $hash => $resource) {
            $originalRequest = $resource['request'];
            if ($originalRequest === null) continue;
            foreach ($request->getAllHeaders() as $headerName => $headerValue) {
                if ($originalRequest->getHeader($headerName) !== $headerValue) continue 2;
            }
            if ($originalRequest->getMethod() === $request->getMethod() &&
                $originalRequest->getPath() === $request->getPath() &&
                $originalRequest->getBody() === $request->getBody()) {
                // Close enough
                $this->respond($connection, $resource['response']);
                return;
            }
        }

        // Or perhaps this is a built in request (e.g. to configure the mock server)
        $this->router->setRequest($request);
        if ($this->router->pathExists()) {
            $response = $this->router->runController();
            if ($response !== null && is_object($response) && $response instanceof Response) {
                $this->respond($connection, $response);
            } else {
                throw new \Exception('The controller failed to handle the request!');
            }
        } else {
            $body = json_encode((object) ['message' => "I'm a parrot!"]);
            $response = Response::getDefaultMockServerRespose();
            $response->setBody($body);
            $response->setHeader('Content-Length', strlen($body));
            $response->setStatusCode(404);
            $this->respond($connection, $response);
            throw new \Exception(sprintf('No controller for %s path!', $request->getPath()));
        }
    }

    /**
     * Does the menial task of writing the stringified response to the socket.
     *
     * @param resource $connection
     * @param Response $response
     * @return void
     */
    private function respond($connection, $response): void {
        $responseRaw = (string) $response;
        socket_write($connection, $responseRaw, strlen($responseRaw));
    }

    /**
     * Returns the one and only instance of this class.
     *
     * @return MockServer
     */
    public static function getInstance(): MockServer {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }
}

// Bootstrapping the server
MockServer::getInstance();