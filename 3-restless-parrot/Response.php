<?php

namespace Mock;

/**
 * A simple POPO (Plain PHP Object) type class, used to store
 * the details of an HTTP response.
 * 
 * The usual suspects here are the setter and getter methods.
 * For pedancy, the setters do validate the parameters, as well
 * as a couple of useful static consts are introduced here.
 */
final class Response {
    const CODE = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];
    const DATE_FORMAT = 'D, d M Y H:i:s T';
    const SERVER_SIGNATURE = 'RestlessParrot/1.0.0 (https://github.com/goobemaster/php-showcase)';
    const HEADER_KEY_DATE = 'Date';
    const HEADER_KEY_SERVER = 'Server';
    const FORMAT = "HTTP/1.1 %s %s\n%s\n%s";

    /** @var int */
    private $statusCode;
    /** @var string */
    private $body;
    /** @var string[]string[] */
    private $headers;

    public function __construct($statusCode = 200, $body = '', $headers = []) {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = array_merge($headers, [
            self::HEADER_KEY_DATE => (new \DateTime())->format(self::DATE_FORMAT),
            self::HEADER_KEY_SERVER => self::SERVER_SIGNATURE
        ]);
    }

    /**
     * @return bool
     */
    public function setStatusCode($code): bool {
        if (!array_key_exists((int) $code, self::CODE)) return false;
        $this->statusCode = (int) $code;
        return true;
    }

    /**
     * It basically accepts anything, but only cast to string.
     *
     * @return bool
     */
    public function setBody($body): bool {
        $this->body = (string) $body;
        return true;
    }

    /**
     * It accepts any name/value but only cast to string.
     * Overwrites existing names.
     * Forbids overwriting date and server headers.
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function setHeader($name, $value): bool {
        if ($name === self::HEADER_KEY_DATE || $name === self::HEADER_KEY_SERVER) return false;
        $this->headers[(string) $name] = (string) $value;
        return true;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * Returns the value associated with given name, or null if name
     * does not exists.
     *
     * @param string $name
     * @return string|null
     */
    public function getHeader($name): mixed {
        $name = (string) $name;
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Returns all headers as an associative array [name] => value.
     * Note that it hands over the array by value, so any changes
     * will not be saved: use the setter methods.
     *
     * @return string[]
     */
    public function getAllHeaders(): array {
        return $this->headers;
    }

    /**
     * @return Response
     */
    public static function getDefaultMockServerRespose(): Response {
        $date = (new \DateTime())->format(''); // TODO!

        return new Response(200, '', [
            'Date' => $date,
            'Server' => Response::SERVER_SIGNATURE,
            'Last-Modified' => $date,
            'Content-Length' => 0,
            'Content-Type' => 'application/json',
            'Connection' => 'Closed',
            'Access-Control-Allow-Origin' => 'http://localhost:8000',
            'Access-Control-Allow-Methods' => "DELETE, GET, POST, PUT, OPTIONS",
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Allow-Headers' => '*'
        ]);
    }

    /**
     * @return string
     */
    public function __toString(): string {
        $statusCode = $this->statusCode;
        $headers = '';
        foreach ($this->headers as $name => $value) {
            $headers .= "{$name}: {$value}" . PHP_EOL;
        }

        return sprintf(self::FORMAT,
            (string) $statusCode,
            self::CODE[$statusCode],
            $headers,
            $this->body        
        );
    }
}