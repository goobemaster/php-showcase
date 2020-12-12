<?php

namespace Mock;

require_once('autoload.php');

use Mock\LineParser as LineParser;

/**
 * A simple POPO for http requests.
 */
final class Request {
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';
    const OPTIONS = 'options';
    const METHOD = [
        self::GET => 0,
        self::POST => 1,
        self::PUT => 2,
        self::DELETE => 3,
        self::OPTIONS => 4
    ];
    const LAX_RELATIVE_URL_PATTERN_TM = "\/(?:.+\/?)?";
    const HEADER_HOST_KEY = 'Host';
    const HEADER_ACCEPT_KEY = 'Accept';
    const HASHING_ALGO = 'md4';
    const PARSE_ERROR = 'Failed to parse the packet, because its malformed or unsupported.';
    const EMPTY_ERROR = 'Empty packet!';
    const JSON_MIME = 'application/json';
    const ANY_MIME = '*/*';

    /** @var int */
    private $method;
    /** @var string */
    private $path;
    /** @var string */
    private $protocolVersion;
    /** @var string[] */
    private $headers;
    /** @var string */
    private $body;

    /**
     * It creates the POPO from a http request packet using a line parser:
     * - It extracts common data such as method, headers
     * - Saves the raw body as is
     * 
     * It throws an exception if the packet is seriously malformed or the
     * request method is unsupported by the mock server.
     *
     * @param string $data
     * @throws Exception
     */
    public function __construct($data = null) {
        if (is_null($data)) return;
        if (!is_string($data) || empty($data)) throw new \Exception(self::EMPTY_ERROR);

        $errorOut = function () { throw new \Exception(self::PARSE_ERROR); };
        $urlPattern = self::LAX_RELATIVE_URL_PATTERN_TM;
        $this->body = '';
        $parser = new LineParser((string) $data);

        while ($parser->hasNext()) {
            $parser->next();
            switch ($parser->getSegment()) {
                case 0: // Method / Version
                    if (!$parser->matches("/^(GET|POST|PUT|DELETE|OPTIONS) ({$urlPattern}) (.+)$/")) $errorOut();
                    $this->method = self::METHOD[strtolower($parser->getMatch(0))];
                    $this->path = $parser->getMatch(1);
                    $this->protocolVersion = trim($parser->getMatch(2));
                    $parser->advanceSegment();
                    break;
                case 1: // Headers
                    if (!$parser->matches('/^([a-zA-Z\-]+): (.+)$/')) {
                        if (empty(trim($parser->getCurrentLine()))) {
                            $parser->advanceSegment();
                        }             
                    } else {
                        $this->headers[trim($parser->getMatch(0))] = trim($parser->getMatch(1));
                    }
                    break;
                case 2: // Body
                    $content = $parser->getCurrentLine();
                    if (empty($content)) break;
                    $this->body .= $content;
                    break;
                default:
                    $errorOut();
            }
        }

        // Checking mandatory headers
        if (!isset($this->headers[self::HEADER_HOST_KEY]) ||
            !isset($this->headers[self::HEADER_ACCEPT_KEY])) {
                $errorOut();
        }
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string {
        return $this->protocolVersion;
    }

    /**
     * @param string $protocolVersion
     * @return void
     */
    private function setProtocolVersion($protocolVersion): void {
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @param string $path
     * @return void
     */
    private function setPath($path): void {
        $this->path = $path;
    }

    /**
     * @return integer
     */
    public function getMethod(): int {
        return $this->method;
    }

    /**
     * @param int $method
     * @return void
     */
    private function setMethod($method): void {
        $this->method = $method;
    }

    /**
     * @param int|string $method
     * @return boolean
     */
    public function isMethodOf($method): bool {
        if (is_int($method)) {
            return $this->method === $method;
        } else if (is_string($method)) {
            return array_key_exists(strtolower($method), self::METHOD) &&
                self::METHOD[strtolower($method)] === $this->method;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * @param string $body
     * @return void
     */
    public function setBody($body): void {
        $this->body = $body;
    }

    /**
     * @return object
     */
    public function getBodyAsJsonObject(): object {
        $object = json_decode($this->body);
        return $object === null ? (object) [] : $object;
    }

    /**
     * @return string[]
     */
    public function getAllHeaders(): array {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader($name): string {
        return isset($this->headers[$name]) ? $this->headers[$name] : '';
    }

    /**
     * @param string[] $headers
     * @return void
     */
    private function replaceHeaders($headers): void {
        $this->headers = $headers;
    }

    /**
     * @param int $method
     * @param string $path
     * @param string[] $headers
     * @param string $body
     * @return void
     */
    public static function fromUserDefinedFields($method, $path, $headers, $body) {
        $request = new Request();
        $request->setMethod($method);
        $request->setPath($path);
        $request->replaceHeaders($headers);
        $request->setBody($body);
        $request->setProtocolVersion('HTTP/1.1');
        return $request;
    }

    /**
     * Returns a unique hash of the request.
     * 
     * We implement this magic method for the specific purpose of
     * comparing two requests, and *not* for logging or other
     * traditional purposes.
     * 
     * To be more precise the result of this method is going to
     * be a key of an associative array, used for lookups, so:
     * - The hash string must be unique to a high degree
     * - The hash string should be short for performance
     * - The algorithm should be quicker than doing a == object comparison
     * 
     * My initial tests were promising on the above points, its
     * around some 25% faster to look up by this hash, than comparing two
     * Request objects.
     * 
     * The only drawback is that we can *only* compare two verbatim
     * requests (problematic if the order of the headers differ).
     * 
     * @return string
     */
    public function __toString(): string {
        $headers = '';
        foreach ($this->headers as $name => $value) {
            $headers .= "{$name}-{$value}";
        }

        return (string) hash(self::HASHING_ALGO,
            $this->method .
            $this->protocolVersion .
            $this->path .
            $headers .
            $this->body);
    }
}