<?php
namespace TakaakiMizuno\Box\Http;

class Request
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $headers    = array();

    private $parameters = array();

    /**
     * Request constructor.
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     */
    public function __construct($url, $method = 'get', $parameters = array())
    {
        $this->url        = $url;
        $this->method     = $method;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers += $headers;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return strtolower($this->method);
    }
}
