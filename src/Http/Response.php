<?php
namespace TakaakiMizuno\Box\Http;

class Response
{
    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var string
     */
    private $body;

    /**
     * Request constructor.
     *
     * @param array  $headers
     * @param string $body
     */
    public function __construct($headers, $body)
    {
        $this->headers = $headers;
        $this->body    = $body;
    }

    /**
     * @param string      $name
     * @param string|null $default
     *
     * @return mixed
     */
    public function getHeader($name, $default = null)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getJsonResponse()
    {
        return json_decode($this->body, true);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->headers['responseCode'];
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getStatus() >= 200 && $this->getStatus() <= 299;
    }
}
