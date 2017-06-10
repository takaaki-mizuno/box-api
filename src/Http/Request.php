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
    private $headers     = array();

    private $parameters  = array();

    private $files       = array();

    private $contentType = '';

    private $deafultHeaders = array(
        'Accept' => '*/*',
    );

    /**
     * Request constructor.
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param string $contentType
     * @param array  $files
     */
    public function __construct($url, $method = 'get', $parameters = array(), $contentType = '', $files = array())
    {
        $this->url         = $url;
        $this->method      = $method;
        $this->parameters  = $parameters;
        $this->files       = $files;
        $this->contentType = $contentType == 'json' ? 'application/json' : ($contentType == 'multipart' ? 'multipart/form-data' : 'application/x-www-form-urlencoded');
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers += $headers;
    }

    public function getParameters()
    {
        return $this->parameters;
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

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return bool
     */
    public function hasFile()
    {
        return count($this->files) > 0;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
