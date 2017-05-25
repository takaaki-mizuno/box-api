<?php
namespace TakaakiMizuno\Box;

use TakaakiMizuno\Box\Http\Client as HttpClient;
use TakaakiMizuno\Box\Http\Request;
use TakaakiMizuno\Box\Http\Response;

class Client
{
    protected $baseURL = 'https://api.box.com/';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * Client constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct($clientId, $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($token)
    {
        $this->refreshToken = $token;
    }

    /**
     * @return bool
     */
    public function refreshAccessToken()
    {
        $response = $this->accessAPI('oauth2/token', 'post', array(
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ), array());

        if (!$response->isSuccess()) {
            return false;
        }

        $data               = $response->getJsonResponse();
        $this->accessToken  = $data['access_token'];
        $this->refreshToken = $data['refresh_token'];

        return true;
    }

    /**
     * @param $name
     * @param $limit
     * @param $offset
     *
     * @return array|File[]|null
     */
    public function searchFile($name, $limit=100, $offset=0)
    {
        $params = array(
            'query' => $name,
        );

        $response = $this->accessAPI('2.0/search', 'get', $params, $this->getAuthenticatedHeaders());
        if (!$response->isSuccess()) {
            return null;
        }

        $data  = $response->getJsonResponse();
        $files = array();
        foreach ($data['entries'] as $entry) {
            $files[] = new File($entry);
        }

        return $files;
    }

    private function getAuthenticatedHeaders()
    {
        return array(
            'Authorization' => 'Bearer '.$this->accessToken,
        );
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $param
     * @param array  $headers
     *
     * @return Response
     */
    private function accessAPI($path, $method, $param, $headers = array())
    {
        $url        = $this->baseURL.$path;
        $httpClient = new HttpClient();
        $request    = new Request($url, $method, $param);
        $request->setHeaders($headers);
        $response = $httpClient->request($request);

        return $response;
    }
}
