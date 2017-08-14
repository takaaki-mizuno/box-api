<?php
namespace TakaakiMizuno\Box;

use Firebase\JWT\JWT;
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
    private $uid;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $publicKeyId;

    /**
     * @var string
     */
    private $type;

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
     * @param string $publicKeyId
     * @param int    $userId
     * @param string $type
     * @param string $uid
     */
    public function __construct($clientId, $clientSecret, $publicKeyId, $userId, $type = 'user', $uid = null)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        if (empty($uid)) {
            $uid = $this->generateUID();
        }
        $this->uid = $uid;

        $this->userId      = $userId;
        $this->type        = $type;
        $this->publicKeyId = $publicKeyId;
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
     * @param string $privateKeyData
     * @param string $passphrase
     *
     * @return bool
     */
    public function getAccessTokenWithJWT($privateKeyData, $passphrase)
    {
        $privateKeyResource = openssl_pkey_get_private($privateKeyData, $passphrase);
        openssl_pkey_export($privateKeyResource, $privateKey);

        $jwtData = array(
            'typ'          => 'JWT',
            'kid'          => $this->publicKeyId,
            'iss'          => $this->clientId,
            'sub'          => $this->userId,
            'box_sub_type' => $this->type,
            'aud'          => 'https://api.box.com/oauth2/token',
            'jti'          => $this->uid,
            'exp'          => time() + 30,
        );
        $jwt = JWT::encode($jwtData, $privateKey, 'RS256');

        $params = array(
            'grant_type'    => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'assertion'     => $jwt,
        );

        $response = $this->accessAPI('oauth2/token', 'post', $params, array());

        if (!$response->isSuccess()) {
            return false;
        }

        $data              = $response->getJsonResponse();
        $this->accessToken = $data['access_token'];

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
        $response = $this->accessAPI(
            '2.0/folders/'.$folderId.'/items',
            'get',
            $params,
            $this->getAuthenticatedHeaders(),
            'json'
        );
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

    /**
     * @param string $name
     * @param int    $folderId
     *
     * @return null|File
     */
    public function renameFolder($name, $folderId)
    {
        $params = array(
            'name' => $name,
        );

        $response = $this->accessAPI(
            '2.0/folders/'.$folderId,
            'put',
            $params,
            $this->getAuthenticatedHeaders(),
            'json'
        );
        if (!$response->isSuccess()) {
            return null;
        }

        $data = $response->getJsonResponse();

        return new File($data);
    }

    /**
     * @param int $id
     *
     * @return null|File
     */
    public function getFileInfo($id)
    {
        $response = $this->accessAPI('2.0/files/'.$id, 'get', array(), $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        return new File($data);
    }

    /**
     * @param int $id
     *
     * @return null|File
     */
    public function getFolderInfo($id)
    {
        $response = $this->accessAPI('2.0/folders/'.$id, 'get', array(), $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        return new File($data);
    }

    /**
     * @param string $name
     * @param int    $fileId
     *
     * @return null|File
     */
    public function renameFile($name, $fileId)
    {
        $params = array(
            'name' => $name,
        );

        $response = $this->accessAPI(
            '2.0/files/'.$fileId,
            'put',
            $params,
            $this->getAuthenticatedHeaders(),
            'json'
        );
        if (!$response->isSuccess()) {
            return null;
        }

        $data = $response->getJsonResponse();

        return new File($data);
    }

    public function getFileVersions($id)
    {
        $file = $this->getFileInfo($id);
        if (empty($file)) {
            return array();
        }

        $fileVersions = array(new FileVersion(array(
            'type'        => 'file_version',
            'id'          => $file->getFileVersionId(),
            'name'        => $file->getName(),
            'size'        => $file->getSize(),
            'created_at'  => $file->getCreatedAt() ? $file->getCreatedAt()->format(\DateTime::ISO8601) : null,
            'modified_at' => $file->getModifiedAt() ? $file->getModifiedAt()->format(\DateTime::ISO8601) : null,
            'modified_by' => array(
                'type'  => 'user',
                'login' => $file->getModifierEmail(),
            ),

        ), $id, true));

        $response = $this->accessAPI(
            '2.0/files/'.$id.'/versions',
            'get',
            array(),
            $this->getAuthenticatedHeaders(),
            'json'
        );
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        foreach ($data['entries'] as $entry) {
            $fileVersions[] = new FileVersion($entry, $id);
        }

        return $fileVersions;
    }

    /**
     * @param int $fileId
     * @param int $fileVersionId
     *
     * @return FileVersion
     */
    public function promoteFileVersion($fileId, $fileVersionId)
    {
        $params = array(
            'type' => 'file_version',
            'id'   => $fileVersionId,
        );

        $response = $this->accessAPI(
            '2.0/files/'.$fileId.'/versions/current',
            'post',
            $params,
            $this->getAuthenticatedHeaders(),
            'json'
        );
        if (!$response->isSuccess()) {
            return null;
        }
        $data = $response->getJsonResponse();

        $fileVersion = new FileVersion($data, $fileId);

        return $fileVersion;
    }

    public function downloadFile($id, $filePath)
    {
        $response = $this->accessAPIDownload(
            '2.0/files/'.$id.'/content',
            'get',
            array(),
            $this->getAuthenticatedHeaders(),
            'json'
        );
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
        $response = $this->accessAPIUpload(
            '2.0/files/content',
            'post',
            $params,
            $this->getAuthenticatedHeaders(),
            array($filePath),
            'https://upload.box.com/api/'
        );
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

    /**
     * @param string $name
     * @param string $filePath
     * @param string $fileId
     *
     * @return File|null
     */
    public function overwriteFile($name, $filePath, $fileId)
    {
        $params = array(
            'name' => $name,
        );
        $response = $this->accessAPIUpload(
            '2.0/files/'.$fileId.'/content',
            'post',
            $params,
            $this->getAuthenticatedHeaders(),
            array($filePath),
            'https://upload.box.com/api/'
        );
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

    public function createUser($name)
    {
        $params = array(
            'name'                    => $name,
            'is_platform_access_only' => true,
        );
        $response = $this->accessAPI('2.0/users', 'post', $params, $this->getAuthenticatedHeaders(), 'json');
        if (!$response->isSuccess()) {
            return null;
        }
        $json = $response->getJsonResponse();
        print_r($json);

        return $json;
    }

    private function generateUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
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
