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
    public function searchFile($name, $limit = 100, $offset = 0)
    {
        $params = array(
            'query'  => $name,
            'offset' => $offset,
            'limit'  => $limit,
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

    /**
     * @param int $folderId
     * @param $limit
     * @param $offset
     *
     * @return array|File[]|null
     */
    public function filesInFolder($folderId = 0, $limit = 1000, $offset = 0)
    {
        $params = array(
            'offset' => $offset,
            'limit'  => $limit,
        );
        $response = $this->accessAPI('2.0/folders/'.$folderId.'/items', 'get', $params,
            $this->getAuthenticatedHeaders(), 'json');
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

    /**
     * @param string $name
     * @param int    $parentFolderId
     *
     * @return null|File
     */
    public function createFolder($name, $parentFolderId)
    {
        $params = array(
            'name'   => $name,
            'parent' => array(
                'id' => $parentFolderId,
            ),
        );
        $response = $this->accessAPI('2.0/folders', 'post', $params, $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }

        $data = $response->getJsonResponse();

        return new File($data);
    }

    public function renameFolder($name, $folderId)
    {
        $params = array(
            'name'   => $name,
        );

        $response = $this->accessAPI('2.0/folders/'.$folderId, 'put', $params, $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }

        $data = $response->getJsonResponse();

        return new File($data);
    }

    public function existFile($id)
    {
        $response = $this->accessAPI('2.0/files/'.$id, 'get', array(), $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        return new File($data);
    }

    public function downloadFile($id, $filePath)
    {
        $response = $this->accessAPIDownload('2.0/files/'.$id.'/content', 'get', array(), $this->getAuthenticatedHeaders(),
            'json');
        if (!$response->isSuccess()) {
            return null;
        }
        file_put_contents($filePath, $response->getResponse());

        return true;
    }

    /**
     * @param string $name
     * @param string $filePath
     * @param int    $parentFolderId
     *
     * @return File|null
     */
    public function uploadFile($name, $filePath, $parentFolderId)
    {
        $params = array(
            'name'   => $name,
            'parent' => array(
                'id' => $parentFolderId,
            ),
        );
        $response = $this->accessAPIUpload('2.0/files/content', 'post', $params, $this->getAuthenticatedHeaders(),
            array($filePath), 'https://upload.box.com/api/');
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        $files = array();
        foreach ($data['entries'] as $entry) {
            $files[] = new File($entry);
        }
        if (count($files) == 0) {
            return null;
        }

        return $files[0];
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
     * @param string $contentType
     *
     * @return Response
     */
    private function accessAPI($path, $method, $param, $headers = array(), $contentType = '')
    {
        $url = $this->baseURL.$path;

        $httpClient = new HttpClient();
        $request    = new Request($url, $method, $param, $contentType);
        $request->setHeaders($headers);
        $response = $httpClient->request($request);

        return $response;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $param
     * @param array  $headers
     * @param array  $files
     * @param string $baseUrl
     *
     * @return Response
     */
    private function accessAPIUpload($path, $method, $param, $headers = array(), $files = array(), $baseUrl = null)
    {
        if (empty($baseUrl)) {
            $baseUrl = $this->baseURL;
        }
        $url        = $baseUrl.$path;
        $httpClient = new HttpClient();
        $request    = new Request($url, $method, $param, 'multipart', $files);
        $request->setHeaders($headers);
        $response = $httpClient->request($request);

        return $response;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $param
     * @param array  $headers
     * @param string $contentType
     *
     * @return Response
     */
    private function accessAPIDownload($path, $method, $param, $headers = array(), $contentType = '')
    {
        $url = $this->baseURL.$path;

        $httpClient = new HttpClient();
        $request    = new Request($url, $method, $param, $contentType);
        $request->setHeaders($headers);
        $response = $httpClient->requestWithCurl($request);

        return $response;
    }
}
